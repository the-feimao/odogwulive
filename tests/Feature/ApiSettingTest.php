<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\PaymentSetting;
use App\Models\Setting;
use Tests\TestCase;

class ApiSettingTest extends TestCase
{
    #[Test]
    public function public_settings_do_not_expose_server_secrets()
    {
        Setting::find(1)->update([
            'map_key' => 'secret-map-key',
            'onesignal_app_id' => 'public-app-id',
            'onesignal_project_number' => 'public-project-number',
            'onesignal_api_key' => 'secret-onesignal-api-key',
            'onesignal_auth_key' => 'secret-onesignal-auth-key',
        ]);

        PaymentSetting::find(1)->update([
            'stripe' => 1,
            'stripeSecretKey' => 'secret-stripe-key',
            'stripePublicKey' => 'public-stripe-key',
            'paypalClientId' => 'public-paypal-id',
            'razorPublishKey' => 'public-razor-key',
            'razorSecretKey' => 'secret-razor-key',
        ]);

        $this->getJson('/api/user/setting')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.map_key', null)
            ->assertJsonPath('data.onesignal_api_key', null)
            ->assertJsonPath('data.onesignal_auth_key', null)
            ->assertJsonPath('data.stripeSecretKey', null)
            ->assertJsonPath('data.razorSecretKey', null)
            ->assertJsonPath('data.onesignal_app_id', 'public-app-id')
            ->assertJsonPath('data.stripe', 1)
            ->assertJsonPath('data.stripePublicKey', 'public-stripe-key')
            ->assertJsonPath('data.paypalClientId', 'public-paypal-id')
            ->assertJsonPath('data.razorPublishKey', 'public-razor-key');
    }
}
