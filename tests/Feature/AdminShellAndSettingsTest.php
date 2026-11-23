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

    private function adminLegacyManifestEntry(): array
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $entry = $manifest['resources/js/admin-legacy.js'] ?? null;

        $this->assertIsArray($entry);

        return $entry;
    }

    private function adminLegacyCssPath(): string
    {
        $entry = $this->adminLegacyManifestEntry();
        $this->assertNotEmpty($entry['css'] ?? []);

        return '/build/'.$entry['css'][0];
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
    public function admin_shell_loads_bootstrap_css_before_legacy_admin_css()
    {
        $this->activateLicense();

        $response = $this->actingAs($this->admin())->get(url('admin-setting'));
        $response->assertOk();

        $html = $response->getContent();
        $bundleCssPath = $this->adminLegacyCssPath();
        $bundleCssPosition = strpos($html, $bundleCssPath);
        $adminCssPosition = strpos($html, 'admin/css/style.css');

        $this->assertNotFalse($bundleCssPosition, 'The admin bundle CSS is not linked.');
        $this->assertNotFalse($adminCssPosition, 'The legacy admin stylesheet is not linked.');
        $this->assertLessThan($adminCssPosition, $bundleCssPosition, 'Bootstrap CSS must load before Stisla overrides.');

        $css = (string) file_get_contents(public_path(ltrim($bundleCssPath, '/')));
        $this->assertStringContainsString('.navbar', $css);
        $this->assertStringContainsString('.form-inline', $css);
        $this->assertStringContainsString('.mr-auto', $css);
    }

    #[Test]
    public function admin_legacy_bundle_registers_plugins_without_pdfmake_module_error()
    {
        $entry = $this->adminLegacyManifestEntry();
        $this->assertNotEmpty($entry['file'] ?? null);

        $vfsFonts = array_values(array_filter(
            $entry['assets'] ?? [],
            static fn (string $asset): bool => str_starts_with($asset, 'assets/vfs_fonts-')
        ));
        $this->assertNotEmpty($vfsFonts, 'pdfMake fonts must be emitted as a local classic script.');
        $this->assertFileExists(public_path('build/'.$vfsFonts[0]));

        $js = (string) file_get_contents(public_path('build/'.$entry['file']));
        $this->assertStringNotContainsString('(void 0).pdfMake=', $js);
        $this->assertStringContainsString('/build/'.$vfsFonts[0], $js);

        foreach ([
            'fn.flatpickr=function',
            'fn.select2.amd=',
            'fn.dataTable',
            'Buttons.jszip=function',
            'Buttons.pdfMake=function',
        ] as $registration) {
            $this->assertStringContainsString($registration, $js, 'Missing bundled plugin registration: '.$registration);
        }
    }

    #[Test]
    public function admin_shell_does_not_apply_public_theme_preference()
    {
        $this->activateLicense();

        $adminResponse = $this->actingAs($this->admin())->get(url('admin-setting'));
        $adminResponse->assertOk()
            ->assertSee('ds-admin', false)
            ->assertDontSee("localStorage.getItem('ds-theme')", false)
            ->assertDontSee("classList.toggle('tw-dark'", false);

        auth()->logout();

        $this->get(url('login'))
            ->assertOk()
            ->assertDontSee("localStorage.getItem('ds-theme')", false)
            ->assertDontSee("classList.toggle('tw-dark'", false);
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

    #[Test]
    public function admin_payment_settings_expose_paystack_fields()
    {
        $this->activateLicense();
        PaymentSetting::find(1)->update([
            'paystack' => 1,
            'paystackPublicKey' => 'pk_test_visible',
            'paystackSecretKey' => 'sk_test_stored',
        ]);

        $response = $this->actingAs($this->admin())->get(url('admin-setting'));

        $response->assertOk()
            ->assertSee('name="paystack"', false)
            ->assertSee('name="paystackPublicKey"', false)
            ->assertSee('name="paystackSecretKey"', false)
            ->assertSee('value="pk_test_visible"', false)
            ->assertSee('value="sk_test_stored"', false)
            ->assertSee('Paystack', false);
    }

    #[Test]
    public function admin_can_save_paystack_settings_without_touching_the_real_env_file()
    {
        $envDirectory = sys_get_temp_dir().'/odogwu-paystack-env-'.uniqid();
        mkdir($envDirectory, 0700, true);
        $envPath = $envDirectory.'/.env';
        file_put_contents($envPath, "APP_NAME=Odogwu\nRAVE_PUBLIC_KEY=old-rave-public\nRAVE_SECRET_KEY=old-rave-secret\n");

        $this->app->useEnvironmentPath($envDirectory);
        $this->app->loadEnvironmentFrom('.env');

        try {
            $this->actingAs($this->admin())
                ->post(url('save-payment-setting'), [
                    'cod' => '1',
                    'paystack' => '1',
                    'paystackPublicKey' => 'pk_test_123',
                    'paystackSecretKey' => 'sk_test_456',
                ])
                ->assertRedirect(url('admin-setting'));

            $this->assertDatabaseHas('payment_setting', [
                'id' => 1,
                'paystack' => 1,
                'paystackPublicKey' => 'pk_test_123',
                'paystackSecretKey' => 'sk_test_456',
            ]);

            $envContents = (string) file_get_contents($envPath);
            $this->assertStringContainsString('PAYSTACK_PUBLIC_KEY=pk_test_123', $envContents);
            $this->assertStringContainsString('PAYSTACK_SECRET_KEY=sk_test_456', $envContents);

            $this->actingAs($this->admin())
                ->post(url('save-payment-setting'), [
                    'cod' => '1',
                    'paystackPublicKey' => 'pk_test_123',
                    'paystackSecretKey' => 'sk_test_456',
                ])
                ->assertRedirect(url('admin-setting'));

            $this->assertDatabaseHas('payment_setting', ['id' => 1, 'paystack' => 0]);
        } finally {
            if (is_file($envPath)) {
                unlink($envPath);
            }

            if (is_dir($envDirectory)) {
                rmdir($envDirectory);
            }
        }
    }

    #[Test]
    public function paystack_keys_are_required_when_the_gateway_is_enabled()
    {
        $this->actingAs($this->admin())
            ->from(url('admin-setting'))
            ->post(url('save-payment-setting'), ['paystack' => '1'])
            ->assertRedirect(url('admin-setting'))
            ->assertSessionHasErrors(['paystackPublicKey', 'paystackSecretKey']);
    }
}
