<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Connectors\SQLiteConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

require_once dirname(__DIR__).'/vendor/autoload.php';

/** Guards run as PHPUnit bootstrap, before test discovery or Laravel providers. */
final class TestDatabaseSafety
{
    public static function assertProcessEnvironment(): void
    {
        foreach (['KASORG_SUMMARY_MARIADB', 'KASORG_API_MARIADB', 'KASORG_FINANCE_MARIADB', 'KASORG_MANUAL_MARIADB'] as $key) {
            if (in_array('1', array_map('strval', [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null]), true)) {
                throw new RuntimeException('Pengujian ditolak: mode '.$key.' dilarang dalam suite SQLite.');
            }
        }

        foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => ''] as $key => $expected) {
            foreach ([getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null] as $value) {
                if ($value !== null && $value !== false && $value !== $expected) {
                    throw new RuntimeException('Pengujian ditolak: konfigurasi proses '.$key.' tidak aman.');
                }
            }
            if (getenv($key) !== $expected) {
                throw new RuntimeException('Jalankan PHPUnit dengan phpunit.xml proyek; konfigurasi testing wajib tersedia.');
            }
        }

        // Do not follow an inherited cache path outside this test application.
        foreach (['APP_CONFIG_CACHE', 'APP_ROUTES_CACHE', 'APP_EVENTS_CACHE', 'APP_PACKAGES_CACHE', 'APP_SERVICES_CACHE', 'APP_BASE_PATH'] as $key) {
            foreach ([getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null] as $value) {
                if ($value !== null && $value !== false && $value !== '') {
                    throw new RuntimeException('Pengujian ditolak: override '.$key.' tidak diizinkan.');
                }
            }
        }
    }

    public static function assertNoCachedConfiguration(): void
    {
        // Check existence only: never execute or read a cached production config.
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            throw new RuntimeException('Pengujian ditolak: config cache ditemukan. Gunakan salinan pengujian tanpa config cache.');
        }
    }

    public static function assertApplication(Application $app): void
    {
        self::assertProcessEnvironment();
        // Preview tests may simulate app()->environment('local'/'production');
        // their underlying configuration must still be the testing configuration.
        if ($app['config']->get('app.env') !== 'testing'
            || $app['config']->get('database.default') !== 'sqlite') {
            throw new RuntimeException('Pengujian ditolak: environment/default koneksi Laravel tidak aman.');
        }
        self::assertConnection($app['config']->get('database.connections.sqlite', []));
    }

    public static function assertConnection(array $config): void
    {
        if (($config['driver'] ?? null) !== 'sqlite'
            || ($config['database'] ?? null) !== ':memory:'
            || ! in_array($config['url'] ?? null, [null, ''], true)
            || isset($config['read']) || isset($config['write'])
            || ! empty($config['pragmas'])) {
            throw new RuntimeException('Pengujian ditolak: hanya SQLite :memory: tanpa URL/read-write/pragmas tambahan yang diizinkan.');
        }
    }
}

TestDatabaseSafety::assertProcessEnvironment();
TestDatabaseSafety::assertNoCachedConfiguration();

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        TestDatabaseSafety::assertProcessEnvironment();
        TestDatabaseSafety::assertNoCachedConfiguration();

        $app = require dirname(__DIR__).'/bootstrap/app.php';
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));

        // Never invoke Dotenv, including fallback to the real .env or .env.testing.
        $app->instance(LoadEnvironmentVariables::class, new class extends LoadEnvironmentVariables
        {
            public function bootstrap($app) {}
        });

        // Runtime/cache/uploads are isolated from the application's real storage.
        $runtime = sys_get_temp_dir().'/kasorg-phpunit-'.bin2hex(random_bytes(12));
        foreach (['bootstrap/cache', 'storage/framework/views', 'storage/framework/testing', 'storage/logs'] as $directory) {
            if (! mkdir($runtime.'/'.$directory, 0700, true) && ! is_dir($runtime.'/'.$directory)) {
                throw new RuntimeException('Direktori runtime pengujian tidak dapat dibuat.');
            }
        }
        $app->useStoragePath($runtime.'/storage');
        $app->useBootstrapPath($runtime.'/bootstrap');

        $app->afterBootstrapping(LoadConfiguration::class, function (Application $app) use ($runtime): void {
            TestDatabaseSafety::assertApplication($app);
            if ($app->configurationIsCached()
                || $app->resolved('db') || $app->resolved('db.factory')
                || $app['config']->get('session.driver') !== 'array'
                || $app['config']->get('cache.default') !== 'array'
                || $app['config']->get('queue.default') !== 'sync') {
                throw new RuntimeException('Pengujian ditolak: konfigurasi runtime harus terisolasi dan tanpa cache konfigurasi.');
            }
            $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
            $app['config']->set('view.compiled', $runtime.'/storage/framework/views');
            $app['config']->set('logging.default', 'null');

            // Extenders are installed BEFORE RegisterProviders/BootProviders.
            // The factory also guards DB::build, connectUsing and lazy PDO access.
            $app->extend('db.factory', fn () => new class($app) extends ConnectionFactory
            {
                public function make(array $config, $name = null)
                {
                    TestDatabaseSafety::assertApplication($this->container);
                    TestDatabaseSafety::assertConnection($config);

                    return parent::make($config, $name);
                }

                public function createConnector(array $config)
                {
                    TestDatabaseSafety::assertApplication($this->container);
                    TestDatabaseSafety::assertConnection($config);

                    return new class($this->container) extends SQLiteConnector
                    {
                        public function __construct(private Application $app) {}

                        public function connect(array $config)
                        {
                            TestDatabaseSafety::assertApplication($this->app);
                            TestDatabaseSafety::assertConnection($config);

                            return parent::connect($config);
                        }
                    };
                }
            });

            // Check named/cached connections too, before driver extensions run.
            $app->extend('db', fn ($manager, $app) => new class($app, $app['db.factory']) extends DatabaseManager
            {
                protected function configuration($name)
                {
                    $config = $this->dynamicConnectionConfigurations[$name]
                        ?? $this->app['config']->get('database.connections.'.$name, []);
                    if (! is_array($config)) {
                        throw new RuntimeException('Pengujian ditolak: konfigurasi koneksi harus berupa array SQLite RAM.');
                    }
                    TestDatabaseSafety::assertConnection($config);

                    return parent::configuration($name);
                }

                public function connection($name = null)
                {
                    TestDatabaseSafety::assertApplication($this->app);
                    [$database] = $this->parseConnectionName(\Illuminate\Support\enum_value($name) ?: $this->getDefaultConnection());
                    TestDatabaseSafety::assertConnection($this->configuration($database));

                    return parent::connection($name);
                }

                public function connectUsing(string $name, array $config, bool $force = false)
                {
                    TestDatabaseSafety::assertApplication($this->app);
                    TestDatabaseSafety::assertConnection($config);

                    return parent::connectUsing($name, $config, $force);
                }
            });
        });

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
