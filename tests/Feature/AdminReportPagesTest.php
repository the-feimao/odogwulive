<?php

namespace Tests\Feature;

use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminReportPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function admin_reports_keep_their_filter_contracts_and_table_hooks()
    {
        $this->activateLicense();
        $organizer = $this->organizer(['first_name' => 'Report', 'last_name' => 'Organizer']);
        $event = $this->event($organizer, ['name' => 'Report Event']);
        $ticket = $this->ticket($event);
        $customer = $this->customer(['name' => 'Report Customer']);
        $this->order($event, $ticket, $customer, $organizer, ['payment_status' => 1]);

        $this->actingAs($this->admin())
            ->get('/admin-report/customer')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Report Customer', false)
            ->assertSee('action="'.url('admin-report/customer').'"', false)
            ->assertSee('name="duration"', false)
            ->assertSee('id="report_table"', false);

        $this->get('/admin-report/organization')
            ->assertOk()
            ->assertSee('Report Organizer', false)
            ->assertSee('Total Events')
            ->assertSee('id="report_table"', false);

        $this->get('/admin-report/revenue')
            ->assertOk()
            ->assertSee('Report Event', false)
            ->assertSee('name="organizer"', false)
            ->assertSee('name="customer"', false)
            ->assertSee('id="revenue_table"', false);
    }

    #[Test]
    public function organization_reports_keep_their_filter_contracts_and_table_hooks()
    {
        $organizer = $this->organizer(['name' => 'Org Report Organizer']);
        $event = $this->event($organizer, ['name' => 'Org Report Event']);
        $ticket = $this->ticket($event);
        $customer = $this->customer(['name' => 'Org Report Customer']);
        $this->order($event, $ticket, $customer, $organizer, ['payment_status' => 1]);

        $this->actingAs($organizer)
            ->get('/organization-report/customer')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Org Report Customer', false)
            ->assertSee('action="'.url('organization-report/customer').'"', false)
            ->assertSee('name="duration"', false)
            ->assertSee('id="report_table"', false);

        $this->get('/organization-report/orders')
            ->assertOk()
            ->assertSee('Org Report Event', false)
            ->assertSee('name="customer"', false)
            ->assertSee('id="org_order_report"', false);
    }

    #[Test]
    public function an_organizer_cannot_open_admin_reports()
    {
        $this->actingAs($this->organizer())
            ->get('/admin-report/customer')
            ->assertForbidden();
    }
}
