<?php

namespace Tests\Feature;

use App\Models\JenisIuran;
use App\Models\KategoriTransaksi;
use App\Models\Periode;
use App\Models\Role;
use App\Models\TransaksiKas;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

class EnforceCsrfForApiTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class KasApiTest extends TestCase
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
        $this->live = getenv('KASORG_API_MARIADB') === '1';
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
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForApiTest::class);
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

    #[DataProvider('apiRoutes')]
    public function test_guest_is_rejected_with_json_even_without_accept_header(string $route): void
    {
        Auth::logout();
        $this->get(route($route))->assertUnauthorized()->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['success' => false, 'message' => 'Silakan login terlebih dahulu.', 'data' => null, 'errors' => null]);
        $this->withHeader('Accept', 'text/html')->get(route($route))->assertUnauthorized()->assertJsonPath('success', false);
    }

    public static function apiRoutes(): array
    {
        return [['api.kategori-transaksi'], ['api.transparansi.ringkasan']];
    }

    #[DataProvider('apiRoutes')]
    public function test_inactive_accounts_and_unknown_roles_are_forbidden(string $route): void
    {
        $this->member->update(['status_akun' => 'nonaktif']);
        $this->actingAs($this->member)->getJson(route($route))->assertForbidden()->assertJsonPath('success', false)->assertJsonPath('data', null);
        $this->assertGuest();
        $badRole = Role::create(['kode' => 'uji_role_'.Str::random(6), 'nama' => 'Role uji terisolasi']);
        $this->other->update(['role_id' => $badRole->id]);
        $this->actingAs($this->other)->getJson(route($route))->assertForbidden()->assertJsonPath('success', false);
    }

    public function test_categories_only_expose_id_name_type_and_support_filter(): void
    {
        $income = KategoriTransaksi::create(['nama' => 'Kategori API '.Str::uuid(), 'jenis' => 'pemasukan', 'is_sistem' => false]);
        $expense = KategoriTransaksi::create(['nama' => 'Kategori API '.Str::uuid(), 'jenis' => 'pengeluaran', 'is_sistem' => false]);
        foreach ([$this->admin, $this->member] as $user) {
            $response = $this->actingAs($user)->getJson(route('api.kategori-transaksi'))->assertOk()->assertJsonPath('success', true);
            $data = $response->json('data');
            $this->assertContains($income->id, array_column($data, 'id'));
            $this->assertContains($expense->id, array_column($data, 'id'));
            foreach ($data as $category) {
                $this->assertSame(['id', 'nama', 'jenis'], array_keys($category));
            }
            $filtered = $this->getJson(route('api.kategori-transaksi', ['jenis' => 'pengeluaran']))->assertOk()->json('data');
            $this->assertNotContains($income->id, array_column($filtered, 'id'));
            $this->assertContains($expense->id, array_column($filtered, 'id'));
            $this->assertSame(['pengeluaran'], array_values(array_unique(array_column($filtered, 'jenis'))));
        }
    }

    public function test_summary_matches_dashboard_transparency_and_decimal_formula_without_pii_or_writes(): void
    {
        $this->period->update(['saldo_awal' => '100.10']);
        foreach ([['pemasukan', '20.20'], ['pemasukan', '0.10'], ['pengeluaran', '5.05']] as [$type, $amount]) {
            $category = KategoriTransaksi::firstOrCreate(['nama' => 'API '.$type, 'jenis' => $type], ['is_sistem' => false]);
            TransaksiKas::create(['tanggal' => $this->period->tanggal_mulai, 'jenis' => $type, 'kategori_transaksi_id' => $category->id, 'nominal' => $amount, 'sumber' => 'manual', 'dibuat_oleh' => $this->admin->id, 'keterangan' => 'Privat '.$this->other->email.' '.$this->other->nim]);
        }
        $before = $this->fingerprint();
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        foreach ([$this->admin, $this->member] as $user) {
            $this->actingAs($user);
            $api = $this->getJson(route('api.transparansi.ringkasan', ['periode_id' => $this->period->id]))->assertOk()
                ->assertJsonPath('data.saldo_awal', '100.10')->assertJsonPath('data.pemasukan', '20.30')
                ->assertJsonPath('data.pengeluaran', '5.05')->assertJsonPath('data.saldo_akhir', '115.35')
                ->assertJsonPath('data.jumlah_transaksi', 3)->assertJsonPath('data.mata_uang', 'IDR');
            $api->assertDontSee($this->other->email)->assertDontSee($this->other->nim)->assertDontSee('password')
                ->assertDontSee('bukti_path')->assertDontSee('keterangan')->assertDontSee('diajukan_oleh');
            $role = $user->role->kode;
            $dashboard = $this->get(route($role.'.dashboard', ['periode_id' => $this->period->id]))->assertOk()->viewData('summary');
            $transparency = $this->get(route($role.'.transparansi', ['periode_id' => $this->period->id]))->assertOk()->viewData('summary');
            $this->assertSame($dashboard, $transparency);
            $this->assertSame($dashboard['closing'], $api->json('data.saldo_akhir'));
            $this->assertSame($dashboard['income'], $api->json('data.pemasukan'));
            $this->assertSame($dashboard['expense'], $api->json('data.pengeluaran'));
            $this->assertSame($dashboard['bars'][0]['income'], $api->json('data.grafik_bulanan.0.pemasukan'));
        }
        $this->assertTrue($before === $this->fingerprint());
        $this->assertFalse(collect($queries)->contains(fn ($sql) => preg_match('/^\s*(insert|update|delete|alter|drop|create)\b/i', $sql)));
    }

    #[DataProvider('invalidQuery')]
    public function test_invalid_query_returns_indonesian_json_422(string $route, array $query, string $field): void
    {
        $response = $this->get(route($route, $query))->assertUnprocessable()->assertJsonPath('success', false)->assertJsonPath('message', 'Parameter tidak valid.')->assertJsonPath('data', null);
        $this->assertArrayHasKey($field, $response->json('errors'));
    }

    public static function invalidQuery(): array
    {
        return [
            ['api.kategori-transaksi', ['jenis' => 'invalid'], 'jenis'],
            ['api.kategori-transaksi', ['jenis' => ['pemasukan']], 'jenis'],
            ['api.transparansi.ringkasan', ['periode_id' => -1], 'periode_id'],
            ['api.transparansi.ringkasan', ['periode_id' => 'invalid'], 'periode_id'],
            ['api.transparansi.ringkasan', ['periode_id' => ['semua']], 'periode_id'],
            ['api.transparansi.ringkasan', ['periode_id' => '99999999'], 'periode_id'],
        ];
    }

    public function test_wrong_methods_missing_routes_and_server_errors_do_not_leak_debug_details(): void
    {
        $this->postJson(route('api.kategori-transaksi'))->assertStatus(405)->assertJsonPath('success', false)->assertHeader('Allow', 'GET, HEAD');
        $this->get('/api/tidak-ada')->assertNotFound()->assertJsonPath('message', 'Endpoint tidak ditemukan.');
        Route::get('/api/test-server-error', fn () => throw new \RuntimeException('RAHASIA_UJI_INTERNAL'));
        $this->get('/api/test-server-error')->assertStatus(500)->assertJsonPath('message', 'Terjadi kesalahan pada server.')->assertDontSee('RAHASIA_UJI_INTERNAL')->assertDontSee('trace');
    }

    public function test_real_login_csrf_session_and_logout_protect_api(): void
    {
        Auth::logout();
        $this->post(route('login.store'), ['email' => $this->member->email, 'password' => $this->password])->assertStatus(419);
        $this->get(route('login'))->assertOk();
        $token = session()->token();
        $oldSession = session()->getId();
        $this->post(route('login.store'), ['email' => $this->member->email, 'password' => $this->password, '_token' => $token])->assertRedirect(route('anggota.dashboard'));
        $this->assertNotSame($oldSession, session()->getId());
        $this->get(route('api.kategori-transaksi'))->assertOk();
        $this->post(route('logout'), ['_token' => session()->token()])->assertRedirect(route('login'));
        $this->get(route('api.kategori-transaksi'))->assertUnauthorized()->assertJsonPath('success', false);
    }

    public function test_period_filter_empty_zero_and_all_periods(): void
    {
        $this->getJson(route('api.transparansi.ringkasan', ['periode_id' => $this->period->id]))->assertOk()->assertJsonPath('data.saldo_akhir', '0.00')->assertJsonPath('data.jumlah_transaksi', 0)->assertJsonPath('data.alokasi_pengeluaran', []);
        $this->getJson(route('api.transparansi.ringkasan', ['periode_id' => 'semua']))->assertOk()->assertJsonPath('data.periode.id', 'semua');
        if (! $this->live) {
            $this->period->delete();
            $this->getJson(route('api.transparansi.ringkasan'))->assertOk()->assertJsonPath('data.periode.id', null)->assertJsonPath('data.saldo_awal', '0.00')->assertJsonPath('data.grafik_bulanan', []);
        }
    }
}
