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
use App\Services\RingkasanKasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ChromiumPasswordCheck;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

class EnforceCsrfForLaporanTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class LaporanKeuanganTest extends TestCase
{
    private User $admin;

    private User $member;

    private User $other;

    private Periode $january;

    private Periode $february;

    protected function setUp(): void
    {
        parent::setUp();
        // Tidak ada mode live: guard SEBELUM DDL atau insert fixture.
        if (! app()->environment('testing') || DB::getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Tes laporan hanya diizinkan pada SQLite RAM testing.');
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
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForLaporanTest::class);
        $this->admin = $this->account('admin');
        $this->member = $this->account('anggota');
        $this->other = $this->account('anggota');
        $this->january = Periode::create(['tahun' => 2030, 'bulan' => 1, 'tanggal_mulai' => '2030-01-01', 'tanggal_selesai' => '2030-01-31', 'saldo_awal' => '100.10', 'status' => 'terbuka']);
        $this->february = Periode::create(['tahun' => 2030, 'bulan' => 2, 'tanggal_mulai' => '2030-02-01', 'tanggal_selesai' => '2030-02-28', 'saldo_awal' => '130.40', 'status' => 'terbuka']);
        $this->actingAs($this->admin);
    }

    private function account(string $role): User
    {
        return User::create([
            'name' => 'Uji Laporan '.Str::random(8), 'nim' => '99'.random_int(1000000000, 9999999999),
            'email' => Str::uuid().'@kasorg.test', 'password' => Str::random(40),
            'role_id' => Role::firstOrCreate(['kode' => $role], ['nama' => ucfirst($role)])->id, 'status_akun' => 'aktif',
        ]);
    }

    private function report(array $filters = [], ?User $user = null)
    {
        $user ??= $this->admin;

        return $this->actingAs($user)->get(route($user->role->kode.'.laporan', array_replace(['periode_id' => $this->january->id], $filters)));
    }

    private function cash(string $type, string $amount, array $attributes = [], string $categoryName = ''): TransaksiKas
    {
        $category = KategoriTransaksi::firstOrCreate(['nama' => $categoryName ?: 'Laporan '.$type, 'jenis' => $type]);

        $record = TransaksiKas::create(array_replace([
            'tanggal' => '2030-01-10', 'jenis' => $type, 'kategori_transaksi_id' => $category->id,
            'nominal' => $amount, 'keterangan' => 'Transaksi laporan '.$type, 'sumber' => 'manual', 'dibuat_oleh' => $this->admin->id,
        ], $attributes));
        // SQLite DATE berafinitas teks; MariaDB DATE membuang bagian jam.
        // Samakan fixture fisik, bukan mengubah query/rentang tanggal produksi.
        DB::table('transaksi_kas')->where('id', $record->id)->update(['tanggal' => $record->tanggal->toDateString()]);

        return $record->fresh();
    }

    private function write(string $url, array $data)
    {
        $token = Str::random(40);

        return $this->withSession(['_token' => $token])->post($url, $data + ['_token' => $token]);
    }

    public function test_empty_period_keeps_opening_and_no_period_produces_zero(): void
    {
        $response = $this->report()->assertOk()->assertSee('Belum ada transaksi kas sah')->assertSee('Belum ada pengeluaran');
        $summary = $response->viewData('summary');
        $this->assertSame('100.10', $summary['opening']);
        $this->assertSame('100.10', $summary['closing']);
        $this->assertSame('0.00', $summary['income']);
        $this->assertSame('0.00', $summary['expense']);
        $this->assertSame([], $summary['allocations']);
        DB::table('periode')->delete(); // Hanya fixture RAM, tidak pernah kasorg_dev.
        $response = $this->report(['periode_id' => null])->assertOk()->assertSee('Belum ada periode')->assertSee('Rp 0,00');
        $this->assertSame('0.00', $response->viewData('summary')['closing']);
        $this->assertSame([], $response->viewData('summary')['bars']);
    }

    public function test_manual_income_expense_soft_delete_and_boundaries_use_exact_summary(): void
    {
        $this->cash('pemasukan', '20.20', ['tanggal' => '2030-01-01']);
        $this->cash('pemasukan', '0.10', ['tanggal' => '2030-01-31']);
        $expense = $this->cash('pengeluaran', '5.05');
        $archived = $this->cash('pemasukan', '999.99');
        $archived->delete();
        $this->cash('pemasukan', '500.00', ['tanggal' => '2030-02-01']);
        $summary = $this->report()->assertOk()->viewData('summary');
        $this->assertSame('20.30', $summary['income']);
        $this->assertSame('5.05', $summary['expense']);
        $this->assertSame('115.35', $summary['closing']);
        $this->assertSame(3, $summary['count']);
        $expense->delete();
        $summary = $this->report()->assertOk()->viewData('summary');
        $this->assertSame('120.40', $summary['closing']);
        $this->assertSame('0.00', $summary['expense']);
        $this->assertSame([], $summary['allocations']);
    }

    public function test_period_filters_control_cards_charts_allocation_and_table(): void
    {
        $this->cash('pemasukan', '20.30');
        $this->cash('pengeluaran', '5.05');
        $this->cash('pemasukan', '50.00', ['tanggal' => '2030-02-01', 'keterangan' => 'Hanya Februari']);
        $jan = $this->report()->assertOk()->assertDontSee('Hanya Februari')->viewData('summary');
        $febResponse = $this->report(['periode_id' => $this->february->id])->assertOk()->assertSee('Hanya Februari');
        $feb = $febResponse->viewData('summary');
        $this->assertSame('50.00', $feb['income']);
        $this->assertSame('180.40', $feb['closing']);
        $this->assertSame('50.00', $feb['bars'][0]['income']);
        $this->assertSame([], $feb['allocations']);
        $this->assertCount(1, $febResponse->viewData('transactions'));
        $all = $this->report(['periode_id' => 'semua'])->assertOk()->viewData('summary');
        $this->assertSame('100.10', $all['opening']);
        $this->assertSame('70.30', $all['income']);
        $this->assertSame('165.35', $all['closing']);
        $this->assertCount(2, $all['bars']);
        $this->assertSame($jan['bars'][0]['income'], $all['bars'][0]['income']);
        $this->assertSame($jan['allocations'], $all['allocations']);
    }

    public function test_negative_balance_keeps_decimal_precision_without_invalid_chart_coordinates(): void
    {
        $this->january->update(['saldo_awal' => '0']);
        $this->cash('pemasukan', '0.10');
        $this->cash('pemasukan', '0.20');
        $this->cash('pengeluaran', '0.45');
        $response = $this->report()->assertOk()->assertSee('- Rp 0,15')->assertDontSee('NaN')->assertDontSee('Infinity');
        $this->assertSame('0.30', $response->viewData('summary')['income']);
        $this->assertSame('-0.15', $response->viewData('summary')['closing']);
    }

    public function test_chart_and_donut_match_service_data_and_no_hardcoded_percentages(): void
    {
        $this->cash('pemasukan', '100.25');
        $this->cash('pengeluaran', '30.00', [], 'Kegiatan');
        $this->cash('pengeluaran', '70.00', [], 'Perlengkapan');
        KategoriTransaksi::create(['nama' => 'Kategori tanpa transaksi', 'jenis' => 'pengeluaran']);
        $response = $this->report()->assertOk()->assertDontSee('Kategori tanpa transaksi')->assertDontSee('18.750.000')->assertDontSee('38%');
        $summary = $response->viewData('summary');
        $this->assertSame('100.25', $summary['bars'][0]['income']);
        $this->assertSame('100.00', $summary['bars'][0]['expense']);
        $total = '0.00';
        $percent = '0.0000';
        foreach ($summary['allocations'] as $allocation) {
            $total = bcadd($total, $allocation['amount'], 2);
            $percent = bcadd($percent, $allocation['percentage'], 4);
            $response->assertSee('data-allocation-percentage="'.$allocation['percentage'].'"', false);
        }
        $this->assertSame($summary['expense'], $total);
        $this->assertSame('100.0000', $percent);
        $response->assertSee('data-income="100.25"', false)->assertSee('data-expense="100.00"', false);
    }

    public function test_pending_rejected_and_unpaid_bills_never_become_income_but_approval_does_once(): void
    {
        $category = KategoriTransaksi::create(['nama' => 'Iuran anggota', 'jenis' => 'pemasukan', 'is_sistem' => true]);
        $this->travelTo($this->january->tanggal_mulai->copy()->addDays(10));
        foreach (['menunggu', 'ditolak'] as $status) {
            $type = JenisIuran::create(['nama' => 'Iuran '.$status, 'nominal' => '75.25', 'aktif' => true]);
            $bill = TagihanIuran::create(['user_id' => $this->member->id, 'jenis_iuran_id' => $type->id, 'periode_id' => $this->january->id, 'nominal' => '75.25', 'status' => 'belum_lunas']);
            $payment = PembayaranIuran::create(['tagihan_iuran_id' => $bill->id, 'diajukan_oleh' => $this->member->id, 'nominal' => '75.25', 'metode' => 'tunai', 'tanggal_bayar' => today(), 'status' => $status]);
            // Fixture sengaja tidak sah: ledger ada tetapi pengajuan belum disetujui.
            $this->cash('pemasukan', '75.25', ['kategori_transaksi_id' => $category->id, 'sumber' => 'pembayaran_iuran', 'tagihan_iuran_id' => $bill->id, 'pembayaran_iuran_id' => $payment->id]);
        }
        $before = $this->report()->assertOk()->viewData('summary');
        $this->assertSame('0.00', $before['income']);
        $this->assertSame(0, $before['count']);
        $type = JenisIuran::create(['nama' => 'Iuran disetujui', 'nominal' => '75.25', 'aktif' => true]);
        $this->write(route('admin.iuran.store'), ['user_id' => $this->member->id, 'jenis_iuran_id' => $type->id, 'periode_id' => $this->january->id])->assertSessionHasNoErrors();
        $bill = TagihanIuran::where('jenis_iuran_id', $type->id)->firstOrFail();
        $this->assertSame('0.00', $this->report()->viewData('summary')['income']);
        $this->actingAs($this->member);
        $this->write(route('anggota.pembayaran.store', $bill), ['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()])->assertSessionHasNoErrors();
        $payment = $bill->pembayaranIuran()->sole();
        $this->assertSame('0.00', $this->report()->viewData('summary')['income']);
        $this->actingAs($this->admin);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasNoErrors();
        $after = $this->report()->assertOk()->viewData('summary');
        $this->assertSame('75.25', $after['income']);
        $this->assertSame('175.35', $after['closing']);
        $this->assertSame(1, $after['count']);
        $this->write(route('admin.verifikasi.update', $payment), ['keputusan' => 'disetujui', 'konfirmasi' => '1'])->assertSessionHasErrors('pembayaran');
        $this->assertSame(1, TransaksiKas::where('tagihan_iuran_id', $bill->id)->count());
    }

    public function test_reports_match_dashboard_transparency_transactions_and_api_for_both_roles(): void
    {
        $this->cash('pemasukan', '20.20');
        $this->cash('pemasukan', '0.10');
        $this->cash('pengeluaran', '5.05');
        foreach ([$this->admin, $this->member] as $user) {
            $summary = $this->report([], $user)->assertOk()->viewData('summary');
            foreach (['dashboard', 'transparansi'] as $page) {
                $response = $this->get(route($user->role->kode.'.'.$page, ['periode_id' => $this->january->id]))->assertOk();
                $this->assertSame($summary, $response->viewData('summary'));
            }
            $api = $this->getJson(route('api.transparansi.ringkasan', ['periode_id' => $this->january->id]))->assertOk();
            $this->assertSame($summary['closing'], $api->json('data.saldo_akhir'));
            $this->assertSame($summary['income'], $api->json('data.pemasukan'));
            $this->assertSame($summary['expense'], $api->json('data.pengeluaran'));
            $this->assertSame($summary['bars'][0]['income'], $api->json('data.grafik_bulanan.0.pemasukan'));
        }
        $report = $this->report()->viewData('summary');
        $this->assertSame($report, $this->get(route('admin.transaksi', ['periode_id' => $this->january->id]))->assertOk()->viewData('summary'));
    }

    #[DataProvider('invalidFilters')]
    public function test_invalid_parameters_are_rejected_with_indonesian_validation(array $query, string $field): void
    {
        $this->getJson(route('admin.laporan', $query))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->get(route('admin.laporan', $query))->assertRedirect(route('admin.laporan'))->assertSessionHasErrors($field);
    }

    public static function invalidFilters(): array
    {
        return [
            [['periode_id' => '-1'], 'periode_id'], [['periode_id' => 'tidak-valid'], 'periode_id'],
            [['periode_id' => '999999'], 'periode_id'], [['periode_id' => ['1']], 'periode_id'],
            [['page' => 0], 'page'], [['page' => ['2']], 'page'],
        ];
    }

    public function test_privacy_role_and_no_pdf_simulation(): void
    {
        $description = 'Privat '.$this->other->email.' '.$this->other->nim.' <script>alert(1)</script>';
        $this->cash('pemasukan', '10.25', ['keterangan' => $description]);
        $this->report()->assertOk()->assertSee($description)->assertDontSee('<script>alert(1)</script>', false);
        $response = $this->report([], $this->member)->assertOk()->assertDontSee($this->other->email)
            ->assertDontSee($this->other->nim)->assertDontSee('bukti_path')->assertDontSee('password')->assertSee('Pemasukan kas organisasi')
            ->assertSee(route('anggota.laporan.pdf'))->assertSee('id="unduhLaporan" type="submit"', false)
            ->assertDontSee('Ekspor PDF belum tersedia.')->assertDontSee('disabled aria-describedby="status-pdf"', false);
        $rows = $response->viewData('transactions')->items();
        $this->assertSame(['date', 'type', 'category', 'description', 'amount', 'positive'], array_keys($rows[0]));
        $this->get(route('admin.laporan'))->assertForbidden();
        $this->member->update(['status_akun' => 'nonaktif']);
        $this->get(route('anggota.laporan'))->assertRedirect(route('login'));
        Auth::logout();
        $this->get(route('admin.laporan'))->assertRedirect(route('login'));
        $this->get(route('anggota.laporan'))->assertRedirect(route('login'));
        $this->post(route('admin.laporan'))->assertStatus(405);
    }

    private function pdfText(string $pdf): string
    {
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', substr($pdf, -64));
        $binary = getenv('KASORG_PDFTOTEXT_EXECUTABLE') ?: 'C:/Program Files/Git/mingw64/bin/pdftotext.exe';
        if (! is_file($binary)) {
            $this->markTestSkipped('Tetapkan KASORG_PDFTOTEXT_EXECUTABLE untuk memvalidasi isi PDF melalui pdftotext.');
        }
        // Xpdf Windows perlu berkas input seekable, bukan stdin. Hanya fixture tes RAM.
        $file = tempnam(sys_get_temp_dir(), 'kasorg-pdf-test-');
        try {
            file_put_contents($file, $pdf);
            $process = proc_open([$binary, '-layout', '-enc', 'UTF-8', $file, '-'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $text = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);

            return $text;
        } finally {
            unlink($file);
        }
    }

    public function test_real_pdf_is_openable_and_matches_period_summary_and_all_transaction_pages(): void
    {
        $this->travelTo(now()->setDate(2030, 1, 15)->setTime(3, 4));
        for ($i = 1; $i <= 25; $i++) {
            $this->cash('pemasukan', '1.01', ['keterangan' => 'Rincian unik '.str_pad((string) $i, 2, '0', STR_PAD_LEFT)]);
        }
        $this->cash('pengeluaran', '5.05');
        $deleted = $this->cash('pemasukan', '999.99', ['keterangan' => 'Hapus dari laporan']);
        $deleted->delete();
        $this->cash('pemasukan', '500.00', ['tanggal' => '2030-02-01', 'keterangan' => 'Hanya PDF Februari']);
        $screen = $this->report(['page' => 2])->assertOk();
        $this->assertCount(10, $screen->viewData('transactions'));
        $before = DB::table('transaksi_kas')->orderBy('id')->get()->toJson();
        $queries = [];
        DB::listen(function ($event) use (&$queries) {
            $queries[] = $event->sql;
        });
        $response = $this->get(route('admin.laporan.pdf', ['periode_id' => $this->january->id, 'page' => 2]))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('laporan-kasorg-'.$this->january->id, $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $text = $this->pdfText($response->getContent());
        $this->assertStringContainsString('KasOrg', $text);
        $this->assertStringContainsString('Januari 2030', $text);
        $this->assertStringContainsString('01/01/2030', $text);
        $this->assertStringContainsString('31/01/2030', $text);
        $this->assertStringContainsString('Tanggal cetak:', $text);
        $this->assertStringContainsString('WIB', $text);
        foreach (['opening', 'income', 'expense', 'closing'] as $key) {
            $this->assertStringContainsString(RingkasanKasService::rupiah($screen->viewData('summary')[$key]), $text);
        }
        $this->assertSame('120.30', $screen->viewData('summary')['closing']);
        for ($i = 1; $i <= 25; $i++) {
            $this->assertStringContainsString('Rincian unik '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), $text);
        }
        $this->assertStringContainsString('26 transaksi', $text);
        $this->assertStringNotContainsString('Hanya PDF Februari', $text);
        $this->assertStringNotContainsString('Hapus dari laporan', $text);
        $this->assertFalse(collect($queries)->contains(fn ($sql) => preg_match('/^\s*(insert|update|delete|alter|drop|create)\b/i', $sql)));
        $this->assertSame($before, DB::table('transaksi_kas')->orderBy('id')->get()->toJson());
        $feb = $this->pdfText($this->get(route('admin.laporan.pdf', ['periode_id' => $this->february->id]))->assertOk()->getContent());
        $this->assertStringContainsString('Februari 2030', $feb);
        $this->assertStringContainsString('Hanya PDF Februari', $feb);
        $this->assertStringContainsString('Rp 630,40', $feb);
        $this->assertStringNotContainsString('Rincian unik', $feb);
    }

    public function test_pdf_empty_default_and_all_periods_keep_exact_opening(): void
    {
        $empty = $this->pdfText($this->get(route('admin.laporan.pdf', ['periode_id' => $this->january->id]))->assertOk()->getContent());
        $this->assertStringContainsString('Rp 100,10', $empty);
        $this->assertStringContainsString('Belum ada transaksi kas sah', $empty);
        $this->cash('pemasukan', '0.10');
        $this->cash('pengeluaran', '200.25');
        $all = $this->pdfText($this->get(route('admin.laporan.pdf', ['periode_id' => 'semua']))->assertOk()->getContent());
        $this->assertStringContainsString('- Rp 100,05', $all);
        $this->assertStringContainsString('Rp 100,10', $all);
        $this->assertStringNotContainsString('Rp 230,50', $all); // Bukan penjumlahan dua saldo awal.
        $default = $this->get(route('admin.laporan.pdf'))->assertOk();
        $this->pdfText($default->getContent());
        DB::table('transaksi_kas')->delete(); // Fixture RAM saja.
        DB::table('periode')->delete();
        $noPeriod = $this->pdfText($this->get(route('admin.laporan.pdf'))->assertOk()->getContent());
        $this->assertStringContainsString('Belum ada periode', $noPeriod);
        $this->assertStringContainsString('Rp 0,00', $noPeriod);
    }

    public function test_pdf_authorization_validation_and_member_privacy_are_enforced(): void
    {
        $secret = 'Rahasia '.$this->other->email.' '.$this->other->nim.' bukti-transfer-private.png';
        $this->cash('pemasukan', '10.25', ['keterangan' => $secret]);
        $adminText = $this->pdfText($this->get(route('admin.laporan.pdf', ['periode_id' => $this->january->id]))->assertOk()->getContent());
        $this->assertStringContainsString('Rahasia', $adminText);
        $this->actingAs($this->member);
        $text = $this->pdfText($this->get(route('anggota.laporan.pdf', ['periode_id' => $this->january->id]))->assertOk()->getContent());
        $this->assertStringContainsString('Pemasukan kas organisasi', $text);
        $this->assertStringContainsString('Rp 110,35', $text);
        foreach ([$this->other->email, $this->other->nim, 'bukti-transfer-private.png', 'password'] as $private) {
            $this->assertStringNotContainsString($private, $text);
        }
        $this->get(route('admin.laporan.pdf'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('anggota.laporan.pdf'))->assertForbidden();
        foreach (['admin' => $this->admin, 'anggota' => $this->member] as $role => $user) {
            $this->actingAs($user);
            $this->getJson(route($role.'.laporan.pdf', ['periode_id' => 999999]))->assertUnprocessable()->assertJsonValidationErrors('periode_id');
            $this->get(route($role.'.laporan.pdf', ['periode_id' => ['1']]))->assertRedirect(route($role.'.laporan'))->assertSessionHasErrors('periode_id');
            $this->post(route($role.'.laporan.pdf'))->assertStatus(405);
        }
        $this->member->update(['status_akun' => 'nonaktif']);
        $this->get(route('anggota.laporan.pdf'))->assertRedirect(route('login'));
        Auth::logout();
        $this->get(route('admin.laporan.pdf'))->assertRedirect(route('login'));
        $this->get(route('anggota.laporan.pdf'))->assertRedirect(route('login'));
    }

    public function test_pdf_export_limit_rejects_instead_of_silently_truncating(): void
    {
        $row = $this->cash('pemasukan', '0.01');
        $attributes = DB::table('transaksi_kas')->where('id', $row->id)->first();
        $attributes = (array) $attributes;
        unset($attributes['id']);
        foreach (array_chunk(array_fill(0, 2000, $attributes), 100) as $chunk) {
            DB::table('transaksi_kas')->insert($chunk);
        }
        $this->getJson(route('admin.laporan.pdf', ['periode_id' => $this->january->id]))->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $this->get(route('admin.laporan.pdf', ['periode_id' => $this->january->id]))
            ->assertRedirect(route('admin.laporan', ['periode_id' => $this->january->id]))->assertSessionHasErrors('periode_id');
        $this->assertSame(2001, TransaksiKas::count());
    }

    public function test_pagination_is_only_table_scoped_and_queries_are_bounded_and_read_only(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->cash('pemasukan', '1.01');
        }
        $this->report()->assertOk(); // Warm up role relation before measuring.
        $before = DB::table('transaksi_kas')->orderBy('id')->get()->toJson();
        $queries = [];
        DB::listen(function ($event) use (&$queries) {
            $queries[] = $event->sql;
        });
        $first = $this->report()->assertOk();
        $count = count($queries);
        $this->assertLessThanOrEqual(12, $count);
        $this->assertFalse(collect($queries)->contains(fn ($sql) => preg_match('/^\s*(insert|update|delete|alter|drop|create)\b/i', $sql)));
        $second = $this->report(['page' => 2])->assertOk();
        $this->assertCount(10, $second->viewData('transactions'));
        $this->assertSame(25, $second->viewData('transactions')->total());
        $this->assertSame($first->viewData('summary'), $second->viewData('summary'));
        $this->assertSame('25.25', $second->viewData('summary')['income']);
        $this->assertSame($before, DB::table('transaksi_kas')->orderBy('id')->get()->toJson());
        $second->assertSee('periode_id='.$this->january->id, false);
    }

    public function test_preview_reports_stay_local_mock_and_never_query_database(): void
    {
        $this->assertFalse(Route::has('ui-preview.admin.laporan'));
        Auth::logout();
        app()->instance('env', 'local');
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::getRoutes()->refreshNameLookups();
        $queries = [];
        DB::listen(function ($event) use (&$queries) {
            $queries[] = $event->sql;
        });
        foreach (['admin', 'anggota'] as $role) {
            $this->get(route('ui-preview.'.$role.'.laporan'))->assertOk()
                ->assertViewIs('laporan.preview')
                ->assertSee('Preview lokal tanpa autentikasi — bukan bukti pengujian aplikasi.')
                ->assertSee('Maret 2026')->assertSee('Rp 18.750.000')
                ->assertDontSee('action="'.route($role.'.laporan.pdf').'"', false)
                ->assertDontSee('action="'.route('logout').'"', false);
        }
        $this->assertSame([], $queries);
        $this->assertGuest();
        app()->instance('env', 'production');
        $this->get(route('ui-preview.admin.laporan'))->assertNotFound();
        $this->get(route('ui-preview.anggota.laporan'))->assertNotFound();
    }

    public function test_report_interactions_and_responsive_layout_in_real_chrome(): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan KASORG_BROWSER_EXECUTABLE untuk menjalankan Chrome CDP.');
        }
        $this->january->update(['saldo_awal' => '0']);
        $this->cash('pemasukan', '100.25');
        $this->cash('pengeluaran', '30.10');
        foreach ([$this->admin, $this->member] as $user) {
            $html = $this->report([], $user)->assertOk()->getContent();
            // Submit ditangkap di file fixture: tidak menghubungi kasorg_dev.
            // Request unduhan PDF sesungguhnya diuji lewat HTTP kernel pada tes PDF.
            $checks = ChromiumPasswordCheck::runInteractive($html, $browser, 'laporan');
            $this->assertCount(45, $checks);
            foreach ($checks as $check) {
                $this->assertTrue($check['passed'], $check['name']);
            }
        }
    }
}
