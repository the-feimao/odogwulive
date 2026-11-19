<?php

namespace Tests\Feature;

use App\Models\Category;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventPagesTest extends TestCase
{
    #[Test]
    public function the_landing_page_and_event_listing_render()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Upcoming Test Carnival']);

        $this->get('/')
            ->assertOk()
            ->assertSee(config('brand.name'), false)
            ->assertSee('Upcoming Test Carnival', false);
        $this->get('/index')->assertOk();

        $this->get('/all-events')
            ->assertOk()
            ->assertSee('Upcoming Test Carnival', false);
    }

    #[Test]
    public function the_event_guide_page_lists_published_events()
    {
        $organizer = $this->organizer();
        $this->event($organizer, ['name' => 'Guide Test Carnival']);

        $this->get('/index')
            ->assertOk()
            ->assertSee('Guide Test Carnival', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="type"', false);
    }

    #[Test]
    public function the_all_events_page_preserves_the_existing_query_filters()
    {
        $organizer = $this->organizer();
        $music = Category::create(['name' => 'Music Events', 'image' => 'default.png', 'status' => 1]);
        $arts = Category::create(['name' => 'Arts Events', 'image' => 'default.png', 'status' => 1]);

        $this->event($organizer, [
            'name' => 'Filtered Music Festival',
            'category_id' => $music->id,
            'type' => 'offline',
        ]);
        $this->event($organizer, [
            'name' => 'Filtered Online Summit',
            'category_id' => $arts->id,
            'type' => 'online',
        ]);

        $this->get('/all-events?category='.$music->id)
            ->assertOk()
            ->assertSee('Filtered Music Festival', false)
            ->assertSee('Music Events', false)
            ->assertDontSee('Filtered Online Summit', false)
            ->assertSee('name="duration"', false)
            ->assertSee('name="date"', false);

        $this->get('/all-events?type=online')
            ->assertOk()
            ->assertSee('Filtered Online Summit', false)
            ->assertDontSee('Filtered Music Festival', false);
    }

    #[Test]
    public function the_category_and_type_listing_routes_render()
    {
        $organizer = $this->organizer();
        $music = Category::create(['name' => 'Live Music', 'image' => 'default.png', 'status' => 1]);

        $this->event($organizer, [
            'name' => 'Category Route Concert',
            'category_id' => $music->id,
            'type' => 'offline',
        ]);
        $this->event($organizer, [
            'name' => 'Type Route Webinar',
            'type' => 'online',
        ]);

        $this->get('/events-category/'.$music->id.'/live-music')
            ->assertOk()
            ->assertSee('Category Route Concert', false)
            ->assertDontSee('Type Route Webinar', false);

        $this->get('/event-type/online')
            ->assertOk()
            ->assertSee('Type Route Webinar', false)
            ->assertDontSee('Category Route Concert', false);

        $this->get('/all-category')
            ->assertOk()
            ->assertSee('Live Music', false);
    }

    #[Test]
    public function the_home_page_has_a_graceful_empty_state()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('No events on sale yet');
    }

    #[Test]
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

    #[Test]
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
