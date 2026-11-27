<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\Settlement;
use App\Models\Setting;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminRemainingReportPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    private function configureStripeKey(): void
    {
        PaymentSetting::find(1)->update(['stripePublicKey' => 'report-stripe-key']);
    }

    private function userWithoutRole(): User
    {
        return User::create([
            'name' => 'No Report Access',
            'first_name' => 'No',
            'last_name' => 'Access',
            'email' => 'no-report-'.uniqid().'@example.test',
            'password' => bcrypt('secret123'),
            'status' => 1,
            'language' => 'English',
        ]);
    }

    private function footerCellCount(string $html): int
    {
        preg_match('/<tfoot[\s\S]*?<\/tfoot>/i', $html, $matches);
        $this->assertNotSame('', $matches[0] ?? '', 'The settlement table footer should exist.');

        preg_match_all('/<th\b/i', $matches[0], $cells);

        return count($cells[0]);
    }

    #[Test]
    public function admin_settlement_report_keeps_totals_and_payout_hooks()
    {
        $this->activateLicense();
        $this->configureStripeKey();
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'Settlement', 'last_name' => 'Organizer']);
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_status' => 1,
            'org_commission' => 1,
            'tax' => 0,
            'org_pay_status' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/admin-report/settlement')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Settlement Organizer', false)
            ->assertSee('id="settlement_report"', false)
            ->assertSee('id="paymentModel"', false)
            ->assertSee('id="paypal-button-container"', false)
            ->assertSee('data-key="report-stripe-key"', false)
            ->assertSee('data-amount="100"', false)
            ->assertSee('action="'.url('pay-to-org').'"', false)
            ->assertSee('name="payment"', false)
            ->assertSee('name="user_id"', false)
            ->assertSee('name="payment_type"', false)
            ->assertSee('value="STRIPE"', false)
            ->assertSee('onclick="payLocally()"', false)
            ->assertSee('Page Total:', false)
            ->assertSee('Main Total:', false)
            ->assertSee('href="'.url('view-settlement/'.$organizer->id).'"', false)
            ->assertSee('data-id="'.$organizer->id.'"', false)
            ->assertSee('data-payment="1"', false)
            ->assertSee('data-toggle="modal"', false)
            ->assertSee('data-target="#paymentModel"', false)
            ->assertSee('Payout Account', false)
            ->assertSee('Paystack Status', false)
            ->assertSee('Paystack unavailable', false);

        $this->assertSame(9, $this->footerCellCount($response->getContent()));

        $this->post('/pay-to-organization', [
            'user_id' => $organizer->id,
            'payment' => 1,
            'payment_type' => 'LOCAL',
            'payment_status' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('settlement', [
            'user_id' => $organizer->id,
            'payment' => 1,
            'payment_type' => 'LOCAL',
            'payment_status' => 1,
        ]);
        $this->assertSame(1, (int) $order->fresh()->org_pay_status);

        $this->actingAs($this->organizer())->get('/admin-report/settlement')->assertForbidden();
    }

    #[Test]
    public function organization_revenue_report_keeps_filter_and_gateway_hooks()
    {
        $this->activateLicense();
        $this->configureStripeKey();
        $organizer = $this->organizer(['first_name' => 'Revenue', 'last_name' => 'Organizer']);
        $today = now()->format('Y-m-d');

        Settlement::create([
            'user_id' => $organizer->id,
            'payment' => 250,
            'payment_token' => 'SETTLE-ABC',
            'payment_type' => 'LOCAL',
            'payment_status' => 1,
        ]);

        $this->actingAs($organizer)->get('/organization-report/revenue')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('SETTLE-ABC', false)
            ->assertSee('id="report_table"', false)
            ->assertSee('action="'.url('organization-report/revenue').'"', false)
            ->assertSee('name="duration"', false)
            ->assertSee('date duration', false)
            ->assertSee('id="paymentModel"', false)
            ->assertSee('id="paypal-button-container"', false)
            ->assertSee('data-key="report-stripe-key"', false)
            ->assertSee('onclick="payLocally()"', false);

        $this->post('/organization-report/revenue', ['duration' => $today.' to '.$today])
            ->assertOk()
            ->assertSee('value="'.$today.' to '.$today.'"', false)
            ->assertSee('SETTLE-ABC', false);

        $this->actingAs($this->userWithoutRole())->get('/organization-report/revenue')->assertForbidden();
    }

    #[Test]
    public function settlement_detail_keeps_table_and_owner_checks()
    {
        $this->activateLicense();
        $this->configureStripeKey();
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'Detail', 'last_name' => 'Organizer']);

        Settlement::create([
            'user_id' => $organizer->id,
            'payment' => 500,
            'payment_token' => 'DETAIL-TOKEN',
            'payment_type' => 'LOCAL',
            'payment_status' => 1,
        ]);

        $url = url('view-settlement/'.$organizer->id);

        $this->actingAs($admin)->get($url)->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('DETAIL-TOKEN', false)
            ->assertSee('id="report_table"', false)
            ->assertSee('id="paymentModel"', false)
            ->assertSee('id="paypal-button-container"', false)
            ->assertSee('data-key="report-stripe-key"', false)
            ->assertSee('onclick="payLocally()"', false)
            ->assertSee('Compelete', false);

        $this->actingAs($organizer)->get($url)->assertOk()->assertSee('DETAIL-TOKEN', false);

        $this->actingAs($this->organizer())->get($url)->assertForbidden();
        $this->actingAs($this->userWithoutRole())->get($url)->assertForbidden();
    }
}
