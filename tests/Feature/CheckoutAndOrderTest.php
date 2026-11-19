<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderChild;
use App\Models\OrderTax;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CheckoutAndOrderTest extends TestCase
{
    /** @test */
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
            ->assertSee('Checkout VAT', false);
    }

    /** @test */
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
        $this->assertSame(2000, (int) $order->payment);
        $this->assertSame(200, (int) $order->tax);
        $this->assertSame(1, (int) $order->org_commission);
        $this->assertSame('Pending', $order->order_status);
        $this->assertSame(0, (int) $order->payment_status);

        $this->assertSame(2, OrderChild::where('order_id', $order->id)->count());
        $this->assertSame(2, OrderTax::where('order_id', $order->id)->count());
        $this->assertSame(1, (int) Coupon::find($coupon->id)->use_count);
    }

    /** @test */
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

    /** @test */
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
}
