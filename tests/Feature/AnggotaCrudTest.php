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
use Tests\TestCase;

class EnforceCsrfForAnggotaTest extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class AnggotaCrudTest extends TestCase
{
    private User $admin;

    private string $password;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();
        if (! app()->environment('testing') || DB::getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('CRUD test hanya boleh memakai SQLite :memory: pada environment testing.');
        }
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        $this->app->bind(ValidateCsrfToken::class, EnforceCsrfForAnggotaTest::class);
        $this->password = Str::random(28).'Aa1!';

        // Fixture RAM tanpa migration, seeder, drop atau reset database development.
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('nim', 20)->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('status_akun')->default('aktif');
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Role::create(['kode' => Role::ANGGOTA, 'nama' => 'Anggota Organisasi']);
        $this->admin = $this->makeUser(Role::ADMIN);
        $this->actingAs($this->admin);
    }

    public function test_index_reads_members_relation_and_real_statistics_without_writing(): void
    {
        $active = $this->makeUser();
        $inactive = $this->makeUser(attributes: ['status_akun' => 'nonaktif']);
        $deleted = $this->makeUser();
        $deleted->delete();
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $response = $this->get(route('admin.anggota'))->assertOk()
            ->assertSee($active->name)->assertSee($inactive->name)
            ->assertDontSee($deleted->email)->assertSee('Anggota Organisasi')
            ->assertSee('Data anggota terhubung ke database; hasil clone masih perlu pengujian.')
            ->assertSee('Belum terhubung')->assertDontSee('/ui-preview/');
        $this->assertSame(['total' => 2, 'aktif' => 1, 'nonaktif' => 1], $response->viewData('stats'));
        $this->assertCount(2, $response->viewData('members'));
        $this->assertFalse(collect($queries)->contains(fn ($sql) => preg_match('/^\s*(insert|update|delete|alter|drop|create)\b/i', $sql)));
    }

    public function test_admin_creates_persisted_member_with_hash_and_login_works(): void
    {
        $payload = $this->payload();
        $this->write('POST', route('admin.anggota.store'), $payload)
            ->assertRedirect(route('admin.anggota'))->assertSessionHasNoErrors();
        $member = User::where('email', $payload['email'])->firstOrFail();
        $this->assertSame('anggota', $member->role->kode);
        $this->assertSame($payload['nim'], $member->nim);
        $this->assertTrue(Hash::check($this->password, $member->password));
        $this->assertTrue($member->password !== $this->password);
        $this->assertFalse(session()->hasOldInput('password'));
        $this->get(route('admin.anggota'))->assertSee($payload['name'])->assertDontSee($this->password);

        Auth::logout();
        $this->write('POST', route('login.store'), ['email' => $member->email, 'password' => $this->password])
            ->assertRedirect(route('anggota.dashboard'));
        $this->assertAuthenticatedAs($member);
    }

    public function test_edit_persists_identity_status_but_preserves_id_role_and_hash(): void
    {
        $member = $this->makeUser(attributes: ['email_verified_at' => now()]);
        $before = $member->fresh();
        $values = ['name' => 'Nama Baru', 'nim' => '24999990', 'email' => 'baru@kasorg.test', 'status_akun' => 'nonaktif'];
        $this->write('PUT', route('admin.anggota.update', $member), $values)->assertSessionHasNoErrors()->assertRedirect();
        $member->refresh();
        foreach ($values as $field => $value) {
            $this->assertSame($value, $member->{$field});
        }
        $this->assertSame($before->id, $member->id);
        $this->assertSame($before->role_id, $member->role_id);
        $this->assertTrue($before->password === $member->password);
        $this->assertNull($member->email_verified_at);
        $this->get(route('admin.anggota', ['q' => 'Nama Baru']))->assertSee('Nama Baru');
        $this->get(route('admin.anggota', ['q' => $before->name]))->assertDontSee($member->email);
    }

    public function test_update_allows_unchanged_unique_identity(): void
    {
        $member = $this->makeUser();
        $this->write('PUT', route('admin.anggota.update', $member), $member->only(['name', 'nim', 'email', 'status_akun']))
            ->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_deactivate_and_reactivate_persist_and_nonactive_login_is_rejected(): void
    {
        $member = $this->makeUser();
        $before = $member->fresh()->getRawOriginal();
        $this->write('PATCH', route('admin.anggota.status', $member), ['status_akun' => 'nonaktif'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('nonaktif', $member->fresh()->status_akun);
        Auth::logout();
        $this->write('POST', route('login.store'), ['email' => $member->email, 'password' => $this->password])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($this->admin);
        $this->write('PATCH', route('admin.anggota.status', $member), ['status_akun' => 'aktif'])->assertRedirect();
        $this->assertSame('aktif', $member->fresh()->status_akun);
        $this->assertTrue($before['password'] === $member->fresh()->password);
        $this->assertSame($before['nim'], $member->fresh()->nim);
    }

    public function test_soft_delete_keeps_user_and_financial_reference_and_binding_excludes_archived_users(): void
    {
        $member = $this->makeUser();
        Schema::create('tagihan_iuran', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
        });
        DB::table('tagihan_iuran')->insert(['user_id' => $member->id]);

        $this->write('DELETE', route('admin.anggota.destroy', $member))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull(User::find($member->id));
        $this->assertTrue(User::withTrashed()->findOrFail($member->id)->trashed());
        $this->assertSame('nonaktif', User::withTrashed()->findOrFail($member->id)->status_akun);
        $this->assertSame(1, DB::table('tagihan_iuran')->where('user_id', $member->id)->count());
        $this->get(route('admin.anggota'))->assertDontSee($member->email);
        foreach (['PUT' => 'admin.anggota.update', 'PATCH' => 'admin.anggota.status', 'DELETE' => 'admin.anggota.destroy'] as $method => $route) {
            $this->write($method, route($route, $member))->assertNotFound();
        }
    }

    public function test_search_handles_name_nim_email_new_values_and_literal_wildcards(): void
    {
        $member = $this->makeUser(attributes: ['name' => 'Nama 100%_Unik']);
        $other = $this->makeUser(attributes: ['name' => 'Nama Biasa']);
        foreach ([$member->name, $member->nim, $member->email, '0', '%_'] as $search) {
            $response = $this->get(route('admin.anggota', ['q' => $search]))->assertOk();
            $this->assertTrue($response->viewData('members')->contains('id', $member->id));
            if ($search !== '0') {
                $this->assertFalse($response->viewData('members')->contains('id', $other->id));
            }
        }
        $response = $this->get(route('admin.anggota', ['q' => "' OR 1=1 --"]))->assertOk();
        $this->assertSame(0, $response->viewData('members')->total());
    }

    public function test_server_side_filter_and_pagination_keep_query_parameters(): void
    {
        for ($index = 1; $index <= 12; $index++) {
            $this->makeUser(attributes: ['name' => sprintf('Anggota %02d', $index)]);
        }
        $this->makeUser(attributes: ['status_akun' => 'nonaktif']);
        $response = $this->get(route('admin.anggota', ['q' => 'Anggota', 'status' => 'aktif', 'per_page' => 5, 'page' => 2]))->assertOk();
        $page = $response->viewData('members');
        $this->assertSame(12, $page->total());
        $this->assertSame(2, $page->currentPage());
        $this->assertCount(5, $page);
        $this->assertStringContainsString('q=Anggota', $page->nextPageUrl());
        $this->assertStringContainsString('status=aktif', $page->nextPageUrl());
        $this->assertStringContainsString('per_page=5', $page->nextPageUrl());
        $inactive = $this->get(route('admin.anggota', ['status' => 'nonaktif']))->assertOk();
        $this->assertSame(1, $inactive->viewData('members')->total());
    }

    public function test_invalid_create_reopens_modal_with_indonesian_errors_and_no_password_flash(): void
    {
        $before = User::count();
        $this->write('POST', route('admin.anggota.store'), ['email' => 'tidak-valid', 'password' => $this->password])
            ->assertRedirect(route('admin.anggota'))->assertSessionHasErrors([
                'name' => 'Nama anggota wajib diisi.', 'nim' => 'NIM wajib diisi.',
                'email' => 'Format email tidak valid.', 'status_akun' => 'Status akun wajib dipilih.',
                'password' => 'Konfirmasi kata sandi tidak cocok.',
            ])->assertSessionHas('anggota_form.mode', 'create');
        $this->assertSame($before, User::count());
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(session()->hasOldInput('password_confirmation'));
        $this->get(route('admin.anggota'))->assertOk()->assertSee('Nama anggota wajib diisi.')
            ->assertSee('value="tidak-valid"', false)->assertDontSee($this->password);
    }

    public function test_invalid_edit_restores_correct_bound_member_and_previous_input(): void
    {
        $member = $this->makeUser();
        $this->write('PUT', route('admin.anggota.update', $member), [
            'name' => 'Nama Koreksi', 'nim' => $member->nim, 'email' => 'tidak-valid', 'status_akun' => 'aktif',
        ])->assertSessionHasErrors('email')->assertSessionHas('anggota_form.id', $member->id);
        $this->get(route('admin.anggota'))->assertOk()
            ->assertSee('action="'.route('admin.anggota.update', $member).'"', false)
            ->assertSee('value="Nama Koreksi"', false);
        $this->assertNotSame('Nama Koreksi', $member->fresh()->name);
    }

    public function test_duplicate_nim_email_and_archived_identities_are_rejected(): void
    {
        $member = $this->makeUser();
        $payload = $this->payload(['nim' => $member->nim, 'email' => $member->email]);
        $this->write('POST', route('admin.anggota.store'), $payload)->assertSessionHasErrors(['nim', 'email']);
        $other = $this->makeUser();
        $this->write('PUT', route('admin.anggota.update', $other), $payload)->assertSessionHasErrors(['nim', 'email']);
        $member->delete();
        $this->write('POST', route('admin.anggota.store'), $payload)->assertSessionHasErrors(['nim', 'email']);
    }

    public function test_role_escalation_and_editing_password_are_rejected(): void
    {
        foreach ([['role_id' => $this->admin->role_id], ['role' => 'admin']] as $forbidden) {
            $this->write('POST', route('admin.anggota.store'), $this->payload($forbidden))
                ->assertSessionHasErrors(array_keys($forbidden));
        }
        $member = $this->makeUser();
        $this->write('PUT', route('admin.anggota.update', $member), $this->payload())->assertSessionHasErrors('password');
    }

    public function test_account_status_nim_length_and_format_are_validated(): void
    {
        foreach ([['nim' => 'bukan-angka'], ['nim' => str_repeat('9', 21)], ['status_akun' => 'menunggu']] as $invalid) {
            $this->write('POST', route('admin.anggota.store'), $this->payload($invalid))
                ->assertSessionHasErrors(array_keys($invalid));
        }
        $member = $this->makeUser();
        $this->write('PATCH', route('admin.anggota.status', $member), ['status_akun' => 'disetujui'])
            ->assertSessionHasErrors(['status_akun' => 'Status akun harus aktif atau nonaktif.']);
        $this->assertSame('aktif', $member->fresh()->status_akun);
    }

    public function test_weak_missing_and_mismatched_passwords_are_rejected_in_indonesian(): void
    {
        foreach (['', 'abc', str_repeat('a', 12), str_repeat('A', 12).'1!', str_repeat('a', 12).'1!', str_repeat('a', 12).'A!', str_repeat('a', 12).'A1'] as $password) {
            $this->write('POST', route('admin.anggota.store'), $this->payload([
                'password' => $password, 'password_confirmation' => $password,
            ]))->assertSessionHasErrors('password');
            foreach (session('errors')->get('password') as $error) {
                $this->assertStringContainsString('Kata sandi', $error);
            }
        }
        $this->write('POST', route('admin.anggota.store'), $this->payload(['password_confirmation' => Str::random(32)]))
            ->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi tidak cocok.']);
        $this->assertSame(1, User::count());
    }

    public function test_password_over_bcrypt_byte_limit_is_rejected_without_hashing_error(): void
    {
        $long = str_repeat('あ', 25).'Aa1!';
        $this->write('POST', route('admin.anggota.store'), $this->payload(['password' => $long, 'password_confirmation' => $long]))
            ->assertSessionHasErrors('password');
        $this->assertSame(1, User::count());
    }

    #[DataProvider('protectedIdentities')]
    public function test_demo_identities_cannot_be_edited_deactivated_or_archived(array $identity): void
    {
        $member = $this->makeUser(attributes: $identity);
        $before = $member->fresh()->getRawOriginal();
        foreach (['PUT' => 'admin.anggota.update', 'PATCH' => 'admin.anggota.status', 'DELETE' => 'admin.anggota.destroy'] as $method => $route) {
            $this->write($method, route($route, $member), $this->payload(['status_akun' => 'nonaktif']))->assertForbidden();
            $this->assertTrue($before === $member->fresh()->getRawOriginal());
        }
        $this->get(route('admin.anggota', ['q' => $member->email]))->assertSee('Akun dilindungi')
            ->assertDontSee('action="'.route('admin.anggota.destroy', $member).'"', false);
    }

    public static function protectedIdentities(): array
    {
        return [[['email' => 'anggota@kasorg.test', 'nim' => '24170002']],
            [['email' => 'admin@kasorg.test']], [['nim' => '24170001']], [['nim' => '24170002']]];
    }

    public function test_reserved_demo_identity_cannot_be_used_for_new_account(): void
    {
        $this->write('POST', route('admin.anggota.store'), $this->payload(['nim' => '24170002', 'email' => 'anggota@kasorg.test']))
            ->assertSessionHasErrors(['nim', 'email']);
        $this->assertSame(1, User::count());
    }

    public function test_admin_accounts_cannot_be_modified_through_member_routes(): void
    {
        foreach (['PUT' => 'admin.anggota.update', 'PATCH' => 'admin.anggota.status', 'DELETE' => 'admin.anggota.destroy'] as $method => $route) {
            $this->write($method, route($route, $this->admin), $this->payload())->assertForbidden();
        }
    }

    #[DataProvider('writeRoutes')]
    public function test_member_is_forbidden_from_all_crud_actions(string $method, string $route): void
    {
        $member = $this->makeUser();
        $before = User::withTrashed()->get()->toJson();
        $this->actingAs($member);
        $this->write($method, route($route, $route === 'admin.anggota.store' ? [] : $member), $this->payload())
            ->assertForbidden();
        $this->get(route('admin.anggota'))->assertForbidden();
        $this->assertTrue($before === User::withTrashed()->get()->toJson());
    }

    public static function writeRoutes(): array
    {
        return [['POST', 'admin.anggota.store'], ['PUT', 'admin.anggota.update'],
            ['PATCH', 'admin.anggota.status'], ['DELETE', 'admin.anggota.destroy']];
    }

    #[DataProvider('writeRoutes')]
    public function test_all_write_routes_require_csrf(string $method, string $route): void
    {
        $member = $this->makeUser();
        $before = User::withTrashed()->get()->toJson();
        $uri = route($route, $route === 'admin.anggota.store' ? [] : $member);
        $this->call($method, $uri, $this->payload())->assertStatus(419);
        $this->assertTrue($before === User::withTrashed()->get()->toJson());
    }

    public function test_guest_cannot_create_and_missing_member_binding_returns_404(): void
    {
        $this->write('PUT', route('admin.anggota.update', 999999), $this->payload())->assertNotFound();
        Auth::logout();
        $this->write('POST', route('admin.anggota.store'), $this->payload())->assertRedirect(route('login'));
    }

    public function test_xss_is_escaped_in_html_and_modal_uses_no_inner_html(): void
    {
        $name = '<img src=x onerror=alert(1)>';
        $member = $this->makeUser(attributes: ['name' => $name]);
        $this->get(route('admin.anggota'))->assertOk()->assertSee(e($name), false)
            ->assertDontSee($name, false)->assertDontSee('innerHTML', false);
        $this->assertSame($name, $member->fresh()->name);
    }

    public function test_invalid_list_filters_return_indonesian_validation_errors(): void
    {
        $this->get(route('admin.anggota', ['status' => 'lunas', 'per_page' => 1000, 'q' => str_repeat('a', 101), 'page' => -1]))
            ->assertRedirect(route('admin.anggota'))->assertSessionHasErrors(['status', 'per_page', 'q', 'page']);
    }

    public function test_local_mock_preview_still_does_not_query_database_or_link_to_crud_actions(): void
    {
        Auth::logout();
        app()->instance('env', 'local');
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::getRoutes()->refreshNameLookups();
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });
        $this->get(route('ui-preview.admin.anggota'))->assertOk()->assertSee('Jovan Putra Wijaya')
            ->assertSee('hanya berlaku pada tampilan')
            ->assertDontSee('action="'.route('admin.anggota.store').'"', false);
        $this->assertSame([], $queries);
    }

    public function test_password_visibility_mouse_keyboard_and_lucide_in_chrome(): void
    {
        $browser = getenv('KASORG_BROWSER_EXECUTABLE');
        if (! $browser) {
            $this->markTestSkipped('Tetapkan KASORG_BROWSER_EXECUTABLE untuk menjalankan Chrome CDP dengan aset asli.');
        }
        $html = $this->get(route('admin.anggota'))->assertOk()->getContent();
        $results = ChromiumPasswordCheck::runInteractive($html, $browser);
        $this->assertGreaterThanOrEqual(40, count($results));
        foreach ($results as $result) {
            $this->assertTrue($result['passed'], $result['name']);
        }
    }

    private function makeUser(string $role = Role::ANGGOTA, array $attributes = []): User
    {
        $roleModel = Role::firstOrCreate(['kode' => $role], ['nama' => $role === 'anggota' ? 'Anggota Organisasi' : 'Admin']);
        $sequence = ++$this->sequence;

        return User::create([
            'name' => 'Pengguna Uji '.$sequence, 'nim' => '249000'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'email' => 'uji-anggota-'.$sequence.'@kasorg.test', 'password' => $this->password,
            'role_id' => $roleModel->id, 'status_akun' => 'aktif', ...$attributes,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Anggota Baru', 'nim' => '24888888', 'email' => 'anggota-baru@kasorg.test',
            'status_akun' => 'aktif', 'password' => $this->password, 'password_confirmation' => $this->password,
            ...$overrides,
        ];
    }

    private function write(string $method, string $uri, array $data = []): TestResponse
    {
        $token = session()->token() ?? Str::random(40);

        return $this->withSession(['_token' => $token])->call($method, $uri, [...$data, '_token' => $token]);
    }
}
