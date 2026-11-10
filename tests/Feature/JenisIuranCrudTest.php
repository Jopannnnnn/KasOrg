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
use App\Services\JenisIuranService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ChromiumPasswordCheck;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

class EnforceCsrfForJenisIuranTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class JenisIuranCrudTest extends TestCase
{
    private User $admin;

    private User $member;

    private JenisIuran $type;

    private Periode $period;

    protected function setUp(): void
    {
        parent::setUp();
        // Tidak ada opt-in live DB: menolak target selain RAM sebelum DDL/fixture.
        if (! app()->environment('testing') || DB::getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Tes jenis iuran hanya diizinkan pada SQLite RAM testing.');
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
            $t->string('nim')->unique();
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
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForJenisIuranTest::class);
        $this->admin = $this->account('admin');
        $this->member = $this->account('anggota');
        $this->type = JenisIuran::create(['nama' => 'Iuran rutin', 'nominal' => '75000.25', 'deskripsi' => 'Kas mahasiswa', 'aktif' => true]);
        $this->period = Periode::create(['tahun' => 2100, 'bulan' => 1, 'tanggal_mulai' => '2100-01-01', 'tanggal_selesai' => '2100-01-31', 'saldo_awal' => '0', 'status' => 'terbuka']);
        $this->actingAs($this->admin);
    }

    private function account(string $role): User
    {
        return User::create([
            'name' => 'Uji Jenis '.Str::random(8), 'nim' => '99'.random_int(1000000000, 9999999999),
            'email' => Str::uuid().'@kasorg.test', 'password' => Str::random(40),
            'role_id' => Role::firstOrCreate(['kode' => $role], ['nama' => ucfirst($role)])->id, 'status_akun' => 'aktif',
        ]);
    }

    private function write(string $method, string $url, array $data = [])
    {
        $token = Str::random(40);

        return $this->withSession(['_token' => $token])->call($method, $url, $data + ['_token' => $token]);
    }

    private function data(array $overrides = []): array
    {
        return array_replace(['nama' => 'Iuran kegiatan', 'nominal' => '120000.50', 'deskripsi' => 'Kegiatan kampus', 'aktif' => '1'], $overrides);
    }

    private function bill(): TagihanIuran
    {
        return TagihanIuran::create([
            'user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id,
            'nominal' => '75000.25', 'status' => 'belum_lunas',
        ]);
    }

    public function test_list_filters_search_pagination_and_escaped_html(): void
    {
        $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertSee('Iuran rutin')
            ->assertSee('Rp 75.000,25')->assertSee('name="_token"', false)->assertSee('edit-jenis-'.$this->type->id);
        for ($i = 0; $i < 12; $i++) {
            JenisIuran::create(['nama' => sprintf('Jenis %02d', $i), 'nominal' => '10', 'aktif' => false]);
        }
        $this->get(route('admin.iuran.jenis.index', ['status' => 'nonaktif', 'page' => 2]))
            ->assertOk()->assertViewHas('types', fn ($types) => $types->total() === 12 && $types->count() === 2)
            ->assertSee('status=nonaktif', false);
        $this->get(route('admin.iuran.jenis.index', ['q' => 'mahasiswa']))->assertOk()
            ->assertViewHas('types', fn ($types) => $types->total() === 1);
        $this->get(route('admin.iuran.jenis.index', ['q' => '%']))->assertOk()
            ->assertViewHas('types', fn ($types) => $types->total() === 0);
        $this->type->update(['nama' => '<script>alert(1)</script>']);
        $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
        $this->getJson(route('admin.iuran.jenis.index', ['status' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_create_uses_existing_endpoint_and_saves_status_and_decimal(): void
    {
        $this->write('POST', route('admin.iuran.jenis.store'), $this->data(['aktif' => '0']))
            ->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasNoErrors();
        $new = JenisIuran::where('nama', 'Iuran kegiatan')->firstOrFail();
        $this->assertSame('120000.50', $new->nominal);
        $this->assertFalse($new->aktif);
        $this->assertSame('Kegiatan kampus', $new->deskripsi);
        $this->assertDatabaseCount('tagihan_iuran', 0);
        $this->assertDatabaseCount('transaksi_kas', 0);
        $this->write('POST', route('admin.iuran.jenis.store'), ['nama' => 'Kompatibel', 'nominal' => '1.25'])
            ->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasNoErrors();
        $this->assertTrue(JenisIuran::where('nama', 'Kompatibel')->firstOrFail()->aktif);
    }

    public function test_create_ignores_return_marker_and_success_page_has_closed_modal_and_new_row(): void
    {
        $this->write('POST', route('admin.iuran.jenis.store'), $this->data(['_return' => 'https://example.invalid']))
            ->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHas('success');
        $response = $this->get(route('admin.iuran.jenis.index'))->assertOk()
            ->assertSee('Jenis iuran berhasil ditambahkan.')->assertSee('Iuran kegiatan');
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="modal-jenis"]')->length);
        $modal = $xpath->query('//*[@id="modal-jenis"]')->item(0);
        $classes = preg_split('/\s+/', trim($modal->getAttribute('class')));
        // Bootstrap menyembunyikan .modal tanpa .show; bukan class hidden Tailwind.
        $this->assertContains('modal', $classes);
        $this->assertContains('fade', $classes);
        $this->assertNotContains('show', $classes);
        $this->assertSame('true', $modal->getAttribute('aria-hidden'));
        $this->assertFalse($modal->hasAttribute('aria-modal'));
        $this->assertTrue($modal->hasAttribute('data-bootstrap-finance-modal'));
        $response->assertSee('const errorModal = null;', false);
        $this->assertSame(0, $xpath->query('//input[@name="_return"]')->length);
        $this->assertSame(route('admin.iuran'), $xpath->query('//a[normalize-space(.)="Kembali ke tagihan"]')->item(0)->getAttribute('href'));
    }

    public function test_validation_without_referer_returns_to_management_and_preserves_create_input(): void
    {
        $this->from(route('admin.iuran'))->write('POST', route('admin.iuran.jenis.store'), $this->data([
            'nominal' => '0', '_modal' => 'modal-tagihan',
        ]))->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasErrors('nominal')
            ->assertSessionHasInput('nama', 'Iuran kegiatan')->assertSessionHasInput('nominal', '0')
            ->assertSessionHasInput('_modal', 'modal-jenis');
        $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertSee('Nominal harus lebih besar dari 0.')
            ->assertSee('value="Iuran kegiatan"', false)->assertSee('value="0"', false);
        $this->assertDatabaseCount('jenis_iuran', 1);
    }

    private function paginatedEditFailure(): string
    {
        for ($i = 0; $i < 11; $i++) {
            JenisIuran::create(['nama' => sprintf('Iuran A%02d', $i), 'nominal' => '10', 'aktif' => true]);
        }
        $filters = ['q' => 'Iuran', 'status' => 'aktif', 'page' => '2'];
        $url = route('admin.iuran.jenis.index', $filters);
        $this->get($url)->assertOk()->assertSee('edit-jenis-'.$this->type->id);
        $this->from(route('admin.iuran'))->write('PUT', route('admin.iuran.jenis.update', $filters + ['jenisIuran' => $this->type]), $this->data([
            'nama' => 'Koreksi tersimpan', 'nominal' => '0', 'deskripsi' => 'Input belum disimpan', '_modal' => 'salah',
        ]))->assertRedirect($url)->assertSessionHasErrors('nominal')
            ->assertSessionHasInput('_modal', 'edit-jenis-'.$this->type->id)
            ->assertSessionHasInput('nama', 'Koreksi tersimpan');
        $this->assertSame('Iuran rutin', $this->type->fresh()->nama);

        return $url;
    }

    public function test_edit_validation_preserves_filters_page_and_correct_modal_input(): void
    {
        $url = $this->paginatedEditFailure();
        $response = $this->get($url)->assertOk()->assertSee('Nominal harus lebih besar dari 0.');
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $prefix = '//*[@id="edit-jenis-'.$this->type->id.'"]';
        $this->assertSame('Koreksi tersimpan', $xpath->query($prefix.'//input[@name="nama"]')->item(0)->getAttribute('value'));
        $this->assertSame('0', $xpath->query($prefix.'//input[@name="nominal"]')->item(0)->getAttribute('value'));
        $this->assertSame('Input belum disimpan', $xpath->query($prefix.'//textarea')->item(0)->textContent);
        $this->assertSame('', $xpath->query('//*[@id="modal-jenis"]//input[@name="nama"]')->item(0)->getAttribute('value'));
    }

    public function test_successful_actions_keep_management_filters_and_flash_success(): void
    {
        $query = ['q' => 'Iuran', 'page' => '1'];
        $url = route('admin.iuran.jenis.index', $query);
        $this->write('POST', route('admin.iuran.jenis.store', $query), $this->data())->assertRedirect($url)->assertSessionHas('success');
        $this->write('PUT', route('admin.iuran.jenis.update', $query + ['jenisIuran' => $this->type]), $this->data(['nama' => 'Iuran diperbarui']))
            ->assertRedirect($url)->assertSessionHas('success');
        $this->get($url)->assertOk()->assertSee('Iuran diperbarui')->assertSee('Jenis iuran berhasil diubah.');
        $this->write('PATCH', route('admin.iuran.jenis.status', $query + ['jenisIuran' => $this->type]), ['aktif' => '0'])
            ->assertRedirect($url)->assertSessionHas('success');
        $this->assertFalse($this->type->fresh()->aktif);
        $this->write('DELETE', route('admin.iuran.jenis.destroy', $query + ['jenisIuran' => $this->type]), ['konfirmasi' => '1'])
            ->assertRedirect($url)->assertSessionHas('success');
        $this->get($url)->assertOk()->assertDontSee('Iuran diperbarui')->assertSee('berhasil dihapus');
        $this->assertDatabaseCount('jenis_iuran', 1);
    }

    public function test_invalid_context_does_not_control_redirect_destination(): void
    {
        $this->write('POST', route('admin.iuran.jenis.store', ['q' => ['invalid'], 'status' => 'salah', 'page' => '-1', 'redirect' => 'https://example.invalid']), $this->data())
            ->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasNoErrors();
    }

    public function test_modals_in_chrome_desktop_and_mobile(): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan KASORG_BROWSER_EXECUTABLE untuk menjalankan Chrome.');
        }
        $this->bill();
        JenisIuran::create($this->data());
        $html = $this->get(route('admin.iuran.jenis.index'))->assertOk()->getContent();
        $results = ChromiumPasswordCheck::runInteractive($html, $browser, 'jenis');
        $this->assertCount(26, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['passed'], $result['name']);
        }
    }

    #[DataProvider('failedFormModes')]
    public function test_failed_form_reopens_in_chrome_with_old_input_and_errors(string $mode): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan KASORG_BROWSER_EXECUTABLE untuk menjalankan Chrome.');
        }
        if ($mode === 'edit') {
            $url = $this->paginatedEditFailure();
        } else {
            $url = route('admin.iuran.jenis.index');
            $this->from(route('admin.iuran'))->write('POST', route('admin.iuran.jenis.store'), $this->data([
                'nama' => 'Koreksi tersimpan', 'nominal' => '0', 'deskripsi' => 'Input belum disimpan',
            ]))->assertRedirect($url)->assertSessionHasErrors('nominal');
        }
        $html = $this->get($url)->assertOk()->getContent();
        $results = ChromiumPasswordCheck::runInteractive($html, $browser, 'jenis-error');
        $this->assertCount(14, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['passed'], $result['name']);
        }
    }

    public static function failedFormModes(): array
    {
        return [['create'], ['edit']];
    }

    #[DataProvider('invalidData')]
    public function test_invalid_input_is_rejected_in_indonesian(string $field, mixed $value): void
    {
        $response = $this->postJson(route('admin.iuran.jenis.store'), $this->data([$field => $value]));
        // CSRF dijalankan terpisah: request JSON juga wajib membawa token.
        $response->assertStatus(419);
        $this->write('POST', route('admin.iuran.jenis.store'), $this->data([$field => $value]))
            ->assertSessionHasErrors($field);
        $this->assertDatabaseCount('jenis_iuran', 1);
        $errors = session('errors')->get($field);
        $this->assertNotEmpty($errors);
        $this->assertMatchesRegularExpression('/wajib|sudah|harus|Format|melebihi/', $errors[0]);
    }

    public static function invalidData(): array
    {
        return [
            'nama kosong' => ['nama', ''], 'nama duplikat' => ['nama', 'Iuran rutin'],
            'nol' => ['nominal', '0'], 'negatif' => ['nominal', '-1'], 'pecahan berlebih' => ['nominal', '1.001'],
            'notasi eksponen' => ['nominal', '1e3'], 'melebihi DECIMAL' => ['nominal', '10000000000000'],
            'status invalid' => ['aktif', 'ya'], 'deskripsi panjang' => ['deskripsi', str_repeat('a', 256)],
        ];
    }

    public function test_update_preserves_bill_and_payment_history_even_in_closed_period(): void
    {
        $bill = $this->bill();
        $payment = PembayaranIuran::create([
            'tagihan_iuran_id' => $bill->id, 'diajukan_oleh' => $this->member->id, 'metode' => 'tunai',
            'nominal' => $bill->nominal, 'tanggal_bayar' => '2100-01-10', 'status' => 'ditolak', 'alasan_penolakan' => 'Uji riwayat',
        ]);
        $this->period->update(['status' => 'ditutup']);
        $beforeBill = $bill->fresh()->getRawOriginal();
        $beforePayment = $payment->fresh()->getRawOriginal();
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data(['aktif' => '0']))
            ->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasNoErrors();
        $this->assertSame('120000.50', $this->type->fresh()->nominal);
        $this->assertFalse($this->type->fresh()->aktif);
        $this->assertSame($beforeBill, $bill->fresh()->getRawOriginal());
        $this->assertSame($beforePayment, $payment->fresh()->getRawOriginal());
        $this->assertDatabaseCount('transaksi_kas', 0);
        $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertSee('Riwayat dilindungi');
    }

    public function test_update_allows_same_name_and_rejects_other_name_and_invalid_nominal(): void
    {
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data(['nama' => $this->type->nama]))
            ->assertSessionHasNoErrors();
        JenisIuran::create(['nama' => 'Sudah ada', 'nominal' => '10', 'aktif' => true]);
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data(['nama' => 'Sudah ada']))->assertSessionHasErrors('nama');
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data(['nominal' => '0']))->assertSessionHasErrors('nominal');
        $this->assertSame('120000.50', $this->type->fresh()->nominal);
        $this->assertSame('Iuran rutin', $this->type->fresh()->nama);
    }

    public function test_status_toggle_preserves_history_and_prevents_new_bills(): void
    {
        $bill = $this->bill();
        $this->write('PATCH', route('admin.iuran.jenis.status', $this->type), ['aktif' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse($this->type->fresh()->aktif);
        $this->write('POST', route('admin.iuran.store'), [
            'user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id,
        ])->assertSessionHasErrors('jenis_iuran_id');
        $this->assertSame('75000.25', $bill->fresh()->nominal);
        $this->get(route('admin.iuran'))->assertOk()->assertViewHas('types', fn ($types) => $types->isEmpty());
        $this->write('PATCH', route('admin.iuran.jenis.status', $this->type), ['aktif' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($this->type->fresh()->aktif);
        $this->assertDatabaseCount('tagihan_iuran', 1);
    }

    public function test_approved_payment_ledger_and_all_summaries_stay_identical_after_master_edit(): void
    {
        $bill = $this->bill();
        $bill->update(['status' => 'lunas', 'lunas_at' => now()]);
        $payment = PembayaranIuran::create([
            'tagihan_iuran_id' => $bill->id, 'diajukan_oleh' => $this->member->id, 'metode' => 'tunai',
            'nominal' => $bill->nominal, 'tanggal_bayar' => '2100-01-10', 'status' => 'disetujui',
            'diverifikasi_oleh' => $this->admin->id, 'diverifikasi_at' => now(),
        ]);
        $category = KategoriTransaksi::create(['nama' => 'Iuran anggota', 'jenis' => 'pemasukan', 'is_sistem' => true]);
        $ledger = TransaksiKas::create([
            'tanggal' => '2100-01-10', 'jenis' => 'pemasukan', 'kategori_transaksi_id' => $category->id,
            'tagihan_iuran_id' => $bill->id, 'pembayaran_iuran_id' => $payment->id, 'keterangan' => 'Fixture riwayat',
            'nominal' => $bill->nominal, 'sumber' => 'pembayaran_iuran', 'dibuat_oleh' => $this->admin->id,
        ]);
        $before = [$bill->fresh()->getRawOriginal(), $payment->fresh()->getRawOriginal(), $ledger->fresh()->getRawOriginal()];
        $apiUrl = route('api.transparansi.ringkasan', ['periode_id' => $this->period->id]);
        $beforeApi = $this->getJson($apiUrl)->assertOk()->assertJsonPath('data.pemasukan', '75000.25')->json('data');
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data())->assertSessionHasNoErrors();
        $this->write('PATCH', route('admin.iuran.jenis.status', $this->type), ['aktif' => '0'])->assertSessionHasNoErrors();
        $this->write('DELETE', route('admin.iuran.jenis.destroy', $this->type), ['konfirmasi' => '1'])->assertSessionHasErrors('jenis_iuran');
        $this->assertSame($before, [$bill->fresh()->getRawOriginal(), $payment->fresh()->getRawOriginal(), $ledger->fresh()->getRawOriginal()]);
        $this->assertSame($beforeApi, $this->getJson($apiUrl)->assertOk()->json('data'));
        foreach ([$this->admin, $this->member] as $user) {
            $this->actingAs($user);
            foreach (['dashboard', 'transparansi'] as $page) {
                $summary = $this->get(route($user->role->kode.'.'.$page, ['periode_id' => $this->period->id]))->assertOk()->viewData('summary');
                $this->assertSame('75000.25', $summary['income']);
                $this->assertSame('75000.25', $summary['closing']);
            }
        }
        $this->actingAs($this->admin)->get(route('admin.transaksi', ['periode_id' => $this->period->id]))->assertOk()->assertSee('Fixture riwayat');
        $this->assertDatabaseCount('transaksi_kas', 1);
        $this->assertDatabaseCount('pembayaran_iuran', 1);
    }

    public function test_new_bill_uses_changed_standard_without_repricing_existing_bill(): void
    {
        $old = $this->bill();
        $other = $this->account('anggota');
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data())->assertSessionHasNoErrors();
        $this->write('POST', route('admin.iuran.store'), [
            'user_id' => $other->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id,
        ])->assertSessionHasNoErrors();
        $new = TagihanIuran::where('user_id', $other->id)->firstOrFail();
        $this->assertSame('120000.50', $new->nominal);
        $this->assertSame('75000.25', $old->fresh()->nominal);
        $this->assertDatabaseCount('transaksi_kas', 0);
        $this->assertDatabaseCount('pembayaran_iuran', 0);
        $this->period->update(['status' => 'ditutup']);
        $closedMember = $this->account('anggota');
        $this->write('POST', route('admin.iuran.store'), [
            'user_id' => $closedMember->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id,
        ])->assertSessionHasErrors('periode_id');
        $this->assertDatabaseCount('tagihan_iuran', 2);
    }

    public function test_publish_one_bill_updates_type_count_and_only_target_member_without_changing_cash(): void
    {
        $other = $this->account('anggota');
        $inactiveMember = $this->account('anggota');
        $inactiveMember->update(['status_akun' => 'nonaktif']);
        $archived = $this->account('anggota');
        $archived->delete();
        $inactiveType = JenisIuran::create(['nama' => 'Iuran nonaktif', 'nominal' => '10', 'aktif' => false]);
        $this->period->update(['saldo_awal' => '123.45']);
        $category = KategoriTransaksi::create(['nama' => 'Fixture pemasukan', 'jenis' => 'pemasukan']);
        TransaksiKas::create([
            'tanggal' => '2100-01-15', 'jenis' => 'pemasukan', 'kategori_transaksi_id' => $category->id,
            'nominal' => '42.35', 'keterangan' => 'Kas sebelum tagihan', 'sumber' => 'manual', 'dibuat_oleh' => $this->admin->id,
        ]);
        $apiUrl = route('api.transparansi.ringkasan', ['periode_id' => $this->period->id]);
        $beforeSummary = $this->getJson($apiUrl)->assertOk()->assertJsonPath('data.saldo_akhir', '165.80')->json('data');
        $beforeLedger = DB::table('transaksi_kas')->orderBy('id')->get()->toJson();
        $beforeType = $this->get(route('admin.iuran.jenis.index'))->assertOk()->viewData('types')->firstWhere('id', $this->type->id);
        $this->assertSame(0, $beforeType->tagihan_iuran_count);

        $html = $this->get(route('admin.iuran'))->assertOk()->getContent();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $base = '//*[@id="modal-tagihan"]';
        $this->assertSame(1, $xpath->query($base.'//select[@name="jenis_iuran_id"]/option[@value="'.$this->type->id.'"]')->length);
        $this->assertSame(0, $xpath->query($base.'//select[@name="jenis_iuran_id"]/option[@value="'.$inactiveType->id.'"]')->length);
        $this->assertSame(1, $xpath->query($base.'//select[@name="user_id"]/option[@value="'.$this->member->id.'"]')->length);
        foreach ([$this->admin, $inactiveMember, $archived] as $excluded) {
            $this->assertSame(0, $xpath->query($base.'//select[@name="user_id"]/option[@value="'.$excluded->id.'"]')->length);
        }
        $data = ['user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id, 'jatuh_tempo' => '2100-01-20'];
        $this->write('POST', route('admin.iuran.store'), $data)->assertRedirect(route('admin.iuran'))->assertSessionHasNoErrors();
        $bill = TagihanIuran::sole();
        $this->assertSame($this->member->id, $bill->user_id);
        $this->assertSame($this->type->id, $bill->jenis_iuran_id);
        $this->assertSame($this->period->id, $bill->periode_id);
        $this->assertSame('75000.25', $bill->nominal);
        $this->assertSame('belum_lunas', $bill->status);
        $this->assertNull($bill->lunas_at);
        $typeAfter = $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertSee('1 tagihan')
            ->viewData('types')->firstWhere('id', $this->type->id);
        $this->assertSame(1, $typeAfter->tagihan_iuran_count);
        $this->write('POST', route('admin.iuran.store'), $data)->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('tagihan_iuran', 1);

        $this->actingAs($this->member)->get(route('anggota.tagihan'))->assertOk()->assertSee($this->type->nama)
            ->assertSee('bayar-'.$bill->id)->assertViewHas('bills', fn ($bills) => $bills->modelKeys() === [$bill->id]);
        $this->actingAs($other)->get(route('anggota.tagihan'))->assertOk()
            ->assertViewHas('bills', fn ($bills) => $bills->isEmpty())->assertDontSee('id="bayar-'.$bill->id.'"', false);
        $this->write('POST', route('anggota.pembayaran.store', $bill), ['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()])
            ->assertForbidden();
        $this->assertDatabaseCount('pembayaran_iuran', 0);
        foreach ([$this->admin, $this->member] as $user) {
            $this->actingAs($user);
            foreach (['dashboard', 'transparansi'] as $page) {
                $summary = $this->get(route($user->role->kode.'.'.$page, ['periode_id' => $this->period->id]))->assertOk()->viewData('summary');
                $this->assertSame('165.80', $summary['closing']);
                $this->assertSame('42.35', $summary['income']);
            }
            $this->assertSame($beforeSummary, $this->getJson($apiUrl)->assertOk()->json('data'));
        }
        $this->assertSame($beforeLedger, DB::table('transaksi_kas')->orderBy('id')->get()->toJson());
    }

    public function test_bill_unique_scope_allows_other_member_type_period_but_never_the_same_tuple(): void
    {
        $other = $this->account('anggota');
        $otherType = JenisIuran::create(['nama' => 'Iuran lain', 'nominal' => '12000.50', 'aktif' => true]);
        $otherPeriod = Periode::create(['tahun' => 2100, 'bulan' => 2, 'tanggal_mulai' => '2100-02-01', 'tanggal_selesai' => '2100-02-28', 'saldo_awal' => '0', 'status' => 'terbuka']);
        $base = ['user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id];
        $cases = [
            $base, array_replace($base, ['user_id' => $other->id]),
            array_replace($base, ['jenis_iuran_id' => $otherType->id]), array_replace($base, ['periode_id' => $otherPeriod->id]),
        ];
        foreach ($cases as $data) {
            $this->write('POST', route('admin.iuran.store'), $data)->assertSessionHasNoErrors();
            $this->write('POST', route('admin.iuran.store'), $data)->assertSessionHasErrors('user_id');
        }
        $this->assertDatabaseCount('tagihan_iuran', 4);
        $this->assertSame(3, $this->type->tagihanIuran()->count());
        $this->assertSame(1, $otherType->tagihanIuran()->count());
        $this->assertDatabaseCount('pembayaran_iuran', 0);
        $this->assertDatabaseCount('transaksi_kas', 0);
    }

    public function test_service_denies_member_and_inactive_admin_even_without_http(): void
    {
        $service = app(JenisIuranService::class);
        foreach (['member', 'inactive'] as $scenario) {
            $actor = $this->member;
            if ($scenario === 'inactive') {
                $this->admin->update(['status_akun' => 'nonaktif']);
                $actor = $this->admin;
            }
            try {
                $service->setStatus($actor, $this->type, false);
                $this->fail('Service tidak boleh melewati authorization.');
            } catch (AuthorizationException) {
                $this->assertTrue($this->type->fresh()->aktif);
            }
        }
    }

    public function test_delete_requires_confirmation_and_rejects_used_type(): void
    {
        $url = route('admin.iuran.jenis.destroy', $this->type);
        $this->write('DELETE', $url)->assertSessionHasErrors('konfirmasi');
        $bill = $this->bill();
        $this->write('DELETE', $url, ['konfirmasi' => '1'])->assertSessionHasErrors('jenis_iuran');
        $this->assertDatabaseHas('jenis_iuran', ['id' => $this->type->id]);
        $this->assertDatabaseHas('tagihan_iuran', ['id' => $bill->id]);
        $unused = JenisIuran::create($this->data());
        $this->write('DELETE', route('admin.iuran.jenis.destroy', $unused), ['konfirmasi' => '1'])
            ->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('jenis_iuran', ['id' => $unused->id]);
    }

    public function test_native_delete_form_post_redirects_and_repeated_submission_is_safe(): void
    {
        $url = route('admin.iuran.jenis.destroy', $this->type);
        $this->write('POST', $url, ['_method' => 'DELETE', 'konfirmasi' => '1'])
            ->assertStatus(303)->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('jenis_iuran', ['id' => $this->type->id]);
        $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertSee('berhasil dihapus');
        $this->write('POST', $url, ['_method' => 'DELETE', 'konfirmasi' => '1'])
            ->assertStatus(303)->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasErrors('jenis_iuran');
        $this->get(route('admin.iuran.jenis.index'))->assertOk()->assertSee('tidak ditemukan atau sudah dihapus');
    }

    public function test_stale_delete_form_returns_to_management_with_notice(): void
    {
        $this->write('POST', route('admin.iuran.jenis.destroy', 999999), ['_method' => 'DELETE', 'konfirmasi' => '1'])
            ->assertStatus(303)->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasErrors('jenis_iuran');
        $this->assertDatabaseCount('jenis_iuran', 1);
        $this->assertSame('75000.25', $this->type->fresh()->nominal);
    }

    public function test_missing_binding_does_not_bypass_csrf_authentication_or_role(): void
    {
        $url = route('admin.iuran.jenis.destroy', 999999);
        $data = ['_method' => 'DELETE', 'konfirmasi' => '1'];
        $this->post($url, $data)->assertStatus(419);
        $this->actingAs($this->member);
        $this->write('POST', $url, $data)->assertForbidden();
        $this->admin->update(['status_akun' => 'nonaktif']);
        $this->actingAs($this->admin);
        $this->write('POST', $url, $data)->assertForbidden();
        Auth::logout();
        $this->write('POST', $url, $data)->assertRedirect(route('login'));
        $this->assertDatabaseCount('jenis_iuran', 1);
    }

    public function test_delete_handles_row_disappearing_after_binding_without_deleting_other_rows(): void
    {
        $other = JenisIuran::create($this->data());
        // Fixture RAM meniru request lain yang selesai di antara binding dan lock service.
        $this->app->instance(JenisIuranService::class, new class extends JenisIuranService
        {
            public function delete(User $admin, JenisIuran $jenisIuran): void
            {
                JenisIuran::whereKey($jenisIuran->id)->delete();
                parent::delete($admin, $jenisIuran);
            }
        });
        $this->write('POST', route('admin.iuran.jenis.destroy', $this->type), ['_method' => 'DELETE', 'konfirmasi' => '1'])
            ->assertStatus(303)->assertRedirect(route('admin.iuran.jenis.index'))->assertSessionHasErrors('jenis_iuran');
        $this->assertDatabaseHas('jenis_iuran', ['id' => $other->id, 'nama' => $other->nama]);
        $this->assertDatabaseCount('jenis_iuran', 1);
    }

    #[DataProvider('actions')]
    public function test_member_is_forbidden_and_csrf_is_required(string $method, string $action): void
    {
        $url = route('admin.iuran.jenis.'.$action, in_array($action, ['index', 'store']) ? [] : $this->type);
        $this->actingAs($this->member);
        $this->write($method, $url, $this->data(['konfirmasi' => '1']))->assertForbidden();
        $this->actingAs($this->admin);
        if ($method !== 'GET') {
            $this->call($method, $url, $this->data())->assertStatus(419);
        }
        $this->assertDatabaseCount('jenis_iuran', 1);
        $this->assertSame('75000.25', $this->type->fresh()->nominal);
    }

    public static function actions(): array
    {
        return [['GET', 'index'], ['POST', 'store'], ['PUT', 'update'], ['PATCH', 'status'], ['DELETE', 'destroy']];
    }

    public function test_guest_inactive_admin_and_policy_are_protected(): void
    {
        $this->assertTrue(Gate::forUser($this->admin)->allows('update', $this->type));
        $this->assertFalse(Gate::forUser($this->member)->allows('delete', $this->type));
        $this->admin->update(['status_akun' => 'nonaktif']);
        $this->assertFalse(Gate::forUser($this->admin)->allows('create', JenisIuran::class));
        $this->write('POST', route('admin.iuran.jenis.store'), $this->data())->assertRedirect(route('login'));
        $this->get(route('admin.iuran.jenis.index'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('jenis_iuran', 1);
    }

    public function test_maximum_decimal_is_sent_without_float_conversion_and_unknown_model_returns_404(): void
    {
        // SQLite NUMERIC bukan DECIMAL presisi: periksa nilai yang dikirim,
        // bukan mengklaim ketepatan penyimpanan MariaDB melalui SQLite.
        $sentNominal = null;
        JenisIuran::saving(function (JenisIuran $model) use (&$sentNominal) {
            $sentNominal = $model->getAttributes()['nominal'];
        });
        $this->write('PUT', route('admin.iuran.jenis.update', $this->type), $this->data(['nominal' => '9999999999999.99']))
            ->assertSessionHasNoErrors();
        $this->assertSame('9999999999999.99', $sentNominal);
        $this->write('PATCH', route('admin.iuran.jenis.status', 999999), ['aktif' => '0'])->assertNotFound();
        $this->assertDatabaseCount('pembayaran_iuran', 0);
        $this->assertDatabaseCount('transaksi_kas', 0);
    }
}
