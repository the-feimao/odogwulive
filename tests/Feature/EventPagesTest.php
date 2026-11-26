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
            ->assertSee('Upcoming Test Carnival', false)
            ->assertSee('Event results', false);
    }

    #[Test]
    public function the_public_layout_uses_valid_language_code_and_lightweight_assets()
    {
        $html = $this->get('/')->assertOk()->getContent();
        $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);

        $this->assertStringContainsString('<html lang="en"', $html);
        $this->assertStringContainsString($manifest['resources/js/public.js']['file'], $html);
        $this->assertStringNotContainsString($manifest['resources/sass/app.scss']['file'], $html);
        $this->assertStringNotContainsString($manifest['resources/js/app.js']['file'], $html);
    }

    #[Test]
    public function the_all_events_page_keeps_the_alpine_expression_out_of_visible_markup()
    {
        $html = $this->get('/all-events')->assertOk()->getContent();

        $this->assertStringContainsString('<div x-data="eventListing"', $html);
        $this->assertStringContainsString("Alpine.data('eventListing'", $html);
        $this->assertStringNotContainsString("x-data='{", $html);
        $this->assertSame(0, preg_match('/<div[^>]*x-data=\'[^\']*this\.(?:apply|watch)/s', $html));
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
        $paid = $this->ticket($event, ['name' => 'Early Bird', 'ticket_number' => 'EARLY-BIRD-TEST']);
        $this->ticket($event, [
            'name' => 'Community Pass',
            'ticket_number' => 'FREE-PASS-TEST',
            'type' => 'free',
            'price' => 0,
        ]);

        $this->get('/event/'.$event->id.'/detailed-test-event')
            ->assertOk()
            ->assertSee('Detailed Test Event', false)
            ->assertSee('Detailed test event description.', false)
            ->assertSee('Test Organizer', false)
            ->assertSee('About this event')
            ->assertSee('Organized by')
            ->assertSee('Reviews')
            ->assertSee('Good to know')
            ->assertSee('View on OpenStreetMap')
            ->assertSee('EARLY-BIRD-TEST', false)
            ->assertSee('FREE-PASS-TEST', false)
            ->assertSee('user/login', false);
    }

    #[Test]
    public function online_events_expose_only_safe_join_links()
    {
        $organizer = $this->organizer();
        $safe = $this->event($organizer, [
            'name' => 'Online Link Summit',
            'type' => 'online',
            'event_link' => 'https://meet.example.com/live-session',
        ]);
        $unsafe = $this->event($organizer, [
            'name' => 'Unsafe Link Summit',
            'type' => 'online',
            'event_link' => 'javascript:alert(1)',
        ]);

        $this->get('/event/'.$safe->id.'/online-link-summit')
            ->assertOk()
            ->assertSee('Join online')
            ->assertSee('Open event link')
            ->assertSee('https://meet.example.com/live-session', false);

        $this->get('/event/'.$unsafe->id.'/unsafe-link-summit')
            ->assertOk()
            ->assertDontSee('javascript:alert(1)', false)
            ->assertSee('Joining details will be shared by the organizer.');
    }

    #[Test]
    public function signed_in_attendees_get_the_original_checkout_link()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Checkout Link Event']);
        $ticket = $this->ticket($event, ['name' => 'Early Bird', 'ticket_number' => 'CHECKOUT-LINK-TEST']);

        $this->actingAs($this->customer(), 'appuser')
            ->get('/event/'.$event->id.'/checkout-link-event')
            ->assertOk()
            ->assertSee('CHECKOUT-LINK-TEST', false)
            ->assertSee('checkout/'.$ticket->id, false);
    }

    #[Test]
    public function the_ticket_selection_page_renders_real_tickets_without_placeholder_pricing()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Ticket Selection Event']);
        $ticket = $this->ticket($event, ['name' => 'VIP Access', 'ticket_number' => 'VIP-ACCESS-TEST']);
        $soldOut = $this->ticket($event, [
            'name' => 'Sold Out Pass',
            'ticket_number' => 'SOLD-OUT-TEST',
            'quantity' => 0,
        ]);

        $this->actingAs($this->customer(), 'appuser')
            ->get('/eventdetail/'.$event->id.'/ticket-selection-event')
            ->assertOk()
            ->assertSee('Ticket Selection Event', false)
            ->assertSee('Event summary')
            ->assertSee('VIP-ACCESS-TEST', false)
            ->assertSee('SOLD-OUT-TEST', false)
            ->assertSee('checkout/'.$ticket->id, false)
            ->assertSee('Sold Out')
            ->assertDontSee('checkout/'.$soldOut->id, false)
            ->assertDontSee('Regular(1 Days)', false)
            ->assertDontSee('1.2M', false);
    }
}
