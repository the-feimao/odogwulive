<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminLowRiskPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function the_migrated_admin_lists_render_with_the_design_system_inside_the_existing_master()
    {
        $this->activateLicense();
        $this->actingAs($this->admin());

        $category = $this->category();
        $faq = Faq::create(['question' => 'Test question?', 'answer' => 'Test answer.', 'status' => 1]);
        $banner = Banner::create(['title' => 'Test banner', 'description' => 'Test copy.', 'image' => 'default.png', 'status' => 1]);

        $this->get('/category')->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('main-wrapper')
            ->assertSee('View Category')
            ->assertSee($category->name);

        $this->get('/faq')->assertOk()
            ->assertSee('All FAQs')
            ->assertSee($faq->question)
            ->assertSee("deleteData('faq','".$faq->id."')", false);

        $this->get('/banner')->assertOk()
            ->assertSee('All Banner')
            ->assertSee($banner->title)
            ->assertSee("deleteData('banner','".$banner->id."')", false);
    }

    #[Test]
    public function the_migrated_admin_forms_keep_their_original_field_contract()
    {
        $this->activateLicense();
        $this->actingAs($this->admin());

        $this->get('/category/create')->assertOk()
            ->assertSee('action="'.url('category').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="image"', false)
            ->assertSee('name="status"', false)
            ->assertSee('image-preview');

        $this->get('/banner/create')->assertOk()
            ->assertSee('action="'.url('banner').'"', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="image"', false)
            ->assertSee('name="status"', false);

        $this->get('/faq/create')->assertOk()
            ->assertSee('action="'.url('faq').'"', false)
            ->assertSee('name="question"', false)
            ->assertSee('name="answer"', false);
    }

    #[Test]
    public function the_migrated_edit_forms_keep_their_original_actions_and_values()
    {
        $this->activateLicense();
        $this->actingAs($this->admin());

        $category = $this->category();
        $faq = Faq::create(['question' => 'Edit question?', 'answer' => 'Edit answer.', 'status' => 1]);
        $banner = Banner::create(['title' => 'Edit banner', 'description' => 'Edit copy.', 'image' => 'default.png', 'status' => 1]);

        $this->get(route('category.edit', $category))->assertOk()
            ->assertSee('action="'.route('category.update', [$category->id]).'"', false)
            ->assertSee($category->name);

        $this->get(route('faq.edit', $faq))->assertOk()
            ->assertSee('action="'.url('faq/'.$faq->id).'"', false)
            ->assertSee($faq->question);

        $this->get(route('banner.edit', $banner))->assertOk()
            ->assertSee('action="'.route('banner.update', [$banner->id]).'"', false)
            ->assertSee($banner->title);
    }

    #[Test]
    public function category_permissions_are_unchanged_for_organizers()
    {
        $this->actingAs($this->organizer());

        $this->get('/category')->assertForbidden();
        $this->get('/category/create')->assertForbidden();
    }
}
