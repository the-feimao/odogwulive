<?php

namespace Tests\Feature;

use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminDashboardPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function the_admin_dashboard_keeps_its_statistics_and_calendar_hooks()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Dashboard Test Carnival', 'start_time' => now()]);
        $ticket = $this->ticket($event, ['quantity' => 50]);
        $this->order($event, $ticket, $this->customer(), $organizer, ['quantity' => 2]);

        $this->actingAs($admin)->get('/admin/home')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Dashboard Test Carnival', false)
            ->assertSee('dark:tw-from-ink-900 dark:tw-via-ink-900', false)
            ->assertSee('View revenue report', false)
            ->assertSee('Total Orders')
            ->assertSee('id="eventDate"', false)
            ->assertSee('id="home_calender"', false)
            ->assertSee('onclick="getStatistics(1)"', false)
            ->assertSee('class="card-statistic-2', false)
            ->assertSee('data-order-bar-completed', false)
            ->assertSee('data-order-completion', false)
            ->assertSee('class="card-stats order-total">1</span>', false);
    }

    #[Test]
    public function the_organization_dashboard_keeps_its_statistics_and_calendar_hooks()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Organizer Test Carnival', 'start_time' => now()]);
        $ticket = $this->ticket($event, ['quantity' => 50]);
        $this->order($event, $ticket, $this->customer(), $organizer, ['quantity' => 2]);

        $this->actingAs($organizer)->get('/organization/home')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Organizer Test Carnival', false)
            ->assertSee('dark:tw-from-ink-900 dark:tw-via-ink-900', false)
            ->assertSee('View payouts', false)
            ->assertSee('Remaining Tickets')
            ->assertSee('2/50', false)
            ->assertSee('id="eventDate"', false)
            ->assertSee('id="home_calender"', false)
            ->assertSee('onclick="getStatistics(1)"', false)
            ->assertSee('class="card-statistic-2', false)
            ->assertSee('data-order-bar-completed', false);
    }

    #[Test]
    public function the_organizer_dashboard_excludes_deleted_tickets_and_flags_over_capacity()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, [
            'name' => 'Capacity Test Event',
            'people' => 20,
            'start_time' => now(),
        ]);

        // 20 + 20 + 10 active tickets = 50. The deleted 14-piece ticket and
        // the inactive 5-piece ticket must not be counted.
        $this->ticket($event, ['quantity' => 20]);
        $this->ticket($event, ['quantity' => 20]);
        $this->ticket($event, ['quantity' => 10]);
        $this->ticket($event, ['quantity' => 14, 'is_deleted' => 1]);
        $this->ticket($event, ['quantity' => 5, 'status' => 0]);

        $this->actingAs($organizer)->get('/organization/home')
            ->assertOk()
            ->assertSee('Capacity Test Event', false)
            ->assertSee('0/50', false)
            ->assertSee('50 Pcs left', false)
            ->assertDontSee('64 Pcs left', false)
            ->assertSee('Ticket quantity exceeds venue capacity')
            ->assertSee('dark:tw-border-warning-800 dark:tw-bg-warning-900/30 dark:tw-text-warning-100', false);
    }

    #[Test]
    public function an_organizer_cannot_open_the_admin_dashboard()
    {
        $this->actingAs($this->organizer())->get('/admin/home')->assertForbidden();
    }
}
