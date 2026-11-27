<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Settlement;
use App\Models\Setting;
use App\Support\PaystackTransferClient;
use App\Support\PaystackTransferException;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayoutManagementTest extends TestCase
{
    #[Test]
    public function an_organizer_can_save_bank_details_that_are_encrypted_and_masked_for_admins()
    {
        $organizer = $this->organizer();

        $this->actingAs($organizer)->post('/organization/paystack-account', [
            'bank_name' => 'Access Bank',
            'bank_code' => '044',
            'account_number' => '0123456789',
            'account_name' => 'Test Organizer',
        ])->assertRedirect('/profile');

        $stored = (string) DB::table('users')->where('id', $organizer->id)->value('account_number');
        $this->assertNotSame('0123456789', $stored);

        $organizer->refresh();
        $this->assertSame('0123456789', $organizer->account_number);
        $this->assertTrue($organizer->hasPayoutDetails());
        $this->assertStringEndsWith('6789', $organizer->maskedAccountNumber());
        $this->assertStringNotContainsString('0123456789', $organizer->maskedAccountNumber());

        // A payable balance is required before the payout button is offered.
        $this->outstandingOrder($organizer, 100, 20);

        Setting::find(1)->update(['license_status' => 1]);
        $this->actingAs($this->admin())->get('/admin-report/settlement')
            ->assertOk()
            ->assertSee('Access Bank')
            ->assertSee($organizer->maskedAccountNumber(), false)
            ->assertSee('Pay with Paystack', false)
            ->assertDontSee('0123456789', false);
    }

    #[Test]
    public function paystack_payout_recalculates_the_amount_and_ignores_client_supplied_money()
    {
        $organizer = $this->withPayoutDetails($this->organizer());
        $order = $this->outstandingOrder($organizer, 1500, 250);
        $admin = $this->admin();

        $client = Mockery::mock(PaystackTransferClient::class)->makePartial();
        $client->shouldReceive('createRecipient')->once()->andReturn('RCP_TEST');
        $client->shouldReceive('initiateTransfer')->once()
            ->with(Mockery::on(function (array $payload) {
                return $payload['source'] === 'balance'
                    && $payload['amount'] === 175000
                    && $payload['recipient_code'] === 'RCP_TEST'
                    && $payload['metadata']['settlement_id'] > 0;
            }))
            ->andReturn(['status' => true, 'data' => ['status' => 'success', 'id' => 'TRF_TEST']]);
        $this->app->instance(PaystackTransferClient::class, $client);

        $this->actingAs($admin)->from('/admin-report/settlement')
            ->post('/pay-to-organization-paystack', [
                'user_id' => $organizer->id,
                'payment' => 1,
                'payment_type' => 'LOCAL',
                'payment_status' => 1,
            ])->assertRedirect('/admin-report/settlement')
            ->assertSessionHas('status', __('Paystack payout completed successfully.'));

        $settlement = Settlement::where('user_id', $organizer->id)->firstOrFail();
        $this->assertSame(1750, (int) $settlement->payment);
        $this->assertSame('PAYSTACK', $settlement->payment_type);
        $this->assertSame(1, (int) $settlement->payment_status);
        $this->assertSame('TRF_TEST', $settlement->gateway_reference);
        $this->assertSame(1, (int) $order->fresh()->org_pay_status);
    }

    #[Test]
    public function a_failed_paystack_transfer_leaves_orders_unpaid_and_records_the_reason()
    {
        $organizer = $this->withPayoutDetails($this->organizer());
        $order = $this->outstandingOrder($organizer, 900, 100);

        $client = Mockery::mock(PaystackTransferClient::class)->makePartial();
        $client->shouldReceive('createRecipient')->once()->andReturn('RCP_FAILED');
        $client->shouldReceive('initiateTransfer')->once()
            ->andThrow(new PaystackTransferException('Insufficient balance.'));
        $this->app->instance(PaystackTransferClient::class, $client);

        $this->actingAs($this->admin())->from('/admin-report/settlement')
            ->post('/pay-to-organization-paystack', ['user_id' => $organizer->id])
            ->assertRedirect('/admin-report/settlement')
            ->assertSessionHas('error');

        $settlement = Settlement::where('user_id', $organizer->id)->firstOrFail();
        $this->assertSame(2, (int) $settlement->payment_status);
        $this->assertSame('Insufficient balance.', $settlement->failure_reason);
        $this->assertSame(0, (int) $order->fresh()->org_pay_status);
    }

    #[Test]
    public function a_signed_transfer_webhook_settles_a_payout_and_ignores_retries_and_wrong_amounts()
    {
        $organizer = $this->organizer();
        $order = $this->outstandingOrder($organizer, 1500, 250);
        $settlement = Settlement::create([
            'user_id' => $organizer->id,
            'payment' => 1750,
            'payment_token' => 'PAYOUT-WEBHOOK',
            'payment_type' => 'PAYSTACK',
            'payment_status' => 0,
            'currency' => 'NGN',
        ]);

        $this->postPaystackWebhook([
            'event' => 'transfer.success',
            'data' => [
                'reference' => $settlement->payment_token,
                'status' => 'success',
                'amount' => 100,
            ],
        ])->assertOk()->assertJson(['received' => true, 'ignored' => true]);

        $this->assertSame(0, (int) $settlement->fresh()->payment_status);
        $this->assertSame(0, (int) $order->fresh()->org_pay_status);

        $payload = [
            'event' => 'transfer.success',
            'data' => [
                'reference' => $settlement->payment_token,
                'status' => 'success',
                'amount' => 175000,
            ],
        ];

        $this->postPaystackWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true, 'completed' => true]);

        $this->assertSame(1, (int) $settlement->fresh()->payment_status);
        $this->assertSame(1, (int) $order->fresh()->org_pay_status);

        $this->postPaystackWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true, 'duplicate' => true]);

        $this->assertSame(1, Settlement::where('payment_token', $settlement->payment_token)->count());
    }

    #[Test]
    public function dashboards_show_paid_revenue_platform_revenue_and_outstanding_payables()
    {
        Setting::find(1)->update(['license_status' => 1]);
        $currency = app(\App\Support\RequestData::class)->currencySymbol();

        $first = $this->organizer();
        $second = $this->organizer();
        $this->outstandingOrder($first, 1500, 1000, 10000);
        $this->outstandingOrder($second, 500, 0, 5000);

        // Unpaid orders must never affect revenue metrics.
        $event = $this->event($first);
        $ticket = $this->ticket($event);
        $this->order($event, $ticket, $this->customer(), $first, [
            'payment' => 9999,
            'org_commission' => 999,
            'tax' => 999,
            'payment_status' => 0,
        ]);

        $this->actingAs($this->admin())->get('/admin/home')
            ->assertOk()
            ->assertSee('class="gross-revenue">'.$currency.number_format(15000, 2).'</span>', false)
            ->assertSee('class="platform-revenue">'.$currency.number_format(12000, 2).'</span>', false)
            ->assertSee('class="outstanding-payables">'.$currency.number_format(3000, 2).'</span>', false);

        $this->actingAs($first)->get('/organization/home')
            ->assertOk()
            ->assertSee('class="gross-revenue">'.$currency.number_format(10000, 2).'</span>', false)
            ->assertSee('class="outstanding-payables">'.$currency.number_format(2500, 2).'</span>', false);
    }

    private function withPayoutDetails(\App\Models\User $organizer): \App\Models\User
    {
        $organizer->update([
            'bank_name' => 'Zenith Bank',
            'bank_code' => '057',
            'account_number' => '0123456789',
            'account_name' => 'Payout Account',
            'payout_currency' => 'NGN',
            'payout_verified_at' => now(),
        ]);

        return $organizer->refresh();
    }

    private function outstandingOrder(\App\Models\User $organizer, int $commission, int $tax, int $payment = 0): Order
    {
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);

        return $this->order($event, $ticket, $this->customer(), $organizer, [
            'payment' => $payment > 0 ? $payment : $commission + $tax + 5000,
            'tax' => $tax,
            'org_commission' => $commission,
            'payment_status' => 1,
            'order_status' => 'Complete',
            'org_pay_status' => 0,
        ]);
    }

    private function postPaystackWebhook(array $payload, string $secret = 'sk_test_payout_webhook')
    {
        config()->set('paystack.secretKey', $secret);

        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->json('POST', '/paystack/webhook', $payload, [
            'x-paystack-signature' => hash_hmac('sha512', $json, $secret),
        ]);
    }
}
