<?php

namespace Tests\Feature;

use App\Models\OrderTax;
use App\Models\Setting;
use App\Support\RequestData;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Phase6PerformanceTest extends TestCase
{
    private function activateLicense(): void
    {
        Setting::find(1)->update(['license_status' => 1]);
    }

    private function queriesMatching(callable $callback, string $pattern): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $sql = collect(DB::getQueryLog())->pluck('query')->implode("\n");
        DB::disableQueryLog();

        return preg_match_all($pattern, $sql);
    }

    #[Test]
    public function request_scoped_data_is_reused_within_a_request(): void
    {
        $this->assertSame(app(RequestData::class), app(RequestData::class));
    }

    #[Test]
    public function event_listing_and_profile_aggregates_do_not_grow_with_event_count(): void
    {
        $organizer = $this->organizer();
        $category = $this->category();
        $first = $this->event($organizer, [
            'name' => 'Batched Event One',
            'category_id' => $category->id,
        ]);
        $firstTicket = $this->ticket($first, ['quantity' => 50]);
        $this->order($first, $firstTicket, $this->customer(), $organizer, ['quantity' => 3]);

        $pattern = '/SUM\s*\(\s*"?quantity"?\s*\).*?GROUP BY/iu';
        $single = $this->queriesMatching(fn () => $this->get('/all-events'), $pattern);
        $this->get('/all-events')->assertOk()->assertSee('Batched Event One', false);

        for ($i = 2; $i <= 8; $i++) {
            $event = $this->event($organizer, [
                'name' => 'Batched Event '.$i,
                'category_id' => $category->id,
            ]);
            $eventTicket = $this->ticket($event, ['quantity' => 50]);
            $this->order($event, $eventTicket, $this->customer(), $organizer, ['quantity' => 3]);
        }

        $many = $this->queriesMatching(fn () => $this->get('/all-events'), $pattern);
        $this->assertSame($single, $many);
        $this->assertLessThanOrEqual(2, $many);

        $favoriteIds = \App\Models\Event::where('name', 'like', 'Batched Event %')
            ->pluck('id')
            ->implode(',');
        $customer = $this->customer(['favorite' => $favoriteIds]);

        $this->actingAs($customer, 'appuser')
            ->get('/user/profile')
            ->assertOk()
            ->assertSee('Batched Event One', false)
            ->assertSee('Batched Event 8', false);
    }

    #[Test]
    public function ticket_availability_uses_grouped_order_sums(): void
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Availability Test Event']);
        $tickets = collect();

        for ($i = 0; $i < 6; $i++) {
            $tickets->push($this->ticket($event, [
                'name' => 'Paid Ticket '.($i + 1),
                'type' => 'paid',
                'quantity' => 50,
            ]));
        }
        for ($i = 0; $i < 3; $i++) {
            $tickets->push($this->ticket($event, [
                'name' => 'Free Ticket '.($i + 1),
                'type' => 'free',
                'price' => 0,
                'quantity' => 50,
            ]));
        }

        $customer = $this->customer();
        $this->order($event, $tickets[0], $customer, $organizer, ['quantity' => 3]);
        $this->order($event, $tickets[6], $customer, $organizer, ['quantity' => 5]);

        $pattern = '/SUM\s*\(\s*"?quantity"?\s*\).*?GROUP BY/iu';
        $detail = $this->queriesMatching(
            fn () => $this->get('/event/'.$event->id.'/availability-test-event'),
            $pattern
        );
        $selection = $this->queriesMatching(
            fn () => $this->actingAs($customer, 'appuser')->get('/eventdetail/'.$event->id.'/availability-test-event'),
            $pattern
        );

        $this->assertLessThanOrEqual(1, $detail);
        $this->assertLessThanOrEqual(1, $selection);
        $this->get('/event/'.$event->id.'/availability-test-event')
            ->assertOk()
            ->assertSee('Paid Ticket 1', false)
            ->assertSee('Free Ticket 3', false);
    }

    #[Test]
    public function dashboard_aggregates_do_not_grow_with_event_count(): void
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $category = $this->category();
        $this->activateLicense();

        $makeEvent = function (string $name) use ($organizer, $category) {
            $event = $this->event($organizer, [
                'name' => $name,
                'category_id' => $category->id,
                'start_time' => now(),
            ]);
            $ticket = $this->ticket($event, ['quantity' => 50]);
            $this->order($event, $ticket, $this->customer(), $organizer, ['quantity' => 2]);

            return $event;
        };

        $makeEvent('Dashboard Batched 1');
        $pattern = '/SUM\s*\(\s*"?quantity"?\s*\).*?GROUP BY/iu';
        $single = $this->queriesMatching(fn () => $this->actingAs($admin)->get('/admin/home'), $pattern);

        for ($i = 2; $i <= 8; $i++) {
            $makeEvent('Dashboard Batched '.$i);
        }

        $many = $this->queriesMatching(fn () => $this->actingAs($admin)->get('/admin/home'), $pattern);
        $this->assertSame($single, $many);
        $this->assertLessThanOrEqual(2, $many);

        $this->actingAs($admin)->get('/admin/home')
            ->assertOk()
            ->assertSee('Dashboard Batched 8', false)
            ->assertSee('id="eventDate"', false);
    }

    #[Test]
    public function admin_report_aggregates_do_not_grow_with_row_count(): void
    {
        $this->activateLicense();
        $admin = $this->admin();
        $customer = $this->customer(['name' => 'Batched Report Customer']);
        $pattern = '/SUM\s*\(\s*"?quantity"?\s*\).*?GROUP BY/iu';

        $single = $this->queriesMatching(fn () => $this->actingAs($admin)->get('/admin-report/customer'), $pattern);

        for ($i = 0; $i < 7; $i++) {
            $this->customer(['name' => 'Extra Report Customer '.$i]);
        }

        $many = $this->queriesMatching(fn () => $this->actingAs($admin)->get('/admin-report/customer'), $pattern);
        $this->assertSame($single, $many);
        $this->assertLessThanOrEqual(1, $many);

        $this->actingAs($admin)->get('/admin-report/customer')
            ->assertOk()
            ->assertSee('Batched Report Customer', false)
            ->assertSee('id="report_table"', false);
    }

    #[Test]
    public function category_counts_use_one_grouped_query(): void
    {
        $this->activateLicense();
        $admin = $this->admin();
        $this->category();
        $pattern = '/COUNT\s*\(\s*\*\s*\).*?FROM events.*?GROUP BY/iu';

        $single = $this->queriesMatching(fn () => $this->actingAs($admin)->get('/category'), $pattern);

        for ($i = 0; $i < 5; $i++) {
            $this->category();
        }

        $many = $this->queriesMatching(fn () => $this->actingAs($admin)->get('/category'), $pattern);
        $this->assertSame($single, $many);
        $this->assertLessThanOrEqual(1, $many);
    }

    #[Test]
    public function invoice_tax_names_use_the_loaded_relation(): void
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $order = $this->order($event, $ticket, $this->customer(), $organizer);

        foreach (['Alpha Tax', 'Beta Tax', 'Gamma Tax'] as $name) {
            $tax = $this->tax($organizer, ['name' => $name]);
            OrderTax::create([
                'order_id' => $order->id,
                'tax_id' => $tax->id,
                'price' => 100,
            ]);
        }

        $queries = $this->queriesMatching(
            fn () => $this->actingAs($admin)->get('/order-invoice/'.$order->id),
            '/FROM taxes/iu'
        );

        $this->assertLessThanOrEqual(1, $queries);
        $this->actingAs($admin)->get('/order-invoice/'.$order->id)
            ->assertOk()
            ->assertSee('Alpha Tax', false)
            ->assertSee('Beta Tax', false)
            ->assertSee('Gamma Tax', false);
    }

    #[Test]
    public function customer_detail_eager_loads_order_events(): void
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $customer = $this->customer(['name' => 'Eager Load Customer']);

        for ($i = 0; $i < 6; $i++) {
            $event = $this->event($organizer, ['name' => 'Eager Load Event '.$i]);
            $ticket = $this->ticket($event);
            $this->order($event, $ticket, $customer, $organizer, ['order_status' => 'Complete']);
        }

        $queries = $this->queriesMatching(
            fn () => $this->actingAs($admin)->get('/view-user/'.$customer->id),
            '/FROM events/iu'
        );

        $this->assertLessThanOrEqual(1, $queries);
        $this->actingAs($admin)->get('/view-user/'.$customer->id)
            ->assertOk()
            ->assertSee('Eager Load Customer', false)
            ->assertSee('Eager Load Event 5', false);
    }
}
