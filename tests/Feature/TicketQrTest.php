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
}
