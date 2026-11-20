<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminShellAndSettingsTest extends TestCase
{
    private const LEGACY_CDN_MARKERS = [
        'https://stackpath.bootstrapcdn.com',
        'https://code.jquery.com',
        'https://use.fontawesome.com',
        'https://cdn.datatables.net',
        'https://cdn.jsdelivr.net/npm/sweetalert2',
        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js',
        'https://jvectormap.com',
    ];

    private function activateLicense(): void
    {
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function admin_shell_bundles_legacy_assets_and_removes_cdns()
    {
        $this->activateLicense();
        PaymentSetting::find(1)->update(['paypalClientId' => 'test-paypal-client']);

        $response = $this->actingAs($this->admin())->get(url('admin-setting'));

        $response->assertOk()
            ->assertSee('ds-admin', false)
            ->assertSee('ds-settings', false)
            ->assertSee('action="'.url('save-general-setting').'"', false)
            ->assertSee('action="'.url('save-payment-setting').'"', false)
            ->assertSee('admin-legacy', false)
            ->assertSee('defer src="'.url('admin/js/myCustom.js').'"', false)
            ->assertSee('data-namespace="paypal_sdk"', false);

        foreach (self::LEGACY_CDN_MARKERS as $marker) {
            $response->assertDontSee($marker, false);
        }
    }

    #[Test]
    public function license_page_stays_reachable_while_license_is_inactive()
    {
        Setting::find(1)->update(['license_status' => 0]);

        $response = $this->actingAs($this->admin())->get(url('license-setting'));

        $response->assertOk()
            ->assertSee('ds-admin', false)
            ->assertSee('License Setting', false)
            ->assertSee('action="'.url('save-license-setting').'"', false)
            ->assertSee('name="license_key"', false)
            ->assertSee('name="license_name"', false)
            ->assertSee('document.addEventListener(\'DOMContentLoaded\'', false)
            ->assertSee('Swal.fire', false);

        foreach (self::LEGACY_CDN_MARKERS as $marker) {
            $response->assertDontSee($marker, false);
        }
    }

    #[Test]
    public function admin_login_keeps_form_contract_and_drops_cdns()
    {
        $response = $this->get(url('login'));

        $response->assertOk()
            ->assertSee('ds-admin', false)
            ->assertSee('action="'.url('admin/login').'"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('novalidate=""', false)
            ->assertSee('data-background="'.url('images/login-bg.jpg').'"', false);

        foreach (self::LEGACY_CDN_MARKERS as $marker) {
            $response->assertDontSee($marker, false);
        }

        $this->withSession(['error_msg' => 'Invalid Username or Password'])
            ->get(url('login'))
            ->assertOk()
            ->assertSee('Invalid Username or Password', false);
    }
}
