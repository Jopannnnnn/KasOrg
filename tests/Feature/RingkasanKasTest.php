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
use App\Services\IuranService;
use App\Services\RingkasanKasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ChromiumPasswordCheck;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

class EnforceCsrfForSummaryTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class RingkasanKasTest extends TestCase
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
        $this->live = getenv('KASORG_SUMMARY_MARIADB') === '1';
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
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForSummaryTest::class);
        Storage::fake('bukti');
        $this->password = Str::random(32).'Aa1!';
        $this->admin = $this->account('admin');
        $this->member = $this->account('anggota');
        $this->other = $this->account('anggota');
        $this->type = JenisIuran::create(['nama' => 'Uji Iuran '.Str::uuid(), 'nominal' => '75000.25', 'aktif' => true]);
        $year = 2030;
        while (Periode::where('tahun', $year)->exists()) {
            $year++;
        }
        if ($year >= 2038) {
            throw new \RuntimeException('Tidak tersedia tahun fixture sebelum batas TIMESTAMP MariaDB 10.4.');
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

    private function cash(string $type, string $amount, array $attributes = []): TransaksiKas
    {
        $category = KategoriTransaksi::firstOrCreate(['nama' => 'Uji Kategori '.ucfirst($type), 'jenis' => $type], ['is_sistem' => false]);

        return TransaksiKas::create(array_replace([
            'tanggal' => $this->period->tanggal_mulai->copy()->addDays(10),
            'jenis' => $type, 'kategori_transaksi_id' => $category->id, 'nominal' => $amount,
            'keterangan' => 'Rahasia '.$this->other->email.' '.$this->other->nim,
            'sumber' => 'manual', 'dibuat_oleh' => $this->admin->id,
        ], $attributes));
    }

    private function bill(?User $member = null): TagihanIuran
    {
        return TagihanIuran::create(['user_id' => ($member ?? $this->member)->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id, 'nominal' => '75000.25', 'status' => 'belum_lunas']);
    }

    private function payment(TagihanIuran $bill, string $status): PembayaranIuran
    {
        return PembayaranIuran::create([
            'tagihan_iuran_id' => $bill->id, 'diajukan_oleh' => $bill->user_id, 'metode' => 'tunai',
            'nominal' => $bill->nominal, 'tanggal_bayar' => $this->period->tanggal_mulai,
            'status' => $status, 'catatan' => 'Privat pembayaran '.$bill->user_id,
            'alasan_penolakan' => $status === 'ditolak' ? 'Privat penolakan '.$bill->user_id : null,
        ]);
    }

    private function dashboard(array $filters = [])
    {
        return $this->get(route('admin.dashboard', $filters + ['periode_id' => $this->period->id]))->assertOk();
    }

    public function test_manual_income_expense_deleted_and_cross_page_totals_are_exact_and_read_only(): void
    {
        $this->period->update(['saldo_awal' => '100.10']);
        $this->cash('pemasukan', '20.20');
        $this->cash('pemasukan', '0.10');
        $this->cash('pengeluaran', '5.05');
        $deleted = $this->cash('pemasukan', '999.99');
        $deleted->delete();
        $snapshot = $this->fingerprint();
        $queries = [];
        DB::listen(function ($event) use (&$queries) {
            $queries[] = $event->sql;
        });
        $dash = $this->dashboard();
        $summary = $dash->viewData('summary');
        $this->assertSame('20.30', $summary['income']);
        $this->assertSame('5.05', $summary['expense']);
        $this->assertSame('115.35', $summary['closing']);
        $this->assertSame(3, $summary['count']);
        $dash->assertSee('Rp 115,35')->assertDontSee('18.750.000');
        $transparency = $this->get(route('admin.transparansi', ['periode_id' => $this->period->id]))->assertOk();
        $this->assertSame($summary, $transparency->viewData('summary'));
        $transactions = $this->get(route('admin.transaksi', ['periode_id' => $this->period->id]))->assertOk();
        $this->assertSame($summary, $transactions->viewData('summary'));
        $this->assertSame(3, $transactions->viewData('transactions')->total());
        $this->assertFalse(collect($queries)->contains(fn ($sql) => preg_match('/^\s*(insert|update|delete|alter|drop|create)\b/i', $sql)));
        $this->assertTrue($snapshot === $this->fingerprint());
    }

    public function test_pending_rejected_and_orphan_payment_transactions_never_count(): void
    {
        $bill = $this->bill();
        $pending = $this->payment($bill, 'menunggu');
        // Fixture sengaja tidak konsisten untuk menguji pembacaan defensif.
        $this->cash('pemasukan', $bill->nominal, ['sumber' => 'pembayaran_iuran', 'tagihan_iuran_id' => $bill->id, 'pembayaran_iuran_id' => $pending->id]);
        $this->assertSame('0.00', $this->dashboard()->viewData('summary')['income']);
        $this->assertIncomeAcrossPages('0.00');
        $pending->update(['status' => 'ditolak']);
        $this->assertSame('0.00', $this->dashboard()->viewData('summary')['income']);
        $this->assertIncomeAcrossPages('0.00');
        $pending->update(['status' => 'disetujui']);
        $this->assertSame('75000.25', $this->dashboard()->viewData('summary')['income']);
        $this->assertIncomeAcrossPages('75000.25');
        $pending->update(['nominal' => '1.00']);
        $this->assertSame('0.00', $this->dashboard()->viewData('summary')['income']);
        $this->assertIncomeAcrossPages('0.00');
    }

    public function test_real_payment_approval_adds_cash_once_and_rejected_attempt_adds_nothing(): void
    {
        $bill = $this->bill();
        $rejected = $this->payment($bill, 'ditolak');
        $pending = $this->payment($bill, 'menunggu');
        $this->travelTo($this->period->tanggal_mulai->copy()->addDays(14));
        $this->assertSame('0.00', $this->dashboard()->viewData('summary')['income']);
        app(IuranService::class)->verify($this->admin, $pending, ['keputusan' => 'disetujui', 'konfirmasi' => 1]);
        $this->assertSame('75000.25', $this->dashboard()->viewData('summary')['income']);
        $this->assertSame(1, TransaksiKas::where('tagihan_iuran_id', $bill->id)->count());
        $this->assertSame('ditolak', $rejected->fresh()->status);
        try {
            app(IuranService::class)->verify($this->admin, $pending->fresh(), ['keputusan' => 'disetujui', 'konfirmasi' => 1]);
            $this->fail('Persetujuan ganda harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame('75000.25', $this->dashboard()->viewData('summary')['income']);
        }
        $this->travelBack();
    }

    public function test_period_filter_chart_and_single_opening_across_periods(): void
    {
        $this->period->update(['saldo_awal' => '100.00']);
        $this->cash('pemasukan', '25.10');
        $this->cash('pengeluaran', '5.05');
        $next = Periode::create(['tahun' => $this->period->tahun, 'bulan' => 2, 'tanggal_mulai' => $this->period->tahun.'-02-01', 'tanggal_selesai' => $this->period->tahun.'-02-28', 'saldo_awal' => '120.05', 'status' => 'terbuka']);
        $this->cash('pemasukan', '10.01', ['tanggal' => $next->tanggal_mulai]);
        $first = $this->dashboard()->viewData('summary');
        $second = $this->dashboard(['periode_id' => $next->id])->viewData('summary');
        $this->assertSame('120.05', $first['closing']);
        $this->assertSame('130.06', $second['closing']);
        $this->assertCount(1, $first['bars']);
        $this->assertSame('25.10', $first['bars'][0]['income']);
        $this->assertSame('5.05', $first['bars'][0]['expense']);
        // Periode live mungkin memiliki riwayat pengguna: scope semua diuji dengan start awal yang sebenarnya.
        $all = $this->dashboard(['periode_id' => 'semua'])->viewData('summary');
        $sumIncome = array_reduce($all['bars'], fn ($a, $b) => bcadd($a, $b['income'], 2), '0.00');
        $sumExpense = array_reduce($all['bars'], fn ($a, $b) => bcadd($a, $b['expense'], 2), '0.00');
        $this->assertSame($all['income'], $sumIncome);
        $this->assertSame($all['expense'], $sumExpense);
        $this->assertSame(bcsub(bcadd($all['opening'], $all['income'], 2), $all['expense'], 2), $all['closing']);
        if (! $this->live) {
            $this->assertSame('100.00', $all['opening']);
            $this->assertSame('130.06', $all['closing']);
        }
    }

    public function test_closed_snapshot_still_detects_difference_without_rendering_extra_panel_or_overwriting(): void
    {
        $this->period->update(['saldo_awal' => '100', 'status' => 'ditutup', 'saldo_akhir_snapshot' => '110.25']);
        $this->cash('pemasukan', '10.25');
        $this->assertFalse($this->dashboard()->viewData('summary')['snapshotMismatch']);
        $this->period->update(['saldo_akhir_snapshot' => '150.00']);
        $response = $this->dashboard()->assertDontSee('Snapshot berbeda');
        $this->assertTrue($response->viewData('summary')['snapshotMismatch']);
        $this->assertSame('110.25', $response->viewData('summary')['closing']);
        $this->assertSame('150.00', $this->period->fresh()->saldo_akhir_snapshot);
    }

    public function test_financial_pages_keep_real_summary_and_controls_without_extra_period_panel(): void
    {
        $this->period->update(['saldo_awal' => '100.00']);
        $this->cash('pemasukan', '25.10');
        $this->cash('pengeluaran', '5.05');

        foreach (['admin' => $this->admin, 'anggota' => $this->member] as $role => $user) {
            $this->actingAs($user);
            $pages = ['dashboard', 'transparansi', 'laporan'];
            if ($role === 'admin') {
                $pages[] = 'transaksi';
            }
            foreach ($pages as $page) {
                $response = $this->get(route($role.'.'.$page, ['periode_id' => $this->period->id]))
                    ->assertOk()
                    ->assertSee('name="periode_id"', false)
                    ->assertDontSee('rounded-xl border border-slate-200 bg-white p-4 text-xs leading-relaxed text-slate-600', false)
                    ->assertDontSee('Snapshot penutupan:')
                    ->assertDontSee('Snapshot berbeda')
                    ->assertDontSee('Saldo awal hanya diambil dari periode paling awal');
                $summary = $response->viewData('summary');
                $this->assertSame('100.00', $summary['opening']);
                $this->assertSame('25.10', $summary['income']);
                $this->assertSame('5.05', $summary['expense']);
                $this->assertSame('120.05', $summary['closing']);
                if ($page === 'laporan') {
                    $response->assertSee('Unduh PDF')->assertSee(route($role.'.laporan.pdf'), false);
                }
            }
        }
    }

    public function test_categories_donut_and_transaction_type_filter_pagination(): void
    {
        $this->cash('pengeluaran', '30.00');
        $category = KategoriTransaksi::create(['nama' => 'Uji Konsumsi '.Str::uuid(), 'jenis' => 'pengeluaran', 'is_sistem' => false]);
        $this->cash('pengeluaran', '70.00', ['kategori_transaksi_id' => $category->id]);
        for ($i = 0; $i < 11; $i++) {
            $this->cash('pemasukan', '1.00');
        }
        $page = $this->get(route('admin.transparansi', ['periode_id' => $this->period->id, 'jenis' => 'pengeluaran']))->assertOk();
        $summary = $page->viewData('summary');
        $this->assertSame('100.00', $summary['expense']);
        $this->assertSame(['30.00', '70.00'], array_column($summary['allocations'], 'amount'));
        $this->assertSame(['30.0000', '70.0000'], array_column($summary['allocations'], 'percentage'));
        $page->assertSee('stroke-dasharray="30.0000 70.0000"', false)->assertSee('stroke-dasharray="70.0000 30.0000"', false);
        $this->assertSame(2, $page->viewData('transactions')->total());
        $this->assertSame(11, $this->get(route('admin.transparansi', ['periode_id' => $this->period->id, 'jenis' => 'pemasukan']))->assertOk()->viewData('transactions')->total());
        $this->assertCount(3, $this->get(route('admin.transparansi', ['periode_id' => $this->period->id, 'page' => 2]))->assertOk()->viewData('transactions'));
    }

    public function test_member_dashboard_only_has_own_bills_payments_and_safe_organization_rows(): void
    {
        $own = $this->bill();
        $others = $this->bill($this->other);
        $this->payment($own, 'menunggu');
        $otherPayment = $this->payment($others, 'ditolak');
        $this->cash('pemasukan', '20.00');
        $this->actingAs($this->member);
        $response = $this->get(route('anggota.dashboard', ['periode_id' => $this->period->id]))->assertOk();
        $response->assertSee('Privat pembayaran '.$this->member->id)
            ->assertDontSee('Privat pembayaran '.$this->other->id)->assertDontSee($otherPayment->alasan_penolakan)
            ->assertDontSee($this->other->email)->assertDontSee($this->other->nim)->assertDontSee('bukti_path');
        $this->assertSame(['total' => 1, 'unpaid' => 1, 'waiting' => 1], $response->viewData('billStats'));
        $this->assertCount(1, $response->viewData('personalPayments'));
        $this->assertCount(1, $response->viewData('personalBills'));
        $transparency = $this->get(route('anggota.transparansi', ['periode_id' => $this->period->id]))->assertOk();
        $transparency->assertDontSee($this->other->email)->assertDontSee($this->other->nim)->assertDontSee('Privat pembayaran')->assertDontSee('/bukti');
        $this->assertSame($response->viewData('summary'), $transparency->viewData('summary'));
    }

    public function test_admin_counts_active_members_unpaid_bills_and_pending_payments(): void
    {
        $baseline = User::where('status_akun', 'aktif')->whereHas('role', fn ($q) => $q->where('kode', 'anggota'))->count();
        $this->other->update(['status_akun' => 'nonaktif']);
        $bill = $this->bill();
        $this->payment($bill, 'menunggu');
        $response = $this->dashboard();
        $this->assertSame($baseline - 1, $response->viewData('activeMembers'));
        $this->assertSame(['total' => 1, 'unpaid' => 1, 'waiting' => 1], $response->viewData('billStats'));
    }

    public function test_empty_period_and_empty_database_have_zero_values_and_empty_states(): void
    {
        $response = $this->dashboard()->assertSee('Belum ada transaksi kas sah');
        $this->assertSame('0.00', $response->viewData('summary')['closing']);
        $this->assertSame([], $response->viewData('summary')['allocations']);
        // Kosong total hanya di fixture SQLite RAM; periode development asli tidak dihapus.
        if (! $this->live) {
            $this->period->delete();
            $this->get(route('admin.dashboard'))->assertOk()->assertSee('Belum ada periode')->assertSee('Rp 0,00');
            $this->get(route('admin.transparansi'))->assertOk()->assertSee('Belum ada pengeluaran');
            $this->actingAs($this->member)->get(route('anggota.dashboard'))->assertOk()->assertSee('Belum ada tagihan');
        }
    }

    #[DataProvider('invalidFilters')]
    public function test_filters_use_indonesian_validation(array $filters, string $field): void
    {
        $this->get(route('admin.transparansi', $filters))->assertRedirect(route('admin.transparansi'))->assertSessionHasErrors($field);
    }

    public static function invalidFilters(): array
    {
        return [[['periode_id' => 'tidak-valid'], 'periode_id'], [['periode_id' => -1], 'periode_id'], [['periode_id' => ['semua']], 'periode_id'], [['jenis' => 'rahasia'], 'jenis'], [['page' => 0], 'page']];
    }

    public function test_guests_inactive_and_members_are_restricted(): void
    {
        Auth::logout();
        foreach (['admin.dashboard', 'anggota.dashboard', 'admin.transparansi', 'anggota.transparansi', 'admin.transaksi'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
        $this->actingAs($this->member);
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.transparansi'))->assertForbidden();
        $this->get(route('admin.transaksi'))->assertForbidden();
        $this->member->update(['status_akun' => 'nonaktif']);
        $this->get(route('anggota.dashboard'))->assertRedirect(route('login'));
    }

    public function test_rupiah_format_uses_no_float_and_keeps_cents(): void
    {
        $this->assertSame('Rp 9.999.999.999.999,99', RingkasanKasService::rupiah('9999999999999.99'));
        $this->assertSame('- Rp 123,45', RingkasanKasService::rupiah('-123.45'));
    }

    public function test_cent_precision_and_negative_balance(): void
    {
        $this->cash('pemasukan', '0.10');
        $this->cash('pemasukan', '0.20');
        $this->cash('pengeluaran', '0.45');
        $summary = $this->dashboard()->viewData('summary');
        $this->assertSame('0.30', $summary['income']);
        $this->assertSame('-0.15', $summary['closing']);
        $this->dashboard()->assertSee('- Rp 0,15');
        if ($this->live) {
            $this->cash('pemasukan', '9999999999999.99');
            $summary = $this->dashboard()->viewData('summary');
            $this->assertSame('10000000000000.29', $summary['income']);
            $this->assertSame('9999999999999.84', $summary['closing']);
        }
    }

    public function test_preview_dashboards_and_transparency_are_isolated_from_database(): void
    {
        Auth::logout();
        app()->instance('env', 'local');
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::getRoutes()->refreshNameLookups();
        $queries = [];
        DB::listen(function ($event) use (&$queries) {
            $queries[] = $event->sql;
        });
        $previews = [
            'admin.dashboard' => 'admin.dashboard-preview',
            'anggota.dashboard' => 'anggota.dashboard-preview',
            'admin.transparansi' => 'transparansi.preview',
            'anggota.transparansi' => 'transparansi.preview',
            'admin.transaksi' => 'admin.transaksi.preview',
        ];
        foreach ($previews as $name => $view) {
            $this->get(route('ui-preview.'.$name))->assertOk()->assertViewIs($view)
                ->assertSee('Preview lokal tanpa autentikasi — bukan bukti pengujian aplikasi.')
                ->assertDontSee('action="'.route('logout').'"', false);
        }
        $this->assertSame([], $queries);
        $this->assertGuest();
        app()->instance('env', 'production');
        foreach (array_keys($previews) as $name) {
            $this->get(route('ui-preview.'.$name))->assertNotFound();
        }
    }

    public function test_financial_pages_render_real_graphs_and_responsive_layout_in_chrome(): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan executable Chrome untuk pengujian grafik desktop/mobile.');
        }
        $this->cash('pemasukan', '100.25');
        $this->cash('pengeluaran', '30.10');
        foreach (['admin.dashboard', 'admin.transparansi', 'anggota.dashboard', 'admin.transaksi'] as $route) {
            $this->actingAs(str_starts_with($route, 'anggota.') ? $this->member : $this->admin);
            $html = $this->get(route($route, ['periode_id' => $this->period->id]))->assertOk()->getContent();
            $results = ChromiumPasswordCheck::runInteractive($html, $browser, 'finance');
            $this->assertCount(10, $results);
            foreach ($results as $result) {
                $this->assertTrue($result['passed'], $result['name']);
            }
        }
    }

    private function assertIncomeAcrossPages(string $expected): void
    {
        $summaries = [];
        foreach (['admin.dashboard', 'admin.transaksi', 'admin.transparansi'] as $route) {
            $summaries[] = $this->get(route($route, ['periode_id' => $this->period->id]))->assertOk()->viewData('summary');
        }
        $this->assertSame($expected, $summaries[0]['income']);
        $this->assertSame($summaries[0], $summaries[1]);
        $this->assertSame($summaries[0], $summaries[2]);
    }

    public function test_transactions_real_search_filters_pagination_empty_and_no_mock_writes(): void
    {
        $this->cash('pemasukan', '25.50', ['keterangan' => 'Pemasukan khusus 0 persen % <script>alert(1)</script>']);
        $deleted = $this->cash('pemasukan', '100.00', ['keterangan' => 'Arsip tersembunyi']);
        $deleted->delete();
        for ($i = 0; $i < 11; $i++) {
            $this->cash('pengeluaran', '1.00');
        }
        $params = ['periode_id' => $this->period->id];
        $response = $this->get(route('admin.transaksi', $params))->assertOk()->assertDontSee('Arsip tersembunyi')->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame(12, $response->viewData('transactions')->total());
        $this->assertCount(10, $response->viewData('transactions'));
        $filtered = $this->get(route('admin.transaksi', $params + ['jenis' => 'pemasukan', 'q' => '%']))->assertOk()->assertSee('persen %');
        $this->assertSame(1, $filtered->viewData('transactions')->total());
        $this->assertSame($response->viewData('summary'), $filtered->viewData('summary'));
        $this->assertCount(2, $this->get(route('admin.transaksi', $params + ['page' => 2]))->assertOk()->viewData('transactions'));
        $this->get(route('admin.transaksi', $params + ['q' => 'Tidak ditemukan']))->assertOk()->assertSee('Tidak ada transaksi kas sah yang sesuai filter.');
        $this->get(route('admin.transaksi', ['q' => str_repeat('a', 101)]))->assertRedirect(route('admin.transaksi'))->assertSessionHasErrors('q');
        $token = Str::random(40);
        $this->withSession(['_token' => $token])->post(route('admin.transaksi'), ['_token' => $token])->assertRedirect(route('admin.transaksi'))->assertSessionHasErrors(['jenis', 'kategori_transaksi_id', 'periode_id', 'tanggal', 'nominal', 'keterangan']);
        $this->assertSame(12, $this->get(route('admin.transaksi', $params))->assertOk()->viewData('transactions')->total());
    }
}
