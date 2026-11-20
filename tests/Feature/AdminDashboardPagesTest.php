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
            ->assertSee('Total Orders')
            ->assertSee('id="eventDate"', false)
            ->assertSee('id="home_calender"', false)
            ->assertSee('onclick="getStatistics(1)"', false)
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
            ->assertSee('Remaining Tickets')
            ->assertSee('2/50', false)
            ->assertSee('id="eventDate"', false)
            ->assertSee('id="home_calender"', false)
            ->assertSee('onclick="getStatistics(1)"', false);
    }

    #[Test]
    public function an_organizer_cannot_open_the_admin_dashboard()
    {
        $this->actingAs($this->organizer())->get('/admin/home')->assertForbidden();
    }
}
