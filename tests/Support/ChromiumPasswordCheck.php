<?php

namespace Tests\Support;

class ChromiumPasswordCheck
{
    public static function runInteractive(string $html, string $browser, string $scenario = 'anggota'): array
    {
        if (! is_file($browser)) {
            throw new \RuntimeException('Executable Chromium tidak tersedia.');
        }
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kasorg-browser-'.bin2hex(random_bytes(8));
        if (! mkdir($root, 0700)) {
            throw new \RuntimeException('Direktori fixture browser tidak dapat dibuat.');
        }
        $server = null;
        try {
            // A dedicated static-only server: never run public/index.php or boot
            // Laravel in the child process, even if a form accidentally submits.
            $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
            if ($socket === false) {
                throw new \RuntimeException('Port fixture browser tidak tersedia.');
            }
            $address = stream_socket_get_name($socket, false);
            fclose($socket);
            $origin = 'http://'.$address;
            $html = self::fixtureHtml($html, $origin);
            file_put_contents($root.'/fixture.html', $html);
            $router = <<<'PHP'
<?php
// Generated fixture router. No Composer, Dotenv, Laravel or database code.
$root = __DIR__;
$public = __PUBLIC_ROOT__;
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (! in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
    http_response_code(405);
    exit;
}
$file = $path === '/fixture.html' ? $root.'/fixture.html' : realpath($public.'/'.$path);
$types = ['html'=>'text/html; charset=UTF-8','css'=>'text/css','js'=>'application/javascript','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','svg'=>'image/svg+xml','png'=>'image/png','ico'=>'image/x-icon'];
$extension = $file ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : '';
if (! $file || ! is_file($file) || ! isset($types[$extension])
    || ($path !== '/fixture.html' && (! str_starts_with($file, $public.DIRECTORY_SEPARATOR) || $extension === 'html'))) {
    http_response_code(404);
    exit;
}
header('Content-Type: '.$types[$extension]);
header('Cache-Control: no-store');
header("Content-Security-Policy: form-action 'none'; connect-src 'self'");
if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
    readfile($file);
}
PHP;
            $public = realpath(public_path());
            if ($public === false) {
                throw new \RuntimeException('Folder public fixture tidak tersedia.');
            }
            file_put_contents($root.'/router.php', str_replace('__PUBLIC_ROOT__', var_export($public, true), $router));
            $server = proc_open([PHP_BINARY, '-n', '-S', $address, $root.'/router.php'], [0 => ['pipe', 'r'], 1 => ['file', $root.'/server.log', 'a'], 2 => ['file', $root.'/server.log', 'a']], $serverPipes, $root);
            if (! is_resource($server)) {
                throw new \RuntimeException('Server statis fixture tidak dapat dijalankan.');
            }
            fclose($serverPipes[0]);
            $ready = false;
            $deadline = microtime(true) + 5;
            do {
                // Confirm our PHP process owns the listener before connecting;
                // do not contact an unrelated local app if port binding failed.
                $started = str_contains((string) @file_get_contents($root.'/server.log'), 'Development Server ('.$origin.') started');
                if ($started && proc_get_status($server)['running']) {
                    $probe = @stream_socket_client('tcp://'.$address, $errno, $error, 0.1);
                    if ($probe !== false) {
                        fclose($probe);
                        $ready = true;
                        break;
                    }
                }
                usleep(50000);
            } while (microtime(true) < $deadline && proc_get_status($server)['running']);
            if (! $ready) {
                throw new \RuntimeException('Server statis fixture tidak siap.');
            }

            $process = proc_open(['powershell', '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', base_path('tests/Browser/password-visibility.ps1'), '-HtmlUrl', $origin.'/fixture.html', '-Browser', $browser, '-Scenario', $scenario], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new \RuntimeException('Pengujian browser tidak dapat dijalankan.');
            }
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            if (proc_close($process) !== 0) {
                throw new \RuntimeException('Pengujian interaksi Chrome gagal: '.$errors);
            }

            return json_decode(trim($output), true, flags: JSON_THROW_ON_ERROR);
        } finally {
            if (is_resource($server)) {
                proc_terminate($server);
                proc_close($server);
            }
            self::removeFixture($root);
        }
    }

    public static function run(string $html, string $browser): array
    {
        $html = str_replace('</body>', '<script>'.file_get_contents(base_path('tests/Browser/password-toggle.js')).'</script></body>', $html);

        return self::runInteractive($html, $browser, 'password-dom');
    }

    private static function fixtureHtml(string $html, string $origin): string
    {
        // Redirect all application URLs, including form/data attributes and
        // inline JSON, to the static fixture, never to a developer's live app.
        $applicationOrigin = static fn (string $url): string => preg_replace('~^(https?://[^/]+).*$~', '$1', $url);
        foreach (array_unique([$applicationOrigin(url('/')), $applicationOrigin(config('app.url'))]) as $source) {
            $html = str_replace([$source, str_replace('/', '\\/', $source)], [$origin, str_replace('/', '\\/', $origin)], $html);
        }

        return $html;
    }

    private static function removeFixture(string $root): void
    {
        // Only the random directory owned by this call, never a user profile.
        $resolved = realpath($root);
        $parent = realpath(sys_get_temp_dir());
        if ($resolved && $parent && strcasecmp(dirname($resolved), $parent) === 0
            && preg_match('/^kasorg-browser-[a-f0-9]{16}$/', basename($resolved))) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($resolved, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
                if ($entry->isDir() && ! $entry->isLink()) {
                    @rmdir($entry->getPathname());
                } else {
                    @unlink($entry->getPathname());
                }
            }
            @rmdir($resolved);
        }
    }
}
