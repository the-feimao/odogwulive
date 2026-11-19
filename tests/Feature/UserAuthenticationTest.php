<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthenticationTest extends TestCase
{
    /** @test */
    public function a_visitor_can_register_and_is_logged_in()
    {
        $response = $this->post('/user/register', [
            'name' => 'New',
            'last_name' => 'Customer',
            'email' => 'new-customer-'.uniqid().'@example.test',
            'phone' => '08012345678',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated('appuser');
        $this->assertDatabaseHas('app_user', [
            'name' => 'New',
            'last_name' => 'Customer',
            'provider' => 'LOCAL',
            'status' => 1,
        ]);
    }

    /** @test */
    public function registration_rejects_a_duplicate_email()
    {
        $existing = $this->customer();

        $this->from('/user/register')->post('/user/register', [
            'name' => 'Duplicate',
            'last_name' => 'Customer',
            'email' => $existing->email,
            'phone' => '08012345678',
            'password' => 'secret123',
        ])->assertRedirect('/user/register')->assertSessionHasErrors('email');
    }

    /** @test */
    public function a_registered_user_can_log_in_and_bad_credentials_are_rejected()
    {
        $user = $this->customer(['password' => Hash::make('secret123')]);

        $this->post('/user/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($user, 'appuser');

        Auth::guard('appuser')->logout();

        $this->from('/user/login')->post('/user/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/user/login')->assertSessionHas('error_msg', 'Invalid Username or Password.');
    }

    /** @test */
    public function the_user_api_issues_a_token_on_login()
    {
        $user = $this->customer(['password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/user/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'provider' => 'LOCAL',
            'device_token' => 'test-device-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('msg', 'Login successfully')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('app_user', ['id' => $user->id, 'device_token' => 'test-device-token']);
        $this->assertDatabaseCount('oauth_access_tokens', 1);
    }
}
