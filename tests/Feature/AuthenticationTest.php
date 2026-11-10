<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ChromiumPasswordCheck;
use Tests\Support\FinanceSchema;
use Tests\TestCase;

// Laravel biasanya melewati CSRF dalam PHPUnit. Di suite ini justru DIPAKSA aktif.
class EnforceCsrfForAuthenticationTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class AuthenticationTest extends TestCase
{
    private string $password;

    private int $userSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Bukan migration: fixture minimal hanya di SQLite RAM milik proses test.
        // Guard ini wajib lolos SEBELUM operasi schema atau insert fixture apa pun.
        if (! $this->app->environment('testing')
            || DB::getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Pengujian auth hanya boleh memakai SQLite :memory: pada environment testing.');
        }

        config(['cache.default' => 'array', 'session.driver' => 'array']);
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForAuthenticationTest::class);
        $this->password = Str::random(40);

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('nim')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('status_akun')->default(User::STATUS_AKTIF);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        FinanceSchema::create();
    }

    public function test_login_password_toggle_in_chrome_with_mouse_keyboard_and_real_lucide(): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan KASORG_BROWSER_EXECUTABLE untuk menjalankan interaksi Chrome.');
        }
        $html = $this->get(route('login'))->assertOk()->getContent();
        $results = ChromiumPasswordCheck::runInteractive($html, $browser, 'login');
        $this->assertCount(19, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['passed'], $result['name']);
        }
    }

    public function test_login_form_has_real_action_csrf_and_original_design_classes(): void
    {
        $response = $this->get(route('login'))->assertOk()
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('css/kasorg-login.css', false)
            ->assertSee('Plus+Jakarta+Sans', false)
            ->assertDontSee('cdn.tailwindcss.com', false)
            ->assertDontSee('onsubmit="return false"', false)
            ->assertDontSee('bendahara@organisasi.id');

        // Bootstrap + CSS KasOrg mempertahankan komposisi, bukan utility Tailwind lama.
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//body[contains(concat(" ", normalize-space(@class), " "), " kasorg-bootstrap ")]')->length);
        $this->assertSame(1, $xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " ko-login-card ") and contains(concat(" ", normalize-space(@class), " "), " ko-shadow-login ")]')->length);
        $this->assertSame(2, $xpath->query('//main/section[contains(concat(" ", normalize-space(@class), " "), " col-md-6 ")]')->length);
        $this->assertSame(1, $xpath->query('//form[@method="POST"]//input[@name="email" and @type="email" and contains(concat(" ", normalize-space(@class), " "), " form-control ")]')->length);
        $this->assertSame(1, $xpath->query('//form[@method="POST"]//input[@name="password" and @type="password" and contains(concat(" ", normalize-space(@class), " "), " form-control ")]')->length);
    }

    #[DataProvider('roles')]
    public function test_valid_login_uses_hash_regenerates_session_and_redirects_by_role(string $role): void
    {
        $user = $this->makeUser($role);
        $before = $user->fresh()->getRawOriginal();
        $this->get(route('login'))->assertOk();
        $oldSession = session()->getId();
        $oldToken = session()->token();

        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertRedirect(route($role.'.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($oldSession !== session()->getId());
        $this->assertTrue($oldToken !== session()->token());
        $this->assertTrue(Hash::check($this->password, $user->fresh()->password));
        $this->assertTrue($before === $user->fresh()->getRawOriginal(), 'Login tidak boleh mengubah data akun.');
        $this->assertSame(1, User::count());

        $this->get(route($role.'.dashboard'))->assertOk()
            ->assertViewIs($role.'.dashboard')->assertViewHas('summary')
            ->assertSee($user->name)
            ->assertSee('Autentikasi aktif.')
            ->assertSee('Dashboard dan modul keuangan masih DALAM PENGEMBANGAN/TESTING.')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertDontSee('/ui-preview/');
        $this->get(route('login'))->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertRedirect(route($role.'.dashboard'));
    }

    public static function roles(): array
    {
        return [['admin'], ['anggota']];
    }

    public function test_incorrect_password_is_rejected_and_never_flashed(): void
    {
        $user = $this->makeUser();
        $this->from(route('login'))->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => Str::random(45),
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertFalse(session()->hasOldInput('password'));
        $this->get(route('login'))->assertSee('Email atau kata sandi tidak sesuai, atau akun tidak aktif.');
    }

    public function test_inactive_account_cannot_login_even_with_correct_password(): void
    {
        $user = $this->makeUser(status: User::STATUS_NONAKTIF);
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_soft_deleted_account_cannot_login(): void
    {
        $user = $this->makeUser();
        $user->delete(); // Hanya fixture SQLite RAM, bukan akun development.
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unknown_role_is_rejected_without_leaving_authenticated_session(): void
    {
        $user = $this->makeUser('tidak_dikenal');
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertForbidden();
        $this->assertGuest();
    }

    public function test_deactivating_an_account_ends_existing_access(): void
    {
        $user = $this->makeUser();
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertRedirect();
        $user->update(['status_akun' => User::STATUS_NONAKTIF]);
        Auth::forgetGuards();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_required_and_email_validation_messages_are_indonesian(): void
    {
        $this->csrfPost(route('login.store'))->assertSessionHasErrors([
            'email' => 'Alamat email wajib diisi.',
            'password' => 'Kata sandi wajib diisi.',
        ]);
        $this->csrfPost(route('login.store'), [
            'email' => 'bukan-email', 'password' => $this->password,
        ])->assertSessionHasErrors(['email' => 'Format alamat email tidak valid.']);
        $this->assertGuest();
    }

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        $user = $this->makeUser();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->csrfPost(route('login.store'), [
                'email' => $user->email, 'password' => Str::random(45),
            ])->assertSessionHasErrors('email');
        }
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan masuk.', session('errors')->first('email'));
        $this->assertGuest();
    }

    #[DataProvider('protectedPages')]
    public function test_guests_are_redirected_to_login(string $route, string $role): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[DataProvider('protectedPages')]
    public function test_pages_enforce_roles_and_keep_application_navigation(string $route, string $role): void
    {
        $user = $this->makeUser($role);
        $message = match (true) {
            $route === 'admin.anggota' => 'Data anggota terhubung ke database; hasil clone masih perlu pengujian.',
            in_array($route, ['admin.iuran', 'admin.verifikasi', 'anggota.tagihan']) => 'Jenis iuran, tagihan, pembayaran dan verifikasi masih DALAM PENGEMBANGAN/TESTING.',
            default => 'Dashboard dan modul keuangan masih DALAM PENGEMBANGAN/TESTING.',
        };
        $this->actingAs($user)->get(route($route))->assertOk()
            ->assertSee($user->name)->assertSee($message)
            ->assertSee('Autentikasi aktif.')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertDontSee('/ui-preview/');

        $otherRole = $role === Role::ADMIN ? Role::ANGGOTA : Role::ADMIN;
        $this->actingAs($this->makeUser($otherRole))->get(route($route).'?role='.$role.'&view='.$role)
            ->assertForbidden()->assertSee('Akses ditolak');
    }

    public static function protectedPages(): array
    {
        return [
            ['admin.dashboard', 'admin'], ['admin.anggota', 'admin'],
            ['admin.iuran', 'admin'], ['admin.verifikasi', 'admin'],
            ['admin.transaksi', 'admin'], ['admin.laporan', 'admin'],
            ['admin.transparansi', 'admin'], ['anggota.dashboard', 'anggota'],
            ['anggota.tagihan', 'anggota'], ['anggota.laporan', 'anggota'],
            ['anggota.transparansi', 'anggota'],
        ];
    }

    public function test_logout_clears_session_rotates_csrf_and_denies_subsequent_access(): void
    {
        $user = $this->makeUser();
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertRedirect();
        session()->put('private_marker', 'harus-dihapus');
        $oldSession = session()->getId();
        $oldToken = session()->token();

        $this->csrfPost(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertTrue($oldSession !== session()->getId());
        $this->assertTrue($oldToken !== session()->token());
        $this->assertFalse(session()->has('private_marker'));
        Auth::forgetGuards();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_without_csrf_is_rejected(): void
    {
        $user = $this->makeUser();
        $this->get(route('login'))->assertOk();
        $this->post(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertStatus(419);
        $this->assertGuest();
    }

    public function test_invalid_csrf_cannot_logout_an_authenticated_user(): void
    {
        $user = $this->makeUser();
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertRedirect();
        $this->post(route('logout'), ['_token' => 'tidak-cocok'])->assertStatus(419);
        $this->assertAuthenticatedAs($user);
        $this->get(route('logout'))->assertStatus(405);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirect_does_not_follow_cross_role_intended_url(): void
    {
        $user = $this->makeUser('anggota');
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->csrfPost(route('login.store'), [
            'email' => $user->email, 'password' => $this->password,
        ])->assertRedirect(route('anggota.dashboard'))->assertSessionMissing('url.intended');
    }

    public function test_preview_routes_are_absent_outside_local(): void
    {
        $this->assertFalse(Route::has('ui-preview.login'));
        $this->get('/ui-preview/login')->assertNotFound();
        $this->get('/ui-preview/admin/dashboard')->assertNotFound();
    }

    public function test_local_previews_use_only_fixtures_and_do_not_authenticate_or_query_database(): void
    {
        $this->app->instance('env', 'local');
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::getRoutes()->refreshNameLookups();

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });
        $this->get(route('ui-preview.login'))->assertOk()
            ->assertSee('formulir ini tidak melakukan autentikasi.')
            ->assertDontSee('action="'.route('login.store').'"', false);
        $previewViews = [
            'admin.dashboard' => 'admin.dashboard-preview',
            'admin.anggota' => 'admin.anggota.preview',
            'admin.iuran' => 'admin.iuran.preview',
            'admin.verifikasi' => 'admin.pembayaran.preview',
            'admin.transaksi' => 'admin.transaksi.preview',
            'admin.laporan' => 'laporan.preview',
            'admin.transparansi' => 'transparansi.preview',
            'anggota.dashboard' => 'anggota.dashboard-preview',
            'anggota.tagihan' => 'anggota.tagihan.preview',
            'anggota.laporan' => 'laporan.preview',
            'anggota.transparansi' => 'transparansi.preview',
        ];
        foreach (self::protectedPages() as [$route]) {
            $response = $this->get(route('ui-preview.'.$route))->assertOk()
                ->assertViewIs($previewViews[$route])
                ->assertSee('Preview lokal tanpa autentikasi — bukan bukti pengujian aplikasi.')
                ->assertDontSee('action="'.route('logout').'"', false);
            $document = new \DOMDocument;
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            foreach ($xpath->query('//form[@action]') as $form) {
                $action = $form->getAttribute('action');
                $this->assertTrue($action === '' || str_starts_with($action, url('/ui-preview/')), 'Form preview tidak boleh mengirim aksi operasional: '.$action);
            }
        }
        $this->assertSame([], $queries, 'Preview tidak boleh membaca database.');
        $this->assertGuest();
        $this->csrfPost('/ui-preview/login')->assertStatus(405);
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        // Bahkan jika route cache dari local terbawa, runtime production menolak preview.
        $this->app->instance('env', 'production');
        $this->get(route('ui-preview.login'))->assertNotFound();
        foreach (self::protectedPages() as [$route]) {
            $this->get(route('ui-preview.'.$route))->assertNotFound();
        }
    }

    public function test_preview_does_not_expose_logged_in_identity_or_logout_action(): void
    {
        $user = $this->makeUser('anggota');
        $this->actingAs($user);
        $this->app->instance('env', 'local');
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::getRoutes()->refreshNameLookups();

        $this->get(route('ui-preview.admin.dashboard'))->assertOk()
            ->assertDontSee($user->name)
            ->assertDontSee('action="'.route('logout').'"', false);
        $this->get(route('ui-preview.login'))->assertOk()
            ->assertDontSee($user->email)
            ->assertDontSee('action="'.route('login.store').'"', false);
        $this->assertAuthenticatedAs($user);
    }

    private function makeUser(string $role = Role::ADMIN, string $status = User::STATUS_AKTIF): User
    {
        $roleModel = Role::firstOrCreate(['kode' => $role], ['nama' => ucfirst($role)]);
        $sequence = ++$this->userSequence;

        return User::create([
            'name' => 'Pengguna Uji '.ucfirst($role).' '.$sequence,
            'nim' => '9900000'.$sequence,
            'email' => 'uji-'.$sequence.'@kasorg.test',
            'password' => Hash::make($this->password),
            'role_id' => $roleModel->id,
            'status_akun' => $status,
        ]);
    }

    private function csrfPost(string $uri, array $data = []): TestResponse
    {
        $token = session()->token() ?? Str::random(40);

        return $this->withSession(['_token' => $token])->post($uri, [...$data, '_token' => $token]);
    }
}
