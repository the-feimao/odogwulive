<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTicketPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    private function userWithoutRole(): User
    {
        return User::create([
            'name' => 'No Ticket Access',
            'first_name' => 'No',
            'last_name' => 'Access',
            'email' => 'no-ticket-'.uniqid().'@example.test',
            'password' => bcrypt('secret123'),
            'status' => 1,
            'language' => 'English',
        ]);
    }

    private function ticketUrl(\App\Models\Event $event): string
    {
        return url($event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets');
    }

    #[Test]
    public function ticket_list_keeps_table_hooks_and_event_scope()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $admin = $this->admin();
        $event = $this->event($organizer, ['name' => 'Ticket Event']);
        $paid = $this->ticket($event, [
            'name' => 'Paid Admission',
            'ticket_number' => 'AB-1234',
            'type' => 'paid',
            'price' => 1000,
        ]);
        $free = $this->ticket($event, [
            'name' => 'Free Entry',
            'ticket_number' => 'CD-5678',
            'type' => 'free',
            'price' => 0,
        ]);

        $this->actingAs($admin)->get(url($event->id.'/Ticket-Event/tickets'))->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Tickets')
            ->assertSee('Ticket Event')
            ->assertSee('AB-1234')
            ->assertSee('Paid Admission')
            ->assertSee('Free Entry')
            ->assertSee('1000', false)
            ->assertSee('FREE')
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.url($event->id.'/ticket/create').'"', false)
            ->assertSee('href="'.url('ticket/edit/'.$paid->id).'"', false)
            ->assertSee("deleteData('deleteTickets','".$paid->id."');", false)
            ->assertSee("deleteData('deleteTickets','".$free->id."');", false);

        $otherOrganizer = $this->organizer();
        $this->actingAs($otherOrganizer)->get(url($event->id.'/Ticket-Event/tickets'))->assertForbidden();

        $this->actingAs($this->userWithoutRole())->get(url($event->id.'/Ticket-Event/tickets'))->assertForbidden();
    }

    #[Test]
    public function ticket_forms_keep_legacy_hooks_and_store_update_delete_flows()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $admin = $this->admin();
        $event = $this->event($organizer, ['name' => 'Form Ticket Event']);

        $this->actingAs($admin)->get(url($event->id.'/ticket/create'))->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('action="'.url('ticket/create').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('class="ticket-form', false)
            ->assertSee('name="event_id"', false)
            ->assertSee('value="'.$event->id.'"', false)
            ->assertSee('name="type"', false)
            ->assertSee('value="paid"', false)
            ->assertSee('value="free"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="quantity"', false)
            ->assertSee('name="price"', false)
            ->assertSee('id="price"', false)
            ->assertSee('name="ticket_per_order"', false)
            ->assertSee('id="ticket_per_order"', false)
            ->assertSee('name="start_time"', false)
            ->assertSee('id="start_time"', false)
            ->assertSee('name="end_time"', false)
            ->assertSee('id="end_time"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="status"', false)
            ->assertSee('select2');

        $this->post('/ticket/create', [
            'event_id' => $event->id,
            'type' => 'free',
            'name' => 'Community Ticket',
            'quantity' => 25,
            'ticket_per_order' => 5,
            'start_time' => now()->addDay()->format('Y-m-d h:i A'),
            'end_time' => now()->addDays(2)->format('Y-m-d h:i A'),
            'description' => 'A free community ticket.',
            'status' => 1,
        ])->assertRedirect($this->ticketUrl($event));

        $ticket = Ticket::where('name', 'Community Ticket')->firstOrFail();
        $this->assertSame('free', $ticket->type);
        $this->assertSame(0, (int) $ticket->price);
        $this->assertSame((int) $organizer->id, (int) $ticket->user_id);
        $this->assertNotEmpty($ticket->ticket_number);

        $this->get(url('ticket/edit/'.$ticket->id))->assertOk()
            ->assertSee('action="'.url('ticket/update/'.$ticket->id).'"', false)
            ->assertSee('class="ticket-form', false)
            ->assertSee('Community Ticket')
            ->assertSee('value="free"', false)
            ->assertSee('checked', false)
            ->assertSee('disabled', false)
            ->assertSee('id="start_time"', false)
            ->assertSee('id="end_time"', false);

        $this->post('/ticket/update/'.$ticket->id, [
            'event_id' => $event->id,
            'type' => 'paid',
            'name' => 'Updated Paid Ticket',
            'quantity' => 30,
            'price' => 1500,
            'ticket_per_order' => 4,
            'start_time' => now()->addDay()->format('Y-m-d h:i A'),
            'end_time' => now()->addDays(2)->format('Y-m-d h:i A'),
            'description' => 'An updated paid ticket.',
            'status' => 0,
        ])->assertRedirect($this->ticketUrl($event));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'name' => 'Updated Paid Ticket',
            'type' => 'paid',
            'price' => 1500,
            'status' => 0,
        ]);

        $this->delete('/deleteTickets/'.$ticket->id)->assertOk();
        $this->assertSame(1, (int) $ticket->fresh()->is_deleted);

        $this->actingAs($this->userWithoutRole())->get(url($event->id.'/ticket/create'))->assertForbidden();
        $this->actingAs($this->organizer())->get(url('ticket/edit/'.$ticket->id))->assertForbidden();
    }
}
