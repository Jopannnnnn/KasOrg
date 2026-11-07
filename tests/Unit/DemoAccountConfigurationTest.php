<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

// Tidak boot Laravel/.env, tidak memanggil seeder, dan tidak membuka koneksi DB.
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class DemoAccountConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['KASORG_DEMO_ADMIN_PASSWORD', 'KASORG_DEMO_ANGGOTA_PASSWORD', 'KASORG_DEMO_PASSWORD'] as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }
        // Proses terpisah: konfigurasi sementara tidak memengaruhi proses aplikasi.
        $app = new Container;
        $app->instance('config', new Repository(['hashing' => ['driver' => 'bcrypt', 'bcrypt' => ['rounds' => 4, 'verify' => true]]]));
        $app->instance('hash', new HashManager($app));
        Facade::setFacadeApplication($app);
        User::setConnectionResolver(new class implements ConnectionResolverInterface
        {
            public function connection($name = null)
            {
                throw new \RuntimeException('Koneksi database dilarang pada tes konfigurasi demo.');
            }

            public function getDefaultConnection()
            {
                return 'blocked';
            }

            public function setDefaultConnection($name) {}
        });
    }

    private function source(string $path): string
    {
        $root = dirname(__DIR__, 2);
        if (getenv('KASORG_DEMO_GIT_INDEX') !== '1') {
            $source = file_get_contents($root.'/'.$path);
            $this->assertIsString($source);

            return $source;
        }
        $process = proc_open(['git', 'show', ':'.$path], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $source = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), 'Source dependensi G2 harus tersedia di index.');

        return $source;
    }

    private function configuration(): array
    {
        return eval(substr($this->source('config/demo.php'), strlen('<?php')));
    }

    private function temporaryValue(string $key, string $value): void
    {
        $_ENV[$key] = $_SERVER[$key] = $value;
        putenv($key.'='.$value);
    }

    public function test_konfigurasi_tanpa_password_bawaan_dan_tidak_memakai_kunci_lama(): void
    {
        $this->temporaryValue('KASORG_DEMO_PASSWORD', Str::password(24));
        $config = $this->configuration();
        $this->assertSame(['passwords'], array_keys($config));
        $this->assertSame(['admin', 'anggota'], array_keys($config['passwords']));
        $this->assertTrue($config['passwords']['admin'] === null && $config['passwords']['anggota'] === null);
    }

    public function test_kedua_password_dibaca_terpisah_dari_environment_sementara(): void
    {
        $admin = Str::password(24);
        $anggota = Str::password(25);
        $this->temporaryValue('KASORG_DEMO_ADMIN_PASSWORD', $admin);
        $this->temporaryValue('KASORG_DEMO_ANGGOTA_PASSWORD', $anggota);
        $config = $this->configuration();
        $this->assertTrue(hash_equals($admin, $config['passwords']['admin']));
        $this->assertTrue(hash_equals($anggota, $config['passwords']['anggota']));
        $this->assertFalse(hash_equals($config['passwords']['admin'], $config['passwords']['anggota']));
    }

    public function test_source_memblokir_production_sebelum_konfigurasi_dan_query(): void
    {
        $source = $this->source('database/seeders/UserSeeder.php');
        $guard = strpos($source, "if (! app()->environment(['local', 'testing']))");
        $config = strpos($source, "config('demo.passwords.admin')");
        $query = strpos($source, 'Role::query()');
        $this->assertTrue($guard !== false && $config !== false && $query !== false);
        $this->assertTrue($guard < $config && $config < $query);
        $this->assertStringContainsString('throw new RuntimeException', substr($source, $guard, $config - $guard));
        $this->assertStringContainsString('! is_string($password) || strlen($password) < 8', $source);
        $this->assertStringContainsString('hash_equals($passwords[Role::ADMIN], $passwords[Role::ANGGOTA])', $source);
        $this->assertTrue(strpos($source, 'hash_equals(') < $query);
    }

    public function test_source_menetapkan_hanya_dua_identitas_demo_baru(): void
    {
        $source = $this->source('database/seeders/UserSeeder.php');
        preg_match_all("/'email' => '([^']+)'/", $source, $emails);
        preg_match_all("/'nim' => '([^']+)'/", $source, $nims);
        $this->assertSame(['admin@kasorg.test', 'anggota@kasorg.test'], $emails[1]);
        $this->assertSame(['24170001', '24170002'], $nims[1]);
        $this->assertStringNotContainsString('25170001', $source);
        $this->assertStringNotContainsString('25170002', $source);
        $this->assertStringContainsString("'password' => \$passwords[Role::ADMIN]", $source);
        $this->assertStringContainsString("'password' => \$passwords[Role::ANGGOTA]", $source);
    }

    public function test_source_memeriksa_konflik_dan_melewati_akun_lama_tanpa_update(): void
    {
        $source = $this->source('database/seeders/UserSeeder.php');
        foreach (['User::withTrashed()', "->where('nim', \$account['nim'])", "->orWhere('email', \$account['email'])", '$existing->name', '$existing->role_id', '$existing->status_akun', '! $existing->trashed()', 'if (! $isExactDemoAccount)', 'continue;'] as $fragment) {
            $this->assertStringContainsString($fragment, $source);
        }
        $this->assertTrue(strpos($source, 'continue;') < strpos($source, 'User::query()->create('));
        $this->assertDoesNotMatchRegularExpression('/->(?:update|updateOrCreate|upsert|delete|forceDelete|restore)\s*\(/', $source);
    }

    public function test_model_hash_laravel_berbeda_sesuai_role_tanpa_query_dan_tanpa_hashing_ganda(): void
    {
        $adminPassword = Str::password(24);
        $memberPassword = Str::password(25);
        $admin = new User(['password' => $adminPassword]);
        $member = new User(['password' => $memberPassword]);
        $adminHash = $admin->getAttributes()['password'];
        $memberHash = $member->getAttributes()['password'];
        $this->assertTrue(Hash::isHashed($adminHash) && Hash::isHashed($memberHash));
        $this->assertTrue(Hash::check($adminPassword, $adminHash));
        $this->assertTrue(Hash::check($memberPassword, $memberHash));
        $this->assertFalse(Hash::check($memberPassword, $adminHash));
        $this->assertFalse(Hash::check($adminPassword, $memberHash));
        $alreadyHashed = new User(['password' => $adminHash]);
        $this->assertTrue(hash_equals($adminHash, $alreadyHashed->getAttributes()['password']));
        $this->assertArrayNotHasKey('password', $admin->toArray());
        $this->assertArrayNotHasKey('password', $member->toArray());
    }

    public function test_dependensi_dan_urutan_role_telah_tersedia_pada_snapshot(): void
    {
        $user = $this->source('app/Models/User.php');
        $role = $this->source('app/Models/Role.php');
        $seeder = $this->source('database/seeders/DatabaseSeeder.php');
        $factory = $this->source('database/factories/UserFactory.php');
        $this->assertStringContainsString("'password' => 'hashed'", $user);
        $this->assertStringContainsString("public const ADMIN = 'admin'", $role);
        $this->assertStringContainsString("public const ANGGOTA = 'anggota'", $role);
        $this->assertTrue(strpos($seeder, 'RoleSeeder::class') < strpos($seeder, 'UserSeeder::class'));
        $this->assertStringContainsString('Hash::make(Str::password(32))', $factory);
    }
}
