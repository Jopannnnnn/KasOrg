<?php

namespace Tests\Feature;

use App\Models\JenisIuran;
use App\Models\KategoriTransaksi;
use App\Models\PembayaranIuran;
use App\Models\Periode;
use App\Models\Role;
use App\Models\TagihanIuran;
use App\Models\TransaksiKas;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ChromiumPasswordCheck;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

class EnforceCsrfForPaymentTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class PembayaranFlowTest extends TestCase
{
    private User $admin;

    private User $member;

    private User $other;

    private JenisIuran $type;

    private Periode $period;

    private string $password;

    private bool $live = false;

    private ?string $baseline = null;

    private array $tables = ['roles', 'users', 'jenis_iuran', 'periode', 'tagihan_iuran', 'pembayaran_iuran', 'transaksi_kas', 'kategori_transaksi'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->live = getenv('KASORG_FINANCE_MARIADB') === '1';
        if (! app()->environment('testing')) {
            throw new \RuntimeException('Pengujian hanya pada environment testing.');
        }
        if ($this->live) {
            config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'kasorg_dev', 'database.connections.mysql.url' => null]);
            DB::purge('mysql');
            if (DB::connection()->getDatabaseName() !== 'kasorg_dev' || DB::getDriverName() !== 'mysql'
                || DB::selectOne('select database() as db')->db !== 'kasorg_dev') {
                throw new \RuntimeException('Koneksi pengujian development tidak sesuai.');
            }
            $this->baseline = $this->fingerprint();
            DB::beginTransaction(); // Seluruh fixture/uji di-rollback, tanpa migration atau seeder.
        } else {
            if (DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
                throw new \RuntimeException('Fixture schema hanya untuk SQLite RAM.');
            }
            Schema::create('roles', function (Blueprint $t) {
                $t->id();
                $t->string('kode')->unique();
                $t->string('nama');
                $t->timestamps();
            });
            Schema::create('users', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('nim', 20)->unique();
                $t->string('email')->unique();
                $t->string('password');
                $t->foreignId('role_id')->constrained('roles');
                $t->string('status_akun')->default('aktif');
                $t->timestamp('email_verified_at')->nullable();
                $t->timestamp('last_login_at')->nullable();
                $t->rememberToken();
                $t->timestamps();
                $t->softDeletes();
            });
            FinanceSchema::create();
        }
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForPaymentTest::class);
        Storage::fake('bukti');
        $this->password = Str::random(32).'Aa1!';
        $this->admin = $this->account('admin');
        $this->member = $this->account('anggota');
        $this->other = $this->account('anggota');
        $this->type = JenisIuran::create(['nama' => 'Uji Iuran '.Str::uuid(), 'nominal' => '75000.25', 'aktif' => true]);
        $year = 2100;
        while (Periode::where('tahun', $year)->exists()) {
            $year++;
        }
        $this->period = Periode::create(['tahun' => $year, 'bulan' => 1, 'tanggal_mulai' => "$year-01-01", 'tanggal_selesai' => "$year-01-31", 'status' => 'terbuka', 'saldo_awal' => '0']);
        KategoriTransaksi::firstOrCreate(['nama' => 'Iuran anggota', 'jenis' => 'pemasukan'], ['is_sistem' => true]);
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->live && $this->baseline !== null) {
                while (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                $this->assertTrue($this->baseline === $this->fingerprint(), 'Data awal, termasuk akun demo, harus identik setelah rollback.');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function fingerprint(): string
    {
        $rows = [];
        foreach ($this->tables as $table) {
            $rows[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        return hash('sha256', json_encode($rows)); // Tidak mencetak data akun/hash password.
    }

    private function account(string $role): User
    {
        return User::create([
            'name' => 'Uji Pembayaran '.Str::random(10), 'nim' => '98'.random_int(100000000000, 999999999999),
            'email' => 'uji-'.Str::uuid().'@kasorg.test', 'password' => $this->password,
            'role_id' => Role::firstOrCreate(['kode' => $role], ['nama' => ucfirst($role)])->id, 'status_akun' => 'aktif',
        ]);
    }

    private function write(string $route, array $data = [])
    {
        $token = Str::random(40);

        return $this->withSession(['_token' => $token])->post($route, $data + ['_token' => $token]);
    }

    private function billData(): array
    {
        return ['user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id, 'jatuh_tempo' => today()->toDateString()];
    }

    private function createBill(): TagihanIuran
    {
        $this->actingAs($this->admin);
        $this->write(route('admin.iuran.store'), $this->billData())->assertRedirect(route('admin.iuran'))->assertSessionHasNoErrors();

        return TagihanIuran::where('user_id', $this->member->id)->where('jenis_iuran_id', $this->type->id)->firstOrFail();
    }

    private function proof(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('screenshot.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jVbQAAAAASUVORK5CYII='));
    }

    private function submit(TagihanIuran $bill, string $method = 'tunai'): PembayaranIuran
    {
        $this->actingAs($this->member);
        $data = ['metode' => $method, 'tanggal_bayar' => today()->toDateString()];
        if ($method === 'transfer') {
            $data['bukti'] = $this->proof();
        }
        $this->write(route('anggota.pembayaran.store', $bill), $data)->assertRedirect(route('anggota.tagihan'))->assertSessionHasNoErrors();

        return $bill->pembayaranIuran()->latest('id')->firstOrFail();
    }

    public function test_full_http_flow_reject_retry_different_method_approve_exactly_once(): void
    {
        $cashBefore = TransaksiKas::withTrashed()->count();
        Auth::logout();
        $this->write(route('login.store'), ['email' => $this->admin->email, 'password' => $this->password])->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Hash::check($this->password, $this->admin->password));
        $bill = $this->createBill();
        $this->assertSame('75000.25', $bill->nominal);
        $this->assertSame($cashBefore, TransaksiKas::withTrashed()->count());
        $this->get(route('admin.iuran'))->assertOk()->assertSee($this->member->name)->assertSee($this->type->nama);
        Auth::logout();
        $this->write(route('login.store'), ['email' => $this->member->email, 'password' => $this->password])->assertRedirect(route('anggota.dashboard'));
        $this->get(route('anggota.tagihan'))->assertOk()->assertSee($this->type->nama)->assertSee('Bayar Sekarang');
        $first = $this->submit($bill, 'transfer');
        $original = $first->getRawOriginal();
        Storage::disk('bukti')->assertExists($first->bukti_path);
        $this->get(route('pembayaran.bukti', $first))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('menunggu', $first->status);
        $this->assertSame($cashBefore, TransaksiKas::withTrashed()->count());
        $this->actingAs($this->admin)->get(route('admin.verifikasi'))->assertOk()->assertSee($this->member->name)->assertSee($this->type->nama);
        $reason = 'Bukti tidak cocok dengan mutasi <script>alert(1)</script>';
        $this->write(route('admin.verifikasi.update', $first), ['keputusan' => 'ditolak', 'alasan_penolakan' => $reason])->assertSessionHasNoErrors();
        $this->assertSame('ditolak', $first->fresh()->status);
        $this->assertSame('belum_lunas', $bill->fresh()->status);
        $this->assertSame($cashBefore, TransaksiKas::withTrashed()->count());
        $this->actingAs($this->member)->get(route('anggota.tagihan'))->assertOk()->assertSee($reason)->assertDontSee($reason, false)->assertSee('Ajukan Ulang');
        $second = $this->submit($bill, 'tunai');
        $this->assertNotEquals($first->id, $second->id);
        $this->assertSame('ditolak', $first->fresh()->status);
        $this->assertSame($original['bukti_path'], $first->fresh()->bukti_path);
        $this->assertSame(2, $bill->pembayaranIuran()->count());
        $this->actingAs($this->admin)->get(route('admin.verifikasi', ['status' => 'menunggu']))->assertOk()->assertSee($this->member->name)->assertSee('Pengajuan #'.$second->id);
        $this->get(route('admin.verifikasi', ['status' => 'ditolak']))->assertOk()->assertSee($reason);
        $this->write(route('admin.verifikasi.update', $second), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('lunas', $bill->fresh()->status);
        $this->assertNotNull($bill->fresh()->lunas_at);
        $this->assertSame('disetujui', $second->fresh()->status);
        $this->assertSame(1, TransaksiKas::where('tagihan_iuran_id', $bill->id)->count());
        $cash = TransaksiKas::where('tagihan_iuran_id', $bill->id)->firstOrFail();
        $this->assertSame($second->id, $cash->pembayaran_iuran_id);
        $this->assertSame('75000.25', $cash->nominal);
        $this->assertSame('pemasukan', $cash->jenis);
        $this->assertSame($cashBefore + 1, TransaksiKas::withTrashed()->count());
        $this->write(route('admin.verifikasi.update', $second), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasErrors('pembayaran');
        $this->actingAs($this->member);
        $this->write(route('anggota.pembayaran.store', $bill), ['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()])->assertSessionHasErrors('pembayaran');
        $this->assertSame(2, $bill->pembayaranIuran()->count());
        $this->assertSame($cashBefore + 1, TransaksiKas::withTrashed()->count());
        $this->get(route('anggota.tagihan'))->assertOk()->assertSee('Lunas')->assertDontSee('Bayar Sekarang')->assertDontSee('Ajukan Ulang');
    }

    public function test_duplicate_bills_pending_attempts_and_uploaded_orphans_are_prevented(): void
    {
        $bill = $this->createBill();
        $this->write(route('admin.iuran.store'), $this->billData())->assertSessionHasErrors('user_id');
        $this->assertSame(1, TagihanIuran::where('user_id', $this->member->id)->count());
        $this->submit($bill);
        $this->write(route('anggota.pembayaran.store', $bill), ['metode' => 'transfer', 'tanggal_bayar' => today()->toDateString(), 'bukti' => $this->proof()])->assertSessionHasErrors('pembayaran');
        $this->assertCount(0, Storage::disk('bukti')->allFiles());
        $this->assertSame(1, $bill->pembayaranIuran()->count());
    }

    public function test_cash_rejected_then_transfer_approved_and_private_file_is_readable(): void
    {
        $bill = $this->createBill();
        $cash = $this->submit($bill);
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $cash), ['keputusan' => 'ditolak', 'alasan_penolakan' => 'Uang tunai belum diterima.'])->assertSessionHasNoErrors();
        $transfer = $this->submit($bill, 'transfer');
        $expected = Storage::disk('bukti')->get($transfer->bukti_path);
        $this->assertSame($expected, $this->get(route('pembayaran.bukti', $transfer))->assertOk()->streamedContent());
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $transfer), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('lunas', $bill->fresh()->status);
        $this->assertSame('ditolak', $cash->fresh()->status);
        $this->assertSame('disetujui', $transfer->fresh()->status);
        $this->assertSame(1, TransaksiKas::where('tagihan_iuran_id', $bill->id)->count());
        $this->write(route('admin.verifikasi.update', $cash), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasErrors('pembayaran');
    }

    public function test_missing_transfer_file_blocks_approval_without_partial_cash(): void
    {
        $bill = $this->createBill();
        $payment = $this->submit($bill, 'transfer');
        Storage::disk('bukti')->delete($payment->bukti_path);
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasErrors('pembayaran');
        $this->assertSame('menunggu', $payment->fresh()->status);
        $this->assertSame('belum_lunas', $bill->fresh()->status);
        $this->assertSame(0, TransaksiKas::where('tagihan_iuran_id', $bill->id)->count());
        $this->get(route('pembayaran.bukti', $payment))->assertNotFound();
    }

    public function test_ownership_admin_authorization_private_proof_and_csrf(): void
    {
        $bill = $this->createBill();
        $payment = $this->submit($bill, 'transfer');
        $this->actingAs($this->other)->get(route('anggota.tagihan'))->assertOk()->assertDontSee($this->type->nama);
        $this->write(route('anggota.pembayaran.store', $bill), ['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()])->assertForbidden();
        $this->get(route('pembayaran.bukti', $payment))->assertForbidden();
        $this->get(route('admin.verifikasi'))->assertForbidden();
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertForbidden();
        $this->write(route('admin.iuran.store'), $this->billData())->assertForbidden();
        $this->actingAs($this->admin)->get(route('pembayaran.bukti', $payment))->assertOk();
        $this->post(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertStatus(419);
        $this->post(route('admin.iuran.store'), $this->billData())->assertStatus(419);
        $this->actingAs($this->member)->post(route('anggota.pembayaran.store', $bill), ['metode' => 'tunai'])->assertStatus(419);
        Auth::logout();
        $this->get(route('pembayaran.bukti', $payment))->assertRedirect(route('login'));
        $this->get('/storage/'.$payment->bukti_path)->assertForbidden();
        $this->assertSame('menunggu', $payment->fresh()->status);
    }

    #[DataProvider('badPaymentData')]
    public function test_server_validation_rejects_invalid_payment_fields(array $override, string $field): void
    {
        $bill = $this->createBill();
        $this->actingAs($this->member);
        $this->write(route('anggota.pembayaran.store', $bill), array_replace(['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()], $override))->assertSessionHasErrors($field);
        $this->assertSame(0, $bill->pembayaranIuran()->count());
    }

    public static function badPaymentData(): array
    {
        return [
            'amount injection' => [['nominal' => 1], 'nominal'],
            'status injection' => [['status' => 'disetujui'], 'status'],
            'path injection' => [['bukti_path' => '../../.env'], 'bukti_path'],
            'actor injection' => [['diajukan_oleh' => 1], 'diajukan_oleh'],
            'method' => [['metode' => 'gateway'], 'metode'],
            'missing transfer proof' => [['metode' => 'transfer'], 'bukti'],
            'future date' => [['tanggal_bayar' => '9999-01-01'], 'tanggal_bayar'],
            'invalid date' => [['tanggal_bayar' => '2026-02-30'], 'tanggal_bayar'],
            'long note' => [['catatan' => str_repeat('a', 256)], 'catatan'],
        ];
    }

    public function test_file_type_size_and_disguised_executable_are_rejected(): void
    {
        $bill = $this->createBill();
        $this->actingAs($this->member);
        foreach ([UploadedFile::fake()->createWithContent('fake.png', '<?php echo 1;'), UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'), $this->proof()->size(5121)] as $file) {
            $this->write(route('anggota.pembayaran.store', $bill), ['metode' => 'transfer', 'tanggal_bayar' => today()->toDateString(), 'bukti' => $file])->assertSessionHasErrors('bukti');
        }
        $this->assertCount(0, Storage::disk('bukti')->allFiles());
        $this->assertSame(0, $bill->pembayaranIuran()->count());
    }

    public function test_rejection_requires_reason_approval_requires_manual_confirmation(): void
    {
        $bill = $this->createBill();
        $payment = $this->submit($bill);
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'ditolak', 'alasan_penolakan' => '  '])->assertSessionHasErrors('alasan_penolakan');
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui'])->assertSessionHasErrors('konfirmasi');
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'pending'])->assertSessionHasErrors('keputusan');
        $this->assertSame('menunggu', $payment->fresh()->status);
        $this->assertSame(0, TransaksiKas::where('tagihan_iuran_id', $bill->id)->count());
    }

    public function test_only_active_members_and_open_periods_can_receive_bills(): void
    {
        $this->member->update(['status_akun' => 'nonaktif']);
        $this->write(route('admin.iuran.store'), $this->billData())->assertSessionHasErrors('user_id');
        $this->member->update(['status_akun' => 'aktif']);
        $this->write(route('admin.iuran.store'), array_replace($this->billData(), ['user_id' => $this->admin->id]))->assertSessionHasErrors('user_id');
        $this->period->update(['status' => 'ditutup']);
        $this->write(route('admin.iuran.store'), $this->billData())->assertSessionHasErrors('periode_id');
        $this->period->update(['status' => 'terbuka']);
        $this->type->update(['aktif' => false]);
        $this->write(route('admin.iuran.store'), $this->billData())->assertSessionHasErrors('jenis_iuran_id');
        $this->type->update(['aktif' => true]);
        $this->member->delete();
        $this->write(route('admin.iuran.store'), $this->billData())->assertSessionHasErrors('user_id');
    }

    public function test_closed_period_blocks_approval_and_archived_member_history_survives(): void
    {
        $bill = $this->createBill();
        $payment = $this->submit($bill);
        $this->period->update(['status' => 'ditutup']);
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasErrors('periode_id');
        $this->assertSame('menunggu', $payment->fresh()->status);
        $this->assertSame('belum_lunas', $bill->fresh()->status);
        $this->member->delete();
        $this->get(route('admin.verifikasi'))->assertOk()->assertSee($this->member->name);
        $this->get(route('admin.iuran'))->assertOk()->assertSee($this->member->name);
    }

    public function test_unique_cash_constraint_rolls_back_bill_and_payment_on_conflict(): void
    {
        $bill = $this->createBill();
        $payment = $this->submit($bill);
        $cash = TransaksiKas::create(['tanggal' => today(), 'jenis' => 'pemasukan', 'kategori_transaksi_id' => KategoriTransaksi::where('nama', 'Iuran anggota')->where('jenis', 'pemasukan')->firstOrFail()->id, 'tagihan_iuran_id' => $bill->id, 'pembayaran_iuran_id' => $payment->id, 'nominal' => $bill->nominal, 'keterangan' => 'Fixture konflik unik', 'sumber' => 'pembayaran_iuran', 'dibuat_oleh' => $this->admin->id]);
        $cash->delete(); // Constraint juga melindungi transaksi yang diarsipkan.
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasErrors('pembayaran');
        $this->assertSame('menunggu', $payment->fresh()->status);
        $this->assertSame('belum_lunas', $bill->fresh()->status);
        $this->assertSame(1, TransaksiKas::withTrashed()->where('tagihan_iuran_id', $bill->id)->count());
    }

    public function test_add_type_search_filter_pagination_and_password_controls(): void
    {
        $name = 'Uji jenis baru '.Str::uuid();
        $this->write(route('admin.iuran.jenis.store'), ['nama' => $name, 'nominal' => '500.50'])->assertSessionHasNoErrors();
        $this->write(route('admin.iuran.jenis.store'), ['nama' => $name, 'nominal' => '500.50'])->assertSessionHasErrors('nama');
        $this->write(route('admin.iuran.jenis.store'), ['nama' => $name.' negatif', 'nominal' => '-1'])->assertSessionHasErrors('nominal');
        $this->write(route('admin.iuran.jenis.store'), ['nama' => $name.' desimal', 'nominal' => '1.001'])->assertSessionHasErrors('nominal');
        $bill = $this->createBill();
        $this->get(route('admin.iuran', ['q' => $this->member->nim]))->assertOk()->assertSee($this->type->nama);
        $this->get(route('admin.iuran', ['q' => '%']))->assertOk()->assertDontSee('detail-tagihan-'.$bill->id);
        for ($i = 0; $i < 11; $i++) {
            $type = JenisIuran::create(['nama' => $name.'-'.$i, 'nominal' => '10', 'aktif' => true]);
            TagihanIuran::create(['user_id' => $this->member->id, 'jenis_iuran_id' => $type->id, 'periode_id' => $this->period->id, 'nominal' => '10', 'status' => 'belum_lunas']);
        }
        $this->actingAs($this->member);
        $response = $this->get(route('anggota.tagihan'))->assertOk();
        $this->assertSame(12, $response->viewData('bills')->total());
        $this->assertCount(10, $response->viewData('bills'));
        $this->assertCount(2, $this->get(route('anggota.tagihan', ['page' => 2]))->assertOk()->viewData('bills'));
        $payment = $this->submit($bill);
        $this->assertSame(1, $this->get(route('anggota.tagihan', ['status' => 'menunggu']))->assertOk()->viewData('bills')->total());
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'ditolak', 'alasan_penolakan' => 'Uji filter'])->assertSessionHasNoErrors();
        $this->actingAs($this->member);
        $this->assertSame(1, $this->get(route('anggota.tagihan', ['status' => 'ditolak']))->assertOk()->viewData('bills')->total());
        $this->submit($bill);
        $this->assertSame(0, $this->get(route('anggota.tagihan', ['status' => 'ditolak']))->assertOk()->viewData('bills')->total());
        $this->actingAs($this->admin)->get(route('admin.anggota'))->assertOk()->assertSee('data-password-toggle="anggota-password"', false)->assertSee('data-password-toggle="anggota-password-confirmation"', false)->assertSee('aria-pressed="false"', false);
        if ($browser = getenv('KASORG_BROWSER_EXECUTABLE')) {
            $html = $this->get(route('admin.anggota'))->assertOk()->getContent();
            $results = ChromiumPasswordCheck::run($html, $browser);
            $this->assertCount(16, $results);
            foreach ($results as $result) {
                $this->assertTrue($result['passed'], $result['name']);
            }
        }
    }
}
