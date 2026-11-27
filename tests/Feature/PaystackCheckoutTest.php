<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackCheckoutTest extends TestCase
{
    #[Test]
    public function an_enabled_paystack_setting_is_offered_at_checkout()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000]);

        PaymentSetting::find(1)->update([
            'paystack' => 1,
            'paypal' => 0,
            'razor' => 0,
            'stripe' => 0,
            'flutterwave' => 0,
            'cod' => 0,
        ]);

        $this->actingAs($this->customer(), 'appuser')
            ->get('/checkout/'.$ticket->id)
            ->assertOk()
            ->assertSee('value="PAYSTACK"', false)
            ->assertSee('>Paystack</span>', false)
            ->assertDontSee('value="LOCAL"', false);
    }

    #[Test]
    public function a_paystack_order_stays_pending_and_starts_the_paystack_flow()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000]);
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')->post('/createOrder', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'price' => 1000,
            'payment' => 1000,
            'tax' => 0,
            'payment_type' => 'PAYSTACK',
            'coupon_id' => null,
            'coupon_discount' => 0,
        ])->assertRedirect('/pay?order='.Order::latest('id')->value('id'));

        $order = Order::latest('id')->first();
        $this->assertSame('PAYSTACK', $order->payment_type);
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
        $this->assertStringStartsWith('ODG-', (string) $order->payment_token);
    }

    #[Test]
    public function a_verified_paystack_callback_completes_the_matching_order()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'PAYSTACK',
            'payment' => 2000,
            'payment_token' => 'ODG-TESTREFERENCE',
            'payment_status' => 0,
            'order_status' => 'Pending',
        ]);

        Paystack::shouldReceive('getPaymentData')->once()->andReturn([
            'status' => true,
            'data' => [
                'reference' => 'ODG-TESTREFERENCE',
                'status' => 'success',
                'amount' => 200000,
                'currency' => 'NGN',
            ],
        ]);

        $this->get('/paystack/callback?reference=ODG-TESTREFERENCE&trxref=ODG-TESTREFERENCE')
            ->assertRedirect('my-tickets');

        $order->refresh();
        $this->assertSame(1, (int) $order->payment_status);
        $this->assertSame('Complete', $order->order_status);
    }

    #[Test]
    public function a_mismatched_paystack_amount_does_not_complete_the_order()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'PAYSTACK',
            'payment' => 2000,
            'payment_token' => 'ODG-BADAMOUNT',
            'payment_status' => 0,
            'order_status' => 'Pending',
        ]);

        Paystack::shouldReceive('getPaymentData')->once()->andReturn([
            'status' => true,
            'data' => [
                'reference' => 'ODG-BADAMOUNT',
                'status' => 'success',
                'amount' => 100,
                'currency' => 'NGN',
            ],
        ]);

        $this->get('/paystack/callback?reference=ODG-BADAMOUNT&trxref=ODG-BADAMOUNT')
            ->assertRedirect('my-tickets');

        $order->refresh();
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
    }

    #[Test]
    public function a_pending_paystack_order_can_be_started_again_from_the_pay_url()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();

        PaymentSetting::find(1)->update([
            'paystack' => 1,
            'paystackPublicKey' => 'pk_test_checkout',
            'paystackSecretKey' => 'sk_test_checkout',
        ]);

        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'PAYSTACK',
            'payment' => 2000,
            'payment_token' => 'ODG-RETRY',
            'payment_status' => 0,
            'order_status' => 'Pending',
        ]);

        $gateway = \Mockery::mock(\Unicodeveloper\Paystack\Paystack::class);
        $gateway->shouldReceive('redirectNow')->once()
            ->andReturn(redirect('https://checkout.paystack.test/mock'));

        Paystack::shouldReceive('getAuthorizationUrl')->once()->andReturn($gateway);

        $this->actingAs($customer, 'appuser')
            ->get('/pay?order='.$order->id)
            ->assertRedirect('https://checkout.paystack.test/mock');
    }

    #[Test]
    public function a_failed_paystack_initialization_shows_a_retry_page_instead_of_the_tickets_page()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();

        PaymentSetting::find(1)->update([
            'paystack' => 1,
            'paystackPublicKey' => 'pk_test_checkout',
            'paystackSecretKey' => 'sk_test_checkout',
        ]);

        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'PAYSTACK',
            'payment' => 2000,
            'payment_token' => 'ODG-FAILED',
            'payment_status' => 0,
            'order_status' => 'Pending',
        ]);

        Paystack::shouldReceive('getAuthorizationUrl')->once()
            ->andThrow(new \RuntimeException('Paystack is unreachable.'));

        $this->actingAs($customer, 'appuser')
            ->get('/pay?order='.$order->id)
            ->assertStatus(502)
            ->assertSee('Payment could not be started')
            ->assertSee('/pay?order='.$order->id, false)
            ->assertDontSee('Paystack is unreachable.', false);

        $order->refresh();
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
    }

    #[Test]
    public function a_signed_webhook_completes_the_order_and_ignores_retries()
    {
        $order = $this->pendingPaystackOrder('ODG-WEBHOOK');
        $payload = $this->paystackChargePayload($order);

        $this->postPaystackWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true, 'completed' => true]);

        $order->refresh();
        $this->assertSame(1, (int) $order->payment_status);
        $this->assertSame('Complete', $order->order_status);

        $this->postPaystackWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true, 'duplicate' => true]);
    }

    #[Test]
    public function an_invalid_webhook_signature_is_rejected_without_changing_the_order()
    {
        $order = $this->pendingPaystackOrder('ODG-WEBHOOK-INVALID');
        $payload = $this->paystackChargePayload($order);

        $this->json('POST', '/paystack/webhook', $payload, [
            'x-paystack-signature' => str_repeat('0', 128),
        ])->assertUnauthorized();

        $order->refresh();
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
    }

    #[Test]
    public function a_timestamped_paystack_signature_completes_the_order()
    {
        config()->set('paystack.webhookSecrets', ['whsec_test_secret']);

        $order = $this->pendingPaystackOrder('ODG-WEBHOOK-SIGNED');
        $payload = $this->paystackChargePayload($order);
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha512', $timestamp.'.'.$json, 'whsec_test_secret');

        $this->json('POST', '/paystack/webhook', $payload, [
            'Paystack-Signature' => "t={$timestamp},v1=whk_test:{$signature}",
        ])->assertOk()->assertJson(['received' => true, 'completed' => true]);

        $order->refresh();
        $this->assertSame(1, (int) $order->payment_status);
        $this->assertSame('Complete', $order->order_status);
    }

    #[Test]
    public function an_expired_timestamped_webhook_signature_is_rejected()
    {
        config()->set('paystack.webhookSecrets', ['whsec_test_secret']);

        $order = $this->pendingPaystackOrder('ODG-WEBHOOK-EXPIRED');
        $payload = $this->paystackChargePayload($order);
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = time() - 601;
        $signature = hash_hmac('sha512', $timestamp.'.'.$json, 'whsec_test_secret');

        $this->json('POST', '/paystack/webhook', $payload, [
            'Paystack-Signature' => "t={$timestamp},v1=whk_test:{$signature}",
        ])->assertUnauthorized();

        $order->refresh();
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
    }

    #[Test]
    public function a_webhook_with_the_wrong_amount_is_acknowledged_but_does_not_complete_the_order()
    {
        $order = $this->pendingPaystackOrder('ODG-WEBHOOK-AMOUNT');
        $payload = $this->paystackChargePayload($order, ['amount' => 100]);

        $this->postPaystackWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true, 'ignored' => true]);

        $order->refresh();
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
    }

    #[Test]
    public function an_unrelated_webhook_event_is_acknowledged_without_changing_the_order()
    {
        $order = $this->pendingPaystackOrder('ODG-WEBHOOK-PING');
        $payload = $this->paystackChargePayload($order);
        $payload['event'] = 'ping';

        $this->postPaystackWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true, 'ignored' => true]);

        $order->refresh();
        $this->assertSame(0, (int) $order->payment_status);
        $this->assertSame('Pending', $order->order_status);
    }

    private function pendingPaystackOrder(string $reference, int $amount = 2000): Order
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => $amount]);
        $customer = $this->customer();

        return $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'PAYSTACK',
            'payment' => $amount,
            'payment_token' => $reference,
            'payment_status' => 0,
            'order_status' => 'Pending',
        ]);
    }

    private function paystackChargePayload(Order $order, array $overrides = []): array
    {
        return [
            'event' => 'charge.success',
            'data' => array_merge([
                'reference' => $order->payment_token,
                'status' => 'success',
                'amount' => (int) round((float) $order->payment * 100),
                'currency' => 'NGN',
                'metadata' => ['order_id' => $order->id],
            ], $overrides),
        ];
    }

    private function postPaystackWebhook(array $payload, string $secret = 'sk_test_webhook'): \Illuminate\Testing\TestResponse
    {
        config()->set('paystack.secretKey', $secret);

        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->json('POST', '/paystack/webhook', $payload, [
            'x-paystack-signature' => hash_hmac('sha512', $json, $secret),
        ]);
    }
}
