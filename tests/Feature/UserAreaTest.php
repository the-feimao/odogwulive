<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Language;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserAreaTest extends TestCase
{
    #[Test]
    public function the_profile_page_renders_saved_items_and_preserves_account_actions()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Saved Test Event']);
        $blog = Blog::create([
            'title' => 'Saved Test Article',
            'description' => 'A saved article.',
            'category_id' => $event->category_id,
            'tags' => 'test',
            'image' => 'default.png',
            'status' => 1,
        ]);

        $customer = $this->customer([
            'favorite' => (string) $event->id,
            'favorite_blog' => (string) $blog->id,
            'following' => (string) $organizer->id,
            'bio' => 'Attendee bio',
            'address' => 'Lagos, Nigeria',
        ]);

        $this->actingAs($customer, 'appuser')
            ->get('/user/profile')
            ->assertOk()
            ->assertSee('Saved Test Event', false)
            ->assertSee('Saved Test Article', false)
            ->assertSee('Test Organizer', false)
            ->assertSee('id="saved-event"', false)
            ->assertSee('id="saved-blog"', false)
            ->assertSee('id="following"', false)
            ->assertSee('id="imagePreview"', false)
            ->assertSee('name="image" id="imgUpload"', false)
            ->assertSee('id="imageUploadForm"', false)
            ->assertSee('action="'.url('upload-profile-image').'"', false)
            ->assertSee('name="bio"', false)
            ->assertSee('btn-bio', false)
            ->assertSee(url('/add-bio'), false)
            ->assertSee('onclick="addFavorite('.$event->id.",'event')\"", false)
            ->assertSee('onclick="addFavorite('.$blog->id.",'blog')\"", false)
            ->assertSee('onclick="follow('.$organizer->id.')"', false)
            ->assertSee('href="'.url('my-tickets').'"', false)
            ->assertSee('href="'.url('update_profile').'"', false)
            ->assertSee('href="'.url('change-password').'"', false)
            ->assertSee('href="'.url('/blog-detail/'.$blog->id).'/', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('wallpapercave.com', false);
    }

    #[Test]
    public function the_my_tickets_page_preserves_order_detail_and_review_hooks()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Ticketed Test Event']);
        $ticket = $this->ticket($event, [
            'name' => 'Test Admission',
            'ticket_number' => 'TICKET-TEST',
            'price' => 1500,
        ]);
        $customer = $this->customer();

        $complete = $this->order($event, $ticket, $customer, $organizer, [
            'order_id' => '#COMPLETE-TEST',
            'order_status' => 'Complete',
            'payment_status' => 1,
            'payment_type' => 'STRIPE',
            'payment_token' => 'tok_test',
        ]);

        $this->actingAs($customer, 'appuser')
            ->get('/my-tickets')
            ->assertOk()
            ->assertSee('Ticketed Test Event', false)
            ->assertSee('#COMPLETE-TEST', false)
            ->assertSee('id="order-'.$complete->id.'"', false)
            ->assertSee('event-data', false)
            ->assertSee('data-order-id="'.$complete->id.'"', false)
            ->assertSee(url('/getOrder'), false)
            ->assertSee('href="'.url('my-tickets/'.$complete->id.'/invoice-print').'"', false)
            ->assertSee('data-id="'.$complete->id.'"', false)
            ->assertSee('onclick="viewPayment()"', false)
            ->assertSee('id="payment-data"', false)
            ->assertSee('class="open-addReview', false)
            ->assertSee('data-target="#reviewModel"', false)
            ->assertSee('id="reviewModel"', false)
            ->assertSee('id="order_id"', false)
            ->assertSee('name="rate"', false)
            ->assertSee('name="message"', false)
            ->assertSee('action="'.url('add-review').'"', false)
            ->assertSee('id="rate-1"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false);
    }

    #[Test]
    public function the_profile_update_page_preserves_the_existing_form_contract()
    {
        Language::create([
            'name' => 'English',
            'json_file' => 'en.json',
            'image' => 'en.png',
            'direction' => 'ltr',
            'status' => 1,
        ]);

        $customer = $this->customer([
            'name' => 'Ada',
            'last_name' => 'Lovelace',
            'phone' => '08012345678',
        ]);

        $this->actingAs($customer, 'appuser')
            ->get('/update_profile')
            ->assertOk()
            ->assertSee('action="'.url('update_user_profile').'"', false)
            ->assertSee('name="login"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="language"', false)
            ->assertSee('value="Ada"', false)
            ->assertSee('value="Lovelace"', false)
            ->assertSee('value="English"', false)
            ->assertSee('readonly', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('NGrUbJBA', false);
    }

    #[Test]
    public function the_change_password_page_preserves_the_existing_form_contract()
    {
        $this->actingAs($this->customer(), 'appuser')
            ->get('/change-password')
            ->assertOk()
            ->assertSee('action="'.url('change-password').'"', false)
            ->assertSee('name="old_password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('autocomplete="new-password"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('NGrUbJBA', false);
    }

    #[Test]
    public function the_inline_profile_endpoints_keep_their_json_responses()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $customer = $this->customer();

        $this->actingAs($customer, 'appuser')
            ->post('/add-bio', ['bio' => 'Updated bio'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'Updated bio');

        $this->actingAs($customer, 'appuser')
            ->get('/add-favorite/'.$event->id.'/event')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($customer, 'appuser')
            ->get('/add-followList/'.$organizer->id)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    #[Test]
    public function the_review_form_still_creates_a_review_for_the_order()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, ['order_status' => 'Complete']);

        $this->actingAs($customer, 'appuser')
            ->post('/add-review', [
                'order_id' => $order->id,
                'rate' => 5,
                'message' => 'A great test event.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('review', [
            'order_id' => $order->id,
            'organization_id' => $organizer->id,
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'rate' => 5,
            'message' => 'A great test event.',
            'status' => 0,
        ]);
    }
}
