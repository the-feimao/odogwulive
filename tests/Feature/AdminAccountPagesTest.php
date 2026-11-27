<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Language;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAccountPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function the_review_page_keeps_its_admin_actions()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Reviewed Event']);
        $customer = $this->customer(['name' => 'Review Customer']);
        $ticket = $this->ticket($event);
        $order = $this->order($event, $ticket, $customer, $organizer);
        $review = Review::create([
            'order_id' => $order->id,
            'event_id' => $event->id,
            'organization_id' => $organizer->id,
            'user_id' => $customer->id,
            'message' => 'A thoughtful review message.',
            'rate' => 4,
            'status' => 0,
        ]);

        $this->actingAs($this->admin())->get('/user-review')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('A thoughtful review message.', false)
            ->assertSee('Reviewed Event', false)
            ->assertSee('Review Customer', false)
            ->assertSee('href="'.url('change-review-status/'.$review->id).'"', false)
            ->assertSee('href="'.url('delete-review/'.$review->id).'"', false);
    }

    #[Test]
    public function an_organizer_cannot_open_the_review_page()
    {
        $this->actingAs($this->organizer())->get('/user-review')->assertForbidden();
    }

    #[Test]
    public function the_notification_page_keeps_its_view_and_archive_links()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Notification Event']);
        $ticket = $this->ticket($event);
        $customer = $this->customer(['name' => 'Notification Customer']);
        $order = $this->order($event, $ticket, $customer, $organizer);
        $notification = Notification::create([
            'organizer_id' => $organizer->id,
            'user_id' => null,
            'order_id' => $order->id,
            'title' => 'New Ticket Booked',
            'message' => 'A new ticket was booked.',
        ]);

        $this->actingAs($organizer)->get('/notification')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('A new ticket was booked.', false)
            ->assertSee('Notification Customer', false)
            ->assertSee('href="'.url('orders/'.$order->id).'"', false)
            ->assertSee('href="'.url('delete-notification/'.$notification->id).'"', false);

        $this->get('/delete-notification/'.$notification->id)->assertRedirect();
        $this->assertDatabaseMissing('notification', ['id' => $notification->id]);
    }

    #[Test]
    public function the_admin_header_shows_the_notification_bell()
    {
        $this->activateLicense();
        $admin = $this->admin();

        Notification::create([
            'organizer_id' => null,
            'user_id' => $admin->id,
            'order_id' => null,
            'title' => 'Admin notice',
            'message' => 'A new booking needs review.',
        ]);

        $this->actingAs($admin)->get('/admin/home')
            ->assertOk()
            ->assertSee('notification-toggle', false)
            ->assertSee('far fa-bell', false);
    }

    #[Test]
    public function the_organizer_sidebar_links_settings_to_the_profile_page()
    {
        $this->activateLicense();
        $organizer = $this->organizer();

        $this->actingAs($organizer)->get('/organization/home')
            ->assertOk()
            ->assertSee('href="'.url('profile').'"', false)
            ->assertSee('<span>Settings</span>', false)
            ->assertDontSee('href="'.url('admin-setting').'"', false);

        $this->actingAs($organizer)->get(url('profile'))->assertOk();

        $this->actingAs($this->admin())->get('/admin/home')
            ->assertOk()
            ->assertSee('href="'.url('admin-setting').'"', false);
    }

    #[Test]
    public function the_admin_profile_keeps_both_forms_and_their_field_names()
    {
        $this->activateLicense();
        Language::create([
            'name' => 'Yoruba',
            'json_file' => 'yo.json',
            'image' => 'yo.png',
            'direction' => 'ltr',
            'status' => 1,
        ]);
        $admin = $this->admin();
        $admin->update(['language' => 'Yoruba', 'bio' => 'Organizer bio']);

        $this->actingAs($admin)->get('/profile')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Organizer bio', false)
            ->assertSee('action="'.url('edit-profile').'"', false)
            ->assertSee('action="'.url('change-password').'"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('readonly', false)
            ->assertSee('name="language"', false)
            ->assertSee('value="Yoruba" selected', false)
            ->assertSee('name="bio"', false)
            ->assertSee('name="current_password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="confirm_password"', false);

        $this->post('/edit-profile', [
            'name' => 'Updated Admin',
            'email' => $admin->email,
            'language' => 'Yoruba',
            'bio' => 'Updated bio',
        ])->assertRedirect(url('profile'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Updated Admin', 'bio' => 'Updated bio']);
    }

    #[Test]
    public function the_organization_profile_shows_followers_without_the_admin_license_gate()
    {
        $organizer = $this->organizer(['name' => 'Followed Organizer']);
        $this->customer(['name' => 'Follower Customer', 'following' => (string) $organizer->id]);

        $this->actingAs($organizer)->get('/profile')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Followers (1)', false)
            ->assertSee('Follower Customer', false)
            ->assertSee('action="'.url('edit-profile').'"', false);
    }

    #[Test]
    public function feedback_pages_keep_their_form_and_delete_hooks()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $admin->update(['name' => 'Feedback Organizer']);
        $feedback = Feedback::create([
            'user_id' => $admin->id,
            'email' => 'feedback@example.test',
            'message' => 'A useful feedback message.',
            'rate' => 4,
        ]);

        $this->actingAs($admin)->get('/feedback')
            ->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('A useful feedback message.', false)
            ->assertSee('Feedback Organizer', false)
            ->assertSee('href="'.route('feedback.edit', $feedback->id).'"', false)
            ->assertSee("deleteData('feedback','".$feedback->id."')", false);

        $this->get('/feedback/create')
            ->assertOk()
            ->assertSee('class="feedback-form', false)
            ->assertSee('action="'.url('feedback').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="rate"', false)
            ->assertSee('id="rate-1"', false)
            ->assertSee("onclick=\"addRate('1');\"", false)
            ->assertSee('name="email"', false)
            ->assertSee('name="message"', false);

        $this->post('/feedback', [
            'email' => 'new-feedback@example.test',
            'message' => 'Created through the existing form contract.',
            'rate' => 5,
        ])->assertRedirect(route('feedback.index'));

        $this->assertDatabaseHas('feedback', [
            'email' => 'new-feedback@example.test',
            'message' => 'Created through the existing form contract.',
            'rate' => 5,
        ]);
    }

    #[Test]
    public function an_organizer_without_feedback_permissions_cannot_open_feedback_pages()
    {
        $this->actingAs($this->organizer())
            ->get('/feedback')
            ->assertForbidden();

        $this->get('/feedback/create')->assertForbidden();
    }
}
