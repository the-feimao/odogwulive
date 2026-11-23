<?php

namespace Tests\Feature;

use App\Mail\TicketBookOrg;
use App\Models\Order;
use App\Models\Setting;
use App\Support\PublicPaymentLink;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KnownIssuesFixTest extends TestCase
{
    #[Test]
    public function the_bare_event_tickets_url_redirects_to_the_event_listing()
    {
        $this->get('/event-tickets')->assertRedirect('/all-events');

        $this->assertSame(url('/event-tickets'), route('frontend.tickets'));
    }

    #[Test]
    public function there_is_only_one_post_change_password_route()
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())->filter(function ($route) {
            return in_array('POST', $route->methods(), true) && $route->uri() === 'change-password';
        });

        $this->assertCount(1, $routes);
        $this->assertStringContainsString(
            'AccountPasswordController@change',
            $routes->first()->getActionName()
        );
    }

    #[Test]
    public function an_attendee_can_change_a_password_through_the_unified_route()
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')->post('/change-password', [
            'old_password' => 'secret123',
            'password' => 'attendee-new-secret',
            'password_confirmation' => 'attendee-new-secret',
        ])->assertRedirect('user/profile');

        $this->assertTrue(Hash::check('attendee-new-secret', $customer->fresh()->password));
    }

    #[Test]
    public function a_staff_user_can_change_a_password_through_the_unified_route()
    {
        $organizer = $this->organizer();

        $this->actingAs($organizer)->post('/change-password', [
            'current_password' => 'secret123',
            'password' => 'staff-new-secret',
            'confirm_password' => 'staff-new-secret',
        ])->assertRedirect('profile');

        $this->assertTrue(Hash::check('staff-new-secret', $organizer->fresh()->password));
    }

    #[Test]
    public function web_checkout_sends_the_organizer_booking_email_to_the_organizer()
    {
        Mail::fake();
        Setting::find(1)->update(['mail_notification' => 1]);

        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')->post('/createOrder', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'payment' => 1000,
            'tax' => 0,
            'payment_type' => 'LOCAL',
            'coupon_id' => null,
            'coupon_discount' => 0,
        ])->assertRedirect('my-tickets');

        Mail::assertSent(TicketBookOrg::class, function ($mail) use ($organizer, $customer) {
            return $mail->hasTo($organizer->email) && !$mail->hasTo($customer->email);
        });
    }

    #[Test]
    public function api_checkout_sends_the_organizer_booking_email_to_the_organizer()
    {
        Mail::fake();
        Setting::find(1)->update(['mail_notification' => 1]);

        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();

        Passport::actingAs($customer, ['*'], 'userApi');

        $this->postJson('/api/user/create-order', [
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'payment' => 1000,
            'tax' => 0,
            'payment_type' => 'LOCAL',
            'coupon_discount' => 0,
        ])->assertOk()->assertJsonPath('success', true);

        Mail::assertSent(TicketBookOrg::class, function ($mail) use ($organizer, $customer) {
            return $mail->hasTo($organizer->email) && !$mail->hasTo($customer->email);
        });
    }

    #[Test]
    public function the_api_flutterwave_redirect_points_to_a_public_payment_link()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();

        Passport::actingAs($customer, ['*'], 'userApi');

        $response = $this->postJson('/api/user/create-order', [
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'payment' => 1000,
            'tax' => 0,
            'payment_type' => 'FLUTTERWAVE',
            'coupon_discount' => 0,
        ])->assertOk()->assertJsonPath('success', true);

        $order = Order::latest('id')->first();
        $redirectUrl = $response->json('data.redirect_url');

        $this->assertSame(PublicPaymentLink::url($order), $redirectUrl);
        $this->assertStringContainsString('/payment-link/', $redirectUrl);
        $this->assertStringNotContainsString('create-payment', $redirectUrl);
    }

    #[Test]
    public function the_public_payment_link_can_only_be_used_for_its_own_pending_flutterwave_order()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'FLUTTERWAVE',
            'payment_status' => 0,
            'payment' => 1000,
        ]);

        $token = PublicPaymentLink::token($order);

        $this->get(PublicPaymentLink::url($order))
            ->assertOk()
            ->assertSee('https://checkout.flutterwave.com/v3/hosted/pay', false)
            ->assertSee('name="redirect_url"', false)
            ->assertSee($order->order_id, false);

        $this->get('/payment-link/'.$order->id.'/invalid-token')->assertForbidden();

        $otherOrder = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'FLUTTERWAVE',
            'payment_status' => 0,
        ]);
        $this->get('/payment-link/'.$otherOrder->id.'/'.$token)->assertForbidden();
    }

    #[Test]
    public function a_public_payment_link_expires_and_its_callback_only_records_a_reference()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_type' => 'FLUTTERWAVE',
            'payment_status' => 0,
            'payment' => 1000,
        ]);

        $expired = Order::findOrFail($order->id);
        $expired->forceFill(['created_at' => now()->subDays(2)])->save();
        $this->get('/payment-link/'.$expired->id.'/'.PublicPaymentLink::token($expired))
            ->assertForbidden();

        Order::findOrFail($order->id)->forceFill(['created_at' => now()])->save();
        $freshOrder = $order->fresh();
        $callbackUrl = route('frontend.publicPayment.callback', [
            'order' => $freshOrder->id,
            'token' => PublicPaymentLink::token($freshOrder),
        ]);

        $this->get($callbackUrl.'?transaction_id=FLW-TEST-123&status=successful')
            ->assertOk()
            ->assertSee('Payment reference received', false);

        $this->assertSame('FLW-TEST-123', $order->fresh()->payment_token);
        $this->assertSame(0, (int) $order->fresh()->payment_status);
    }

    #[Test]
    public function an_attendee_can_log_out_and_the_header_exposes_the_logout_form()
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')
            ->get('/user/profile')
            ->assertOk()
            ->assertSee('action="'.url('/user/logout').'"', false);

        $this->post('/user/logout')->assertRedirect('/user/login');
        $this->assertGuest('appuser');
    }

    #[Test]
    public function an_attendee_can_print_only_their_own_invoice()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'order_status' => 'Complete',
            'payment_status' => 1,
        ]);

        $url = '/my-tickets/'.$order->id.'/invoice-print';

        $this->actingAs($customer, 'appuser')->get($url)
            ->assertOk()
            ->assertSee($order->order_id, false);

        $this->actingAs($this->customer(), 'appuser')->get($url)->assertForbidden();
    }

    #[Test]
    public function the_web_checkout_exposes_an_active_coupon_field_and_lookup_endpoint()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $coupon = $this->coupon($organizer, $event, ['coupon_code' => 'WEBOFFER']);
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')
            ->get('/checkout/'.$ticket->id)
            ->assertOk()
            ->assertSee('name="coupon_code"', false)
            ->assertSee('id="apply-coupon"', false)
            ->assertSee('data-coupon-endpoint="'.url('check-coupon').'"', false)
            ->assertDontSee('Coupon codes are applied from the mobile app.', false);

        $this->postJson('/check-coupon', [
            'coupon_code' => 'WEBOFFER',
            'ticket_id' => $ticket->id,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $coupon->id)
            ->assertJsonPath('data.discount', 10);

        $this->postJson('/check-coupon', [
            'coupon_code' => 'DOES-NOT-EXIST',
            'ticket_id' => $ticket->id,
        ])->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    #[Test]
    public function the_event_listing_duration_filter_is_applied_on_the_server()
    {
        $organizer = $this->organizer();
        $timezone = Setting::find(1)->timezone ?: config('app.timezone');
        $today = Carbon::now($timezone)->setTime(12, 0);
        $tomorrow = Carbon::now($timezone)->addDay()->setTime(12, 0);
        $thisWeek = Carbon::now($timezone)->addDays(3)->setTime(12, 0);
        $later = Carbon::now($timezone)->addDays(14)->setTime(12, 0);
        $end = Carbon::now($timezone)->addDays(30);

        $this->event($organizer, [
            'name' => 'Today Duration Event',
            'start_time' => $today,
            'end_time' => $end,
        ]);
        $this->event($organizer, [
            'name' => 'Tomorrow Duration Event',
            'start_time' => $tomorrow,
            'end_time' => $end,
        ]);
        $this->event($organizer, [
            'name' => 'This Week Duration Event',
            'start_time' => $thisWeek,
            'end_time' => $end,
        ]);
        $this->event($organizer, [
            'name' => 'Later Duration Event',
            'start_time' => $later,
            'end_time' => $end,
        ]);

        $this->get('/all-events?duration=Today')
            ->assertOk()
            ->assertSee('Today Duration Event', false)
            ->assertDontSee('Tomorrow Duration Event', false)
            ->assertDontSee('This Week Duration Event', false)
            ->assertDontSee('Later Duration Event', false);

        $this->get('/all-events?duration=Tomorrow')
            ->assertOk()
            ->assertSee('Tomorrow Duration Event', false)
            ->assertDontSee('Today Duration Event', false)
            ->assertDontSee('Later Duration Event', false);

        $this->get('/all-events?duration=ThisWeek')
            ->assertOk()
            ->assertSee('Today Duration Event', false)
            ->assertSee('Tomorrow Duration Event', false)
            ->assertSee('This Week Duration Event', false)
            ->assertDontSee('Later Duration Event', false);

        $this->get('/all-events?duration=date&date='.$tomorrow->toDateString())
            ->assertOk()
            ->assertSee('Tomorrow Duration Event', false)
            ->assertDontSee('Today Duration Event', false)
            ->assertDontSee('Later Duration Event', false);
    }
}
