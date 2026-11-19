<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    #[Test]
    public function the_login_page_preserves_the_existing_form_contract()
    {
        $this->get('/user/login')
            ->assertOk()
            ->assertSee('action="'.url('user/login').'"', false)
            ->assertSee('data-qa="form-login"', false)
            ->assertSee('name="login"', false)
            ->assertSee('name="url"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('data-qa="forgot-password-link"', false)
            ->assertSee('href="'.url('user/resetPassword').'"', false)
            ->assertSee('href="'.url('user/register').'"', false)
            ->assertSee('href="'.url('user/org-register').'"', false)
            ->assertSee('data-qa="login-button"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('Card-sc-8zkhaa-0', false);
    }

    #[Test]
    public function the_registration_page_preserves_the_existing_form_contract()
    {
        $this->get('/user/register')
            ->assertOk()
            ->assertSee('action="'.url('user/register').'"', false)
            ->assertSee('name="url"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="password"', false)
            ->assertSee('href="'.url('user/login').'"', false)
            ->assertSee('href="'.url('user/org-register').'"', false)
            ->assertSee('data-qa="login-button"', false)
            ->assertSee('autocomplete="given-name"', false)
            ->assertSee('autocomplete="family-name"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('card-register', false);
    }

    #[Test]
    public function the_reset_password_page_preserves_the_existing_form_contract()
    {
        $this->get('/user/resetPassword')
            ->assertOk()
            ->assertSee('action="'.url('user/resetPassword').'"', false)
            ->assertSee('data-qa="form-login"', false)
            ->assertSee('name="login"', false)
            ->assertSee('name="url"', false)
            ->assertSee('name="email"', false)
            ->assertSee('href="'.url('user/login').'"', false)
            ->assertSee('data-qa="login-button"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('NGrUbJBA', false);
    }

    #[Test]
    public function the_organizer_registration_page_preserves_the_existing_form_contract()
    {
        $this->get('/user/org-register')
            ->assertOk()
            ->assertSee('action="'.url('user/org-register').'"', false)
            ->assertSee('name="url"', false)
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="confirm_password"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="country"', false)
            ->assertSee('href="'.url('login').'"', false)
            ->assertSee('data-qa="login-button"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('select2', false);
    }

    #[Test]
    public function login_validation_errors_render_in_the_new_design()
    {
        $this->from(url('user/login'))
            ->post('/user/login', [])
            ->assertRedirect(url('user/login'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->get('/user/login')
            ->assertOk()
            ->assertSee('The email field is required.', false)
            ->assertSee('The password field is required.', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false);
    }
}
