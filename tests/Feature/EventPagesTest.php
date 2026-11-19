<?php

namespace Tests\Feature;

use Tests\TestCase;

class EventPagesTest extends TestCase
{
    /** @test */
    public function the_landing_page_and_event_listing_render()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Upcoming Test Carnival']);

        $this->get('/')->assertOk();
        $this->get('/index')->assertOk();

        $this->get('/all-events')
            ->assertOk()
            ->assertSee('Upcoming Test Carnival', false);
    }

    /** @test */
    public function the_event_detail_page_renders_the_event()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, [
            'name' => 'Detailed Test Event',
            'description' => 'Detailed test event description.',
        ]);
        // The current detail view hides a single paid ticket; add two so the
        // ticket list is actually rendered (existing behaviour, not changed).
        $this->ticket($event, ['name' => 'Early Bird', 'ticket_number' => 'EARLY-BIRD-TEST']);
        $this->ticket($event, ['name' => 'Regular', 'ticket_number' => 'REGULAR-TEST']);

        $this->get('/event/'.$event->id.'/detailed-test-event')
            ->assertOk()
            ->assertSee('Detailed Test Event', false)
            ->assertSee('EARLY-BIRD-TEST', false);
    }

    /** @test */
    public function the_ticket_selection_page_renders_the_ticket()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $this->ticket($event, ['name' => 'VIP Access', 'ticket_number' => 'VIP-ACCESS-TEST']);

        $this->get('/eventdetail/'.$event->id.'/test-event')
            ->assertOk()
            ->assertSee('VIP-ACCESS-TEST', false);
    }
}
