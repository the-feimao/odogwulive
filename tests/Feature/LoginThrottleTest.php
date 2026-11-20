<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    #[Test]
    public function login_endpoints_are_rate_limited()
    {
        $organizer = $this->organizer();
        $admin = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $webCustomer = $this->customer();
        $userApiCustomer = $this->customer();

        $cases = [
            ['/login', [
                'email' => $webCustomer->email,
                'password' => 'wrong-password',
            ]],
            ['/user/login', [
                'email' => $userApiCustomer->email,
                'provider' => 'LOCAL',
                'password' => 'wrong-password',
                'device_token' => 'device-token',
            ]],
            ['/api/user/login', [
                'email' => $this->customer()->email,
                'provider' => 'LOCAL',
                'password' => 'wrong-password',
                'device_token' => 'device-token',
            ]],
            ['/api/organization/login', [
                'email' => $organizer->email,
                'password' => 'wrong-password',
                'device_token' => 'device-token',
            ]],
            ['/api/scanner/login', [
                'email' => $scanner->email,
                'password' => 'wrong-password',
            ]],
            ['/admin/login', [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]],
        ];

        foreach ($cases as [$uri, $payload]) {
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $response = $this->postJson($uri, $payload);
                $this->assertNotSame(429, $response->status(), $uri.' was throttled too early');
            }

            $this->postJson($uri, $payload)->assertStatus(429);
        }
    }
}
