<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Vite;
use Laravel\Passport\Passport;
use Tests\Concerns\CreatesFixtures;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use CreatesFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        // Tests assert against the Vite build manifest. Ignore a leftover
        // public/hot file from `npm run dev` so asset URLs are deterministic.
        $this->app->make(Vite::class)
            ->useHotFile(sys_get_temp_dir().'/odogwu-vite-hot-missing-'.getmypid());

        // Token-issuing endpoints need an RSA key pair. Generate an ephemeral
        // pair outside the repository so no key material is ever committed.
        $keyDir = sys_get_temp_dir().'/odogwu-passport-tests-'.getmypid();
        if (!is_dir($keyDir)) {
            mkdir($keyDir, 0700, true);
        }

        $privateKey = $keyDir.'/oauth-private.key';
        $publicKey = $keyDir.'/oauth-public.key';

        if (!file_exists($privateKey) || !file_exists($publicKey)) {
            $candidates = [
                'C:\\Program Files\\Git\\usr\\bin\\openssl.exe',
                '/usr/bin/openssl',
                'openssl',
            ];
            $openssl = null;
            foreach ($candidates as $candidate) {
                if (is_executable($candidate) || ($candidate === 'openssl' && shell_exec('openssl version'))) {
                    $openssl = $candidate;
                    break;
                }
            }

            if (!$openssl) {
                $this->markTestSkipped('OpenSSL CLI is required to generate ephemeral Passport test keys.');
            }

            $privateArg = escapeshellarg($privateKey);
            $publicArg = escapeshellarg($publicKey);
            exec(escapeshellarg($openssl).' genrsa -out '.$privateArg.' 2048 2>'.(PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'), $output, $status);
            if ($status !== 0) {
                $this->markTestSkipped('Unable to generate Passport test keys.');
            }
            exec(escapeshellarg($openssl).' rsa -in '.$privateArg.' -pubout -out '.$publicArg.' 2>'.(PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'), $output, $status);
            if ($status !== 0) {
                $this->markTestSkipped('Unable to generate Passport test keys.');
            }
        }

        Passport::loadKeysFrom($keyDir);
    }
}
