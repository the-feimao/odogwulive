<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderChild;
use App\Models\OrderTax;
use App\Models\PaymentSetting;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CheckoutAndOrderTest extends TestCase
{
    #[Test]
    public function the_checkout_page_shows_taxes_and_available_quantity()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['quantity' => 10]);
        $this->tax($organizer, ['name' => 'Checkout VAT', 'price' => 150]);

        $customer = $this->customer();
        $this->order($event, $ticket, $customer, $organizer, ['quantity' => 3]);

        $this->actingAs($customer, 'appuser')
            ->get('/checkout/'.$ticket->id)
            ->assertOk()
            ->assertSee('Checkout VAT', false)
            ->assertSee('id="checkout-form"', false)
            ->assertSee('action="'.url('createOrder').'"', false)
            ->assertSee('name="tpo"', false)
            ->assertSee('name="available"', false)
            ->assertSee('name="quantity"', false)
            ->assertSee('name="price"', false)
            ->assertSee('name="tax"', false)
            ->assertSee('name="payment"', false)
            ->assertSee('name="stripe_payment"', false)
            ->assertSee('name="currency_code"', false)
            ->assertSee('name="payment_token"', false)
            ->assertSee('name="ticket_id"', false)
            ->assertSee('name="coupon_id"', false)
            ->assertSee('name="coupon_discount"', false)
            ->assertSee('name="tax_data"', false)
            ->assertSee('name="payment_type"', false)
            ->assertSee('id="form_submit"', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false);
    }

    #[Test]
    public function the_checkout_page_preserves_gateway_field_names_and_sdk_loading()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1200]);

        PaymentSetting::find(1)->update([
            'paypal' => 1,
            'razor' => 1,
            'stripe' => 1,
            'flutterwave' => 1,
            'cod' => 0,
            'paypalClientId' => 'test-client-id',
            'razorPublishKey' => 'test-razor-key',
            'stripePublicKey' => 'test-stripe-key',
        ]);

        $this->actingAs($this->customer(), 'appuser')
            ->get('/checkout/'.$ticket->id)
            ->assertOk()
            ->assertSee('value="PAYPAL"', false)
            ->assertSee('value="RAZOR"', false)
            ->assertSee('value="STRIPE"', false)
            ->assertSee('value="FLUTTERWAVE"', false)
            ->assertSee('data-namespace="paypal_sdk"', false)
            ->assertSee('checkout.razorpay.com/v1/checkout.js', false)
            ->assertSee('id="paypal-button-container"', false)
            ->assertSee('id="stripe-form"', false)
            ->assertDontSee('value="LOCAL"', false);
    }

    #[Test]
    public function a_free_ticket_checkout_uses_the_original_free_payment_value()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['type' => 'free', 'price' => 0]);

        $this->actingAs($this->customer(), 'appuser')
            ->get('/checkout/'.$ticket->id)
            ->assertOk()
            ->assertSee('value="FREE"', false)
            ->assertSee('name="payment" id="payment" value="0"', false)
            ->assertSee('name="stripe_payment" id="stripe_payment" value="0"', false)
            ->assertSee('name="tax" id="tax_total" value="0"', false)
            ->assertDontSee('name="tax_data"', false);
    }

    #[Test]
    public function a_local_payment_order_is_created_with_children_taxes_and_commission()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000]);
        $tax = $this->tax($organizer, ['price' => 100]);
        $coupon = $this->coupon($organizer, $event);
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')->post('/createOrder', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'price' => 1000,
            'payment' => 2000,
            'tax' => 200,
            'payment_type' => 'LOCAL',
            'coupon_id' => $coupon->id,
            'coupon_discount' => 0,
            'tax_data' => json_encode([['id' => $tax->id, 'price' => 100], ['id' => $tax->id, 'price' => 100]]),
        ])->assertRedirect('my-tickets');

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, (int) $order->customer_id);
        $this->assertSame($organizer->id, (int) $order->organization_id);
        $this->assertSame($event->id, (int) $order->event_id);
        $this->assertSame(2, (int) $order->quantity);
        $this->assertSame(1900, (int) $order->payment);
        $this->assertSame(100, (int) $order->tax);
        $this->assertSame(200, (int) $order->coupon_discount);
        $this->assertSame(1, (int) $order->org_commission);
        $this->assertSame('Pending', $order->order_status);
        $this->assertSame(0, (int) $order->payment_status);

        $this->assertSame(2, OrderChild::where('order_id', $order->id)->count());
        $this->assertSame(1, OrderTax::where('order_id', $order->id)->count());
        $this->assertSame(1, (int) Coupon::find($coupon->id)->use_count);
    }

    #[Test]
    public function checking_a_valid_and_an_invalid_coupon_via_the_api()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $coupon = $this->coupon($organizer, $event, ['coupon_code' => 'VALIDCODE']);
        $customer = $this->customer();

        Passport::actingAs($customer, ['*'], 'userApi');

        $this->postJson('/api/user/check-code', [
            'coupon_code' => 'VALIDCODE',
            'event_id' => $event->id,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $coupon->id);

        $this->postJson('/api/user/check-code', [
            'coupon_code' => 'NOPE',
            'event_id' => $event->id,
        ])->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    #[Test]
    public function the_api_returns_taxes_for_an_event_organizer()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $this->tax($organizer, ['name' => 'Event Tax']);
        $this->tax($organizer, ['name' => 'Hidden Tax', 'allow_all_bill' => 0]);

        Passport::actingAs($this->customer(), ['*'], 'userApi');

        $this->getJson('/api/user/order-tax/'.$event->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Event Tax');
    }

    #[Test]
    public function client_supplied_totals_and_tax_rows_are_ignored()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000]);
        $tax = $this->tax($organizer, ['price' => 100]);
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')->post('/createOrder', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'price' => 1000,
            'payment' => 1,
            'tax' => 9999,
            'payment_type' => 'LOCAL',
            'coupon_id' => null,
            'coupon_discount' => 9999,
            'tax_data' => json_encode([
                ['id' => $tax->id, 'price' => 9999],
                ['id' => $tax->id, 'price' => 9999],
            ]),
        ])->assertRedirect('my-tickets');

        $order = Order::latest('id')->first();
        $this->assertSame(2100, (int) $order->payment);
        $this->assertSame(100, (int) $order->tax);
        $this->assertSame(0, (int) $order->coupon_discount);
        $this->assertSame(1, OrderTax::where('order_id', $order->id)->count());
        $this->assertSame(100, (int) OrderTax::where('order_id', $order->id)->first()->price);
    }

    #[Test]
    public function quantity_above_the_ticket_limit_is_rejected()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000, 'ticket_per_order' => 2]);

        $this->actingAs($this->customer(), 'appuser')->post('/createOrder', [
            'ticket_id' => $ticket->id,
            'quantity' => 3,
            'payment' => 3000,
            'tax' => 0,
            'payment_type' => 'LOCAL',
            'coupon_discount' => 0,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(0, Order::where('ticket_id', $ticket->id)->count());
    }

    #[Test]
    public function the_api_order_uses_server_calculated_totals()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000]);
        $this->tax($organizer, ['price' => 100]);
        $customer = $this->customer();

        Passport::actingAs($customer, ['*'], 'userApi');

        $this->postJson('/api/user/create-order', [
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'payment' => 1,
            'tax' => 9999,
            'payment_type' => 'LOCAL',
            'coupon_discount' => 9999,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment', 2100)
            ->assertJsonPath('data.tax', 100)
            ->assertJsonPath('data.coupon_discount', 0);

        $order = Order::latest('id')->first();
        $this->assertSame(2100, (int) $order->payment);
        $this->assertSame(100, (int) $order->tax);
        $this->assertSame(1, OrderTax::where('order_id', $order->id)->count());
    }

    #[Test]
    public function a_coupon_from_another_event_is_rejected_without_incrementing_its_use_count()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event, ['price' => 1000]);

        $otherOrganizer = $this->organizer();
        $otherEvent = $this->event($otherOrganizer);
        $coupon = $this->coupon($otherOrganizer, $otherEvent);

        $this->actingAs($this->customer(), 'appuser')->post('/createOrder', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'payment' => 1000,
            'tax' => 0,
            'payment_type' => 'LOCAL',
            'coupon_id' => $coupon->id,
            'coupon_discount' => 1000,
        ])->assertSessionHasErrors('coupon_id');

        $this->assertSame(0, (int) $coupon->fresh()->use_count);
        $this->assertSame(0, Order::where('ticket_id', $ticket->id)->count());
    }
}
