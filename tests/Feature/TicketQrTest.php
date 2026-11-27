<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Setting;
use Tests\TestCase;

class TicketQrTest extends TestCase
{
    #[Test]
    public function an_admin_can_print_a_ticket_with_a_qr_code()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $child = $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-TEST-123']);

        // The admin layout intentionally hides content when the license is
        // inactive. Enable it for this test only (transaction rolls back).
        Setting::find(1)->update(['license_status' => 1]);

        $this->actingAs($this->admin())
            ->get('/get-code/'.$child->id)
            ->assertOk()
            ->assertSee('QR-TEST-123', false)
            ->assertSee('<img', false);
    }

    #[Test]
    public function an_organization_cannot_print_another_organizations_ticket()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $child = $this->orderChild($order, $ticket, $customer);

        $otherOrganizer = $this->organizer();

        $this->actingAs($otherOrganizer)
            ->get('/get-code/'.$child->id)
            ->assertForbidden();
    }

    #[Test]
    public function a_customer_can_see_qr_codes_for_paid_tickets()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_status' => 1,
            'payment_type' => 'STRIPE',
        ]);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-PAID-1']);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-PAID-2']);

        $this->actingAs($customer, 'appuser')
            ->get('/my-tickets/'.$order->id.'/qr')
            ->assertOk()
            ->assertSee('QR-PAID-1', false)
            ->assertSee('QR-PAID-2', false)
            ->assertSee('QR code for QR-PAID-1', false)
            ->assertSee('ds-qr-pass', false)
            ->assertSee('<svg', false)
            ->assertSee('width="240"', false);
    }

    #[Test]
    public function unpaid_orders_do_not_expose_scannable_qr_codes()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_status' => 0,
            'payment_type' => 'LOCAL',
        ]);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-UNPAID-1']);

        $this->actingAs($customer, 'appuser')
            ->get('/my-tickets/'.$order->id.'/qr')
            ->assertOk()
            ->assertSee('Payment still pending', false)
            ->assertSee('Your QR codes will appear here as soon as this order is confirmed.', false)
            ->assertDontSee('QR code for QR-UNPAID-1', false)
            ->assertDontSee('ds-qr-pass', false);
    }

    #[Test]
    public function a_customer_cannot_read_another_customers_qr_codes()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, ['payment_status' => 1]);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-PRIVATE-1']);

        $this->actingAs($this->customer(), 'appuser')
            ->get('/my-tickets/'.$order->id.'/qr')
            ->assertForbidden();
    }

    #[Test]
    public function the_my_tickets_page_and_invoice_show_the_paid_tickets_qr_codes()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'QR Ticket Event']);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'payment_status' => 1,
            'payment_type' => 'PAYSTACK',
        ]);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-PAGE-1']);

        $this->actingAs($customer, 'appuser')
            ->get('/my-tickets')
            ->assertOk()
            ->assertSee('data-order-qr-content', false)
            ->assertSee('QR code for QR-PAGE-1', false)
            ->assertSee('Your QR tickets', false);

        $this->actingAs($customer, 'appuser')
            ->get('/my-tickets/'.$order->id.'/invoice-print')
            ->assertOk()
            ->assertSee('QR code for QR-PAGE-1', false);
    }
}
