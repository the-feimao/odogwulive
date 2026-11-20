<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ScannerApiSecurityTest extends TestCase
{
    #[Test]
    public function an_unassigned_scanner_cannot_access_event_routes_or_scan()
    {
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $otherScanner = $this->scannerUser($organizer);
        $event = $this->event($organizer, ['scanner_id' => $otherScanner->id]);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $child = $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'UNASSIGNED-SCAN']);

        Passport::actingAs($scanner, ['*'], 'api');

        $this->getJson('/api/scanner/event-detail/'.$event->id)->assertForbidden();
        $this->getJson('/api/scanner/event-users/'.$event->id)->assertForbidden();
        $this->getJson('/api/scanner/scan-ticket/UNASSIGNED-SCAN/'.$event->id)->assertForbidden();

        $this->assertSame(0, (int) $child->fresh()->status);
    }
}
