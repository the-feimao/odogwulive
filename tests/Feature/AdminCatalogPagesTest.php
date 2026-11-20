<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminCatalogPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function tax_pages_keep_their_form_and_table_contracts()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $tax = $this->tax($admin, ['name' => 'VAT Tax', 'price' => 75, 'allow_all_bill' => 1]);

        $this->actingAs($admin)->get('/tax')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Tax')
            ->assertSee('VAT Tax')
            ->assertSee('75', false)
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.route('tax.edit', $tax->id).'"', false);

        $this->get('/tax/create')
            ->assertOk()
            ->assertSee('action="'.url('tax').'"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="price"', false)
            ->assertSee('name="status"', false)
            ->assertSee('name="allow_all_bill"', false)
            ->assertSee('id="allow_all_bill"', false);

        $this->get(route('tax.edit', $tax))
            ->assertOk()
            ->assertSee('action="'.url('tax/'.$tax->id).'"', false)
            ->assertSee('VAT Tax')
            ->assertSee('name="allow_all_bill"', false);

        // The admin-side store path is a pre-existing controller issue: it does
        // not set the required user_id. This group only changes the Blade view,
        // so the form action and field contract are asserted above instead.
    }

    #[Test]
    public function coupon_pages_keep_their_form_hooks_and_delete_contract()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Coupon Event']);
        $coupon = $this->coupon($organizer, $event, ['name' => 'Launch Coupon', 'discount' => 15]);

        $this->actingAs($this->admin())->get('/coupon')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('View Coupon')
            ->assertSee('Coupon Event')
            ->assertSee('Launch Coupon')
            ->assertSee('15%', false)
            ->assertSee("deleteData('coupon','".$coupon->id."')", false)
            ->assertSee('href="'.route('coupon.edit', $coupon->id).'"', false);

        $this->get('/coupon/create')
            ->assertOk()
            ->assertSee('action="'.url('coupon').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="event_id"', false)
            ->assertSee('name="discount"', false)
            ->assertSee('name="max_use"', false)
            ->assertSee('name="start_date"', false)
            ->assertSee('id="start_date"', false)
            ->assertSee('name="end_date"', false)
            ->assertSee('id="end_date"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="status"', false)
            ->assertSee('Coupon Event');

        $this->get(route('coupon.edit', $coupon))
            ->assertOk()
            ->assertSee('action="'.url('coupon/'.$coupon->id).'"', false)
            ->assertSee('Launch Coupon')
            ->assertSee('id="start_date"', false)
            ->assertSee('id="end_date"', false);

        $this->post('/coupon', [
            'name' => 'Checkout Coupon',
            'event_id' => $event->id,
            'discount' => 10,
            'max_use' => 5,
            'start_date' => '2026-11-01',
            'end_date' => '2026-12-01',
            'description' => 'Created through the existing form contract.',
            'status' => 1,
        ])->assertRedirect(route('coupon.index'));

        $this->assertDatabaseHas('coupon', [
            'name' => 'Checkout Coupon',
            'event_id' => $event->id,
            'discount' => 10,
        ]);
    }

    #[Test]
    public function language_pages_keep_their_upload_and_sample_file_contracts()
    {
        $this->activateLicense();
        $language = Language::create([
            'name' => 'Yoruba',
            'json_file' => 'yo.json',
            'image' => 'yo.png',
            'direction' => 'ltr',
            'status' => 1,
        ]);

        $this->actingAs($this->admin())->get('/language')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('All Languages')
            ->assertSee('Yoruba')
            ->assertSee('href="'.url('language/download_sample_file').'"', false)
            ->assertSee('href="'.route('language.edit', $language->id).'"', false);

        $this->get('/language/create')
            ->assertOk()
            ->assertSee('action="'.url('language').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="image"', false)
            ->assertSee('id="image-upload"', false)
            ->assertSee('id="image-preview"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="json_file"', false)
            ->assertSee('accept=".json"', false)
            ->assertSee('name="direction"', false)
            ->assertSee('name="status"', false);

        $this->get(route('language.edit', $language))
            ->assertOk()
            ->assertSee('action="'.url('language/'.$language->id).'"', false)
            ->assertSee('Yoruba')
            ->assertSee('readonly', false)
            ->assertSee('name="json_file"', false);

        $this->put('/language/'.$language->id, [
            'name' => 'Yoruba',
            'direction' => 'rtl',
            'status' => 0,
        ])->assertRedirect('/language');

        $this->assertDatabaseHas('language', [
            'id' => $language->id,
            'direction' => 'rtl',
            'status' => 0,
        ]);
    }
}
