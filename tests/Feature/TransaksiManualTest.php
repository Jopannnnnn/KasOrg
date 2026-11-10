<?php

namespace Tests\Feature;

use App\Models\JenisIuran;
use App\Models\KategoriTransaksi;
use App\Models\Periode;
use App\Models\Role;
use App\Models\TransaksiKas;
use App\Models\User;
use App\Services\IuranService;
use App\Services\TransaksiManualService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ChromiumPasswordCheck;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

class EnforceCsrfForManualTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class TransaksiManualTest extends TestCase
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
        $this->live = getenv('KASORG_MANUAL_MARIADB') === '1';
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
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForManualTest::class);
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
        $this->travelTo($this->period->tanggal_mulai->copy()->addDays(14));
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
            $this->travelBack();
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

    private function payload(array $changes = []): array
    {
        $category = KategoriTransaksi::firstOrCreate(['nama' => 'Uji manual pemasukan', 'jenis' => 'pemasukan'], ['is_sistem' => false]);

        return array_replace(['jenis' => 'pemasukan', 'kategori_transaksi_id' => $category->id,
            'periode_id' => $this->period->id, 'tanggal' => $this->period->tanggal_mulai->toDateString(),
            'nominal' => '20.20', 'keterangan' => 'Donasi uji terisolasi'], $changes);
    }

    private function send(string $method, string $route, array $data = [])
    {
        $token = session()->token() ?: Str::random(40);

        return $this->withSession(['_token' => $token])->json($method, $route, $data + ['_token' => $token]);
    }

    private function store(array $changes = []): TransaksiKas
    {
        $before = TransaksiKas::count();
        $this->send('POST', route('admin.transaksi.store'), $this->payload($changes))->assertRedirect(route('admin.transaksi', ['periode_id' => $this->period->id]));
        $this->assertSame($before + 1, TransaksiKas::count());

        return TransaksiKas::latest('id')->firstOrFail();
    }

    private function assertBalance(string $income, string $expense, string $closing): void
    {
        $summaries = [];
        foreach (['admin.dashboard', 'admin.transaksi', 'admin.transparansi', 'anggota.dashboard', 'anggota.transparansi'] as $route) {
            $this->actingAs(str_starts_with($route, 'anggota.') ? $this->member : $this->admin);
            $response = $this->get(route($route, ['periode_id' => $this->period->id]))->assertOk();
            $summary = $response->viewData('summary');
            $this->assertSame($income, $summary['income']);
            $this->assertSame($expense, $summary['expense']);
            $this->assertSame($closing, $summary['closing']);
            $summaries[] = $summary;
        }
        foreach ($summaries as $summary) {
            $this->assertSame($summaries[0], $summary);
        }
        $api = $this->getJson(route('api.transparansi.ringkasan', ['periode_id' => $this->period->id]))->assertOk()
            ->assertJsonPath('data.pemasukan', $income)->assertJsonPath('data.pengeluaran', $expense)->assertJsonPath('data.saldo_akhir', $closing);
        $this->assertSame($summaries[0]['count'], $api->json('data.jumlah_transaksi'));
        $this->actingAs($this->admin);
    }

    public function test_crud_income_expense_audit_soft_delete_and_all_financial_surfaces_stay_consistent(): void
    {
        $this->period->update(['saldo_awal' => '100.10']);
        $this->assertBalance('0.00', '0.00', '100.10');
        $income = $this->store();
        $this->assertSame('manual', $income->sumber);
        $this->assertNull($income->tagihan_iuran_id);
        $this->assertNull($income->pembayaran_iuran_id);
        $this->assertSame($this->admin->id, $income->dibuat_oleh);
        $this->assertNull($income->diubah_oleh);
        $this->assertSame($this->period->tanggal_mulai->toDateString(), $income->tanggal->toDateString());
        $this->assertBalance('20.20', '0.00', '120.30');
        $expenseCategory = KategoriTransaksi::create(['nama' => 'Konsumsi uji '.Str::random(6), 'jenis' => 'pengeluaran']);
        $expense = $this->store(['jenis' => 'pengeluaran', 'kategori_transaksi_id' => $expenseCategory->id, 'nominal' => '5.05']);
        $this->assertBalance('20.20', '5.05', '115.25');
        $updatedDate = $this->period->tanggal_mulai->copy()->addDay()->toDateString();
        $this->send('PUT', route('admin.transaksi.update', $income), $this->payload(['nominal' => '20.30', 'tanggal' => $updatedDate, 'keterangan' => 'Donasi diperbarui']))
            ->assertRedirect();
        $income->refresh();
        $this->assertSame('20.30', $income->nominal);
        $this->assertSame($this->admin->id, $income->diubah_oleh);
        $this->assertSame($this->admin->id, $income->dibuat_oleh);
        $this->assertSame($updatedDate, $income->tanggal->toDateString());
        $this->assertBalance('20.30', '5.05', '115.35');
        $this->send('DELETE', route('admin.transaksi.destroy', $expense), ['konfirmasi' => '1'])->assertRedirect();
        $this->assertSoftDeleted('transaksi_kas', ['id' => $expense->id, 'diubah_oleh' => $this->admin->id]);
        $this->assertBalance('20.30', '0.00', '120.40');
        $this->send('DELETE', route('admin.transaksi.destroy', $expense), ['konfirmasi' => '1'])->assertNotFound();
        $this->send('PUT', route('admin.transaksi.update', $expense), $this->payload())->assertNotFound();
    }

    #[DataProvider('badAmounts')]
    public function test_invalid_amounts_are_rejected_without_writes(mixed $amount): void
    {
        $payload = $this->payload(['nominal' => $amount]);
        $before = $this->fingerprint();
        $this->send('POST', route('admin.transaksi.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('nominal');
        $this->assertSame($before, $this->fingerprint());
    }

    public static function badAmounts(): array
    {
        return array_map(fn ($value) => [$value], ['0', '0.00', '-1', '1.001', '1e3', '10000000000000.00', 'NaN', '1,50', null, [], 1.25, str_repeat('9', 100)]);
    }

    public function test_decimal_limit_and_cents_remain_exact(): void
    {
        $this->store(['nominal' => '0.10']);
        $this->store(['nominal' => '0.20']);
        $this->assertBalance('0.30', '0.00', '0.30');
        if ($this->live) {
            // SQLite NUMERIC tidak menjamin DECIMAL(15,2) pada batas maksimum.
            $this->store(['nominal' => '9999999999999.99']);
            $this->store(['nominal' => '0.01']);
            $this->assertBalance('10000000000000.30', '0.00', '10000000000000.30');
            $this->travelTo($this->period->tanggal_selesai->copy());
            $this->store(['nominal' => '0.01', 'tanggal' => $this->period->tanggal_selesai->toDateString()]);
            $this->assertBalance('10000000000000.31', '0.00', '10000000000000.31');
        }
    }

    public function test_server_validation_rejects_category_type_date_description_and_reserved_fields(): void
    {
        $expense = KategoriTransaksi::create(['nama' => 'Kategori salah '.Str::random(6), 'jenis' => 'pengeluaran']);
        $cases = [
            ['kategori_transaksi_id' => $expense->id], ['kategori_transaksi_id' => 99999999], ['jenis' => 'tidak_sah'],
            ['tanggal' => '2030-02-30'], ['tanggal' => today()->addDay()->toDateString()], ['tanggal' => '2020-01-01'],
            ['periode_id' => 99999999], ['keterangan' => ''], ['keterangan' => str_repeat('a', 256)],
            ['sumber' => 'pembayaran_iuran'], ['pembayaran_iuran_id' => 1], ['tagihan_iuran_id' => 1],
            ['dibuat_oleh' => $this->other->id], ['diubah_oleh' => $this->other->id], ['deleted_at' => now()->toDateTimeString()],
        ];
        $payload = $this->payload();
        foreach ($cases as $change) {
            $before = $this->fingerprint();
            $this->send('POST', route('admin.transaksi.store'), array_replace($payload, $change))->assertUnprocessable()->assertJsonValidationErrors(array_keys($change)[0]);
            $this->assertSame($before, $this->fingerprint());
        }
        $row = $this->store();
        $before = $this->fingerprint();
        $this->send('PUT', route('admin.transaksi.update', $row), $this->payload(['kategori_transaksi_id' => $expense->id]))->assertUnprocessable();
        $this->assertSame($before, $this->fingerprint());
    }

    public function test_closed_original_period_cannot_be_edited_deleted_or_moved_to_open_period(): void
    {
        $row = $this->store();
        $this->period->update(['status' => 'ditutup', 'saldo_akhir_snapshot' => '20.20']);
        $new = Periode::create(['tahun' => $this->period->tahun, 'bulan' => 2, 'tanggal_mulai' => $this->period->tahun.'-02-01', 'tanggal_selesai' => $this->period->tahun.'-02-28', 'status' => 'terbuka']);
        $this->travelTo($new->tanggal_selesai->copy());
        $before = $this->fingerprint();
        $this->send('POST', route('admin.transaksi.store'), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $this->send('PUT', route('admin.transaksi.update', $row), $this->payload(['periode_id' => $new->id, 'tanggal' => $new->tanggal_mulai->toDateString()]))->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1'])->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $this->assertSame($before, $this->fingerprint());
        $this->get(route('admin.transaksi', ['periode_id' => $this->period->id]))->assertOk()->assertSee('Periode terkunci')->assertDontSee('data-open-modal="modal-transaksi-edit-'.$row->id.'"', false);
        $this->assertBalance('20.20', '0.00', '20.20');
    }

    public function test_open_period_move_and_type_change_are_allowed_but_destination_closed_is_not(): void
    {
        $row = $this->store();
        $new = Periode::create(['tahun' => $this->period->tahun, 'bulan' => 2, 'tanggal_mulai' => $this->period->tahun.'-02-01', 'tanggal_selesai' => $this->period->tahun.'-02-28', 'status' => 'terbuka']);
        $this->travelTo($new->tanggal_selesai->copy());
        $category = KategoriTransaksi::create(['nama' => 'Pengeluaran pindah '.Str::random(6), 'jenis' => 'pengeluaran']);
        $data = $this->payload(['periode_id' => $new->id, 'tanggal' => $new->tanggal_mulai->toDateString(), 'jenis' => 'pengeluaran', 'kategori_transaksi_id' => $category->id]);
        $new->update(['status' => 'ditutup']);
        $this->send('PUT', route('admin.transaksi.update', $row), $data)->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $new->update(['status' => 'terbuka']);
        $this->send('PUT', route('admin.transaksi.update', $row), $data)->assertRedirect(route('admin.transaksi', ['periode_id' => $new->id]));
        $this->assertBalance('0.00', '0.00', '0.00');
        $this->getJson(route('api.transparansi.ringkasan', ['periode_id' => $new->id]))->assertOk()->assertJsonPath('data.pengeluaran', '20.20')->assertJsonPath('data.saldo_akhir', '-20.20');
    }

    public function test_overlapping_periods_and_orphan_original_date_are_rejected(): void
    {
        $row = $this->store();
        $overlap = Periode::create(['tahun' => $this->period->tahun, 'bulan' => 3,
            'tanggal_mulai' => $this->period->tanggal_mulai->toDateString(), 'tanggal_selesai' => $this->period->tanggal_mulai->copy()->addDay()->toDateString(), 'status' => 'terbuka']);
        $before = $this->fingerprint();
        $this->send('POST', route('admin.transaksi.store'), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $this->send('PUT', route('admin.transaksi.update', $row), $this->payload())->assertUnprocessable();
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1'])->assertUnprocessable();
        $this->assertSame($before, $this->fingerprint());
        $overlap->delete();
        $row->update(['tanggal' => '2020-01-01']);
        $before = $this->fingerprint();
        $this->send('PUT', route('admin.transaksi.update', $row), $this->payload())->assertUnprocessable();
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1'])->assertUnprocessable();
        $this->assertSame($before, $this->fingerprint());
    }

    public function test_automatic_verified_income_and_linked_rows_are_protected_and_never_duplicated(): void
    {
        $service = app(IuranService::class);
        $bill = $service->createBill($this->admin, ['user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id]);
        $payment = $service->submit($this->member, $bill, ['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()], null);
        $service->verify($this->admin, $payment, ['keputusan' => 'disetujui']);
        $row = TransaksiKas::where('pembayaran_iuran_id', $payment->id)->sole();
        $payload = $this->payload();
        $before = $this->fingerprint();
        $this->send('PUT', route('admin.transaksi.update', $row), $payload)->assertForbidden();
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1'])->assertForbidden();
        $this->assertSame($before, $this->fingerprint());
        $this->assertSame(1, TransaksiKas::where('pembayaran_iuran_id', $payment->id)->count());
        $this->assertBalance('75000.25', '0.00', '75000.25');
        $this->get(route('admin.transaksi', ['periode_id' => $this->period->id]))->assertOk()->assertSee('Otomatis')->assertDontSee('data-open-modal="modal-transaksi-edit-'.$row->id.'"', false);
        $protected = json_encode([$bill->fresh()->getRawOriginal(), $payment->fresh()->getRawOriginal(), $row->fresh()->getRawOriginal()]);
        $manual = $this->store(['nominal' => '0.10']);
        $this->send('PUT', route('admin.transaksi.update', $manual), $this->payload(['nominal' => '0.20']))->assertRedirect();
        $this->send('DELETE', route('admin.transaksi.destroy', $manual), ['konfirmasi' => '1'])->assertRedirect();
        $this->assertBalance('75000.25', '0.00', '75000.25');
        $this->assertSame($protected, json_encode([$bill->fresh()->getRawOriginal(), $payment->fresh()->getRawOriginal(), $row->fresh()->getRawOriginal()]));
        // Melindungi record tidak konsisten sekalipun sumbernya diubah menjadi manual.
        $row->update(['sumber' => 'manual']);
        $before = $this->fingerprint();
        $this->send('PUT', route('admin.transaksi.update', $row), $payload)->assertForbidden();
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1'])->assertForbidden();
        $this->assertSame($before, $this->fingerprint());
    }

    public function test_service_rechecks_locked_record_instead_of_trusting_stale_binding(): void
    {
        $row = $this->store();
        $stale = $row->replicate();
        $stale->id = $row->id;
        $row->update(['sumber' => 'pembayaran_iuran']);
        $this->expectException(AuthorizationException::class);
        app(TransaksiManualService::class)->save($this->admin, $this->payload(), $stale);
    }

    public function test_members_guests_inactive_accounts_and_csrf_are_rejected(): void
    {
        $row = $this->store();
        $payload = $this->payload();
        $routes = [['POST', route('admin.transaksi.store'), $payload], ['PUT', route('admin.transaksi.update', $row), $payload], ['DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1']]];
        $before = $this->fingerprint();
        $this->actingAs($this->member);
        foreach ($routes as [$method, $url, $data]) {
            $this->send($method, $url, $data)->assertForbidden();
        }
        Auth::logout();
        foreach ($routes as [$method, $url, $data]) {
            $this->withSession(['_token' => 'guest-token'])->call($method, $url, $data + ['_token' => 'guest-token'])->assertRedirect(route('login'));
        }
        $this->actingAs($this->admin);
        $this->post(route('admin.transaksi.store'), $payload)->assertStatus(419);
        $this->assertSame($before, $this->fingerprint());
        $this->admin->update(['status_akun' => 'nonaktif']);
        $this->send('POST', route('admin.transaksi.store'), $payload)->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_delete_needs_confirmation_and_never_hard_deletes(): void
    {
        $row = $this->store();
        $before = $this->fingerprint();
        $this->send('DELETE', route('admin.transaksi.destroy', $row))->assertUnprocessable()->assertJsonValidationErrors('konfirmasi');
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '0'])->assertUnprocessable();
        $this->assertSame($before, $this->fingerprint());
        $this->send('DELETE', route('admin.transaksi.destroy', $row), ['konfirmasi' => '1'])->assertRedirect();
        $this->assertTrue(TransaksiKas::withTrashed()->whereKey($row->id)->exists());
    }

    public function test_delete_failure_rolls_back_audit_update_and_preserves_all_records(): void
    {
        $row = $this->store();
        $before = $this->fingerprint();
        $dispatcher = TransaksiKas::getEventDispatcher();
        $isolated = clone $dispatcher;
        $isolated->listen('eloquent.deleting: '.TransaksiKas::class, fn () => throw new \RuntimeException('Simulasi gagal hapus setelah audit'));
        TransaksiKas::setEventDispatcher($isolated);
        try {
            app(TransaksiManualService::class)->delete($this->admin, $row);
            $this->fail('Operasi yang gagal tidak boleh commit sebagian.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulasi gagal hapus setelah audit', $exception->getMessage());
        } finally {
            TransaksiKas::setEventDispatcher($dispatcher);
        }
        $this->assertSame($before, $this->fingerprint());
        $this->assertNull($row->fresh()->diubah_oleh);
        $this->assertNull($row->fresh()->deleted_at);
    }

    public function test_manual_modals_category_filters_keyboard_and_mobile_in_chrome(): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan executable Chrome untuk pengujian modal manual desktop/mobile.');
        }
        $this->store();
        $category = KategoriTransaksi::create(['nama' => 'Pengeluaran browser '.Str::random(6), 'jenis' => 'pengeluaran']);
        $this->store(['jenis' => 'pengeluaran', 'kategori_transaksi_id' => $category->id, 'nominal' => '5.05']);
        $service = app(IuranService::class);
        $bill = $service->createBill($this->admin, ['user_id' => $this->member->id, 'jenis_iuran_id' => $this->type->id, 'periode_id' => $this->period->id]);
        $payment = $service->submit($this->member, $bill, ['metode' => 'tunai', 'tanggal_bayar' => today()->toDateString()], null);
        $service->verify($this->admin, $payment, ['keputusan' => 'disetujui']);
        $html = $this->get(route('admin.transaksi', ['periode_id' => $this->period->id]))->assertOk()->getContent();
        $results = ChromiumPasswordCheck::runInteractive($html, $browser, 'manual');
        $this->assertCount(28, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['passed'], $result['name']);
        }
    }

    public function test_html_is_escaped_modals_restore_only_their_own_values_and_filters_paginate(): void
    {
        $row = $this->store(['keterangan' => 'Khusus persen % <img src=x onerror=alert(1)>']);
        for ($i = 0; $i < 11; $i++) {
            $this->store(['keterangan' => 'Transaksi uji '.$i]);
        }
        $params = ['periode_id' => $this->period->id];
        $response = $this->get(route('admin.transaksi', $params))->assertOk()->assertDontSee('<img src=x onerror=alert(1)>', false);
        $this->assertSame(12, $response->viewData('transactions')->total());
        $filtered = $this->get(route('admin.transaksi', $params + ['q' => '%', 'jenis' => 'pemasukan']))->assertOk();
        $this->assertSame(1, $filtered->viewData('transactions')->total());
        $this->assertCount(2, $this->get(route('admin.transaksi', $params + ['page' => 2]))->assertOk()->viewData('transactions'));
        $this->get(route('admin.transaksi', $params + ['jenis' => 'pengeluaran']))->assertOk()->assertSee('Tidak ada transaksi kas sah yang sesuai filter.');
        $url = route('admin.transaksi.update', ['transaksi' => $row->id] + $params + ['q' => '%']);
        $token = session()->token();
        $this->post($url, $this->payload(['_method' => 'PUT', '_token' => $token, '_modal' => 'modal-transaksi-edit-'.$row->id, 'nominal' => '-1']))->assertRedirect(route('admin.transaksi', $params + ['q' => '%']))->assertSessionHasErrors('nominal');
        $html = $this->get(route('admin.transaksi', $params + ['q' => '%']))->assertOk()->getContent();
        $this->assertStringContainsString('value="-1"', $html);
        $this->assertSame(1, substr_count($html, 'value="-1"'));
        $this->assertStringContainsString('data-manual-transaction-form', $html);
    }
}
