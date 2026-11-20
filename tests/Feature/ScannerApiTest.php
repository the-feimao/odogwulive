<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\OrderChild;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ScannerApiTest extends TestCase
{
    #[Test]
    public function a_scanner_can_log_in_and_receive_a_token()
    {
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $scanner->password = Hash::make('secret123');
        $scanner->save();

        $this->postJson('/api/scanner/login', [
            'email' => $scanner->email,
            'password' => 'secret123',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('msg', 'Login successfully')
            ->assertJsonStructure(['data' => ['token']]);
    }

    #[Test]
    public function a_non_scanner_cannot_log_in_to_the_scanner_api()
    {
        $organizer = $this->organizer(['password' => Hash::make('secret123')]);

        $this->postJson('/api/scanner/login', [
            'email' => $organizer->email,
            'password' => 'secret123',
        ])->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('msg', 'Only scanner can login.');
    }

    #[Test]
    public function assigned_events_are_listed_for_the_authenticated_scanner()
    {
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $assigned = $this->event($organizer, ['name' => 'Assigned Event', 'scanner_id' => $scanner->id]);
        $this->event($organizer, ['name' => 'Other Event']);

        Passport::actingAs($scanner, ['*'], 'api');

        $this->getJson('/api/scanner/events')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Assigned Event');
    }

    #[Test]
    public function scanning_a_valid_ticket_marks_it_used_and_completes_the_order()
    {
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $event = $this->event($organizer, ['scanner_id' => $scanner->id]);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $child = $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'SCAN-ME-1']);

        Passport::actingAs($scanner, ['*'], 'api');

        $this->getJson('/api/scanner/scan-ticket/SCAN-ME-1/'.$event->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('msg', 'Ticket scanned successfully.');

        $this->assertSame(1, (int) OrderChild::find($child->id)->status);
        $this->assertSame('Complete', $order->fresh()->order_status);
        $this->assertSame(1, (int) $order->fresh()->payment_status);

        $this->getJson('/api/scanner/scan-ticket/SCAN-ME-1/'.$event->id)
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('msg', 'Ticket is already scanned. ');
    }

    #[Test]
    public function a_ticket_from_another_event_cannot_be_scanned()
    {
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $event = $this->event($organizer, ['scanner_id' => $scanner->id]);
        $otherEvent = $this->event($organizer, ['scanner_id' => $scanner->id]);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'WRONG-EVENT']);

        Passport::actingAs($scanner, ['*'], 'api');

        $this->getJson('/api/scanner/scan-ticket/WRONG-EVENT/'.$otherEvent->id)
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('msg', 'Ticket can not be found.');
    }
}
