<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminEventPagesTest extends TestCase
{
    private function activateLicense(): void
    {
        // The existing master layout hides admin content while the license is
        // inactive. Tests run inside a transaction, so this never touches real data.
        Setting::find(1)->update(['license_status' => 1]);
    }

    private function userWithoutRole(): User
    {
        return User::create([
            'name' => 'No Event Access',
            'first_name' => 'No',
            'last_name' => 'Access',
            'email' => 'no-event-'.uniqid().'@example.test',
            'password' => bcrypt('secret123'),
            'status' => 1,
            'language' => 'English',
        ]);
    }

    #[Test]
    public function event_list_keeps_role_columns_and_action_hooks()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'List', 'last_name' => 'Organizer']);
        $event = $this->event($organizer, ['name' => 'List Event']);

        $this->actingAs($admin)->get(route('events.index'))->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('List Event', false)
            ->assertSee('List Organizer', false)
            ->assertSee('Organization')
            ->assertSee('id="report_table"', false)
            ->assertSee('href="'.url('events/create').'"', false)
            ->assertSee('href="'.route('events.show', $event->id).'"', false)
            ->assertSee('href="'.url('event-gallery/'.$event->id).'"', false)
            ->assertSee('href="'.route('events.edit', $event->id).'"', false)
            ->assertSee("deleteData('events','".$event->id."');", false);

        $this->actingAs($organizer)->get(route('events.index'))->assertOk()
            ->assertSee('Scanner')
            ->assertSee('List Event', false);

        $this->actingAs($this->organizer())->get(route('events.index'))->assertOk()
            ->assertDontSee('List Event', false);

        $this->actingAs($this->userWithoutRole())->get(route('events.index'))->assertForbidden();
    }

    #[Test]
    public function event_forms_keep_legacy_hooks_and_crud_flows()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'Form', 'last_name' => 'Organizer']);
        $scanner = $this->scannerUser($organizer);
        $category = $this->category();

        $this->actingAs($admin)->get(route('events.create'))->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('action="'.url('events').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('class="event-form', false)
            ->assertSee('name="image"', false)
            ->assertSee('id="image-preview"', false)
            ->assertSee('id="image-upload"', false)
            ->assertSee('id="image-label"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('name="start_time"', false)
            ->assertSee('id="start_time"', false)
            ->assertSee('name="end_time"', false)
            ->assertSee('id="end_time"', false)
            ->assertSee('name="user_id"', false)
            ->assertSee('id="org-for-event"', false)
            ->assertSee('name="scanner_id"', false)
            ->assertSee('scanner_id select2', false)
            ->assertSee('name="people"', false)
            ->assertSee('id="people"', false)
            ->assertSee('name="status"', false)
            ->assertSee('name="tags"', false)
            ->assertSee('inputtags', false)
            ->assertSee('name="description"', false)
            ->assertSee('textarea_editor', false)
            ->assertSee('name="type"', false)
            ->assertSee('value="offline"', false)
            ->assertSee('value="online"', false)
            ->assertSee('class="location-detail', false)
            ->assertSee('name="address"', false)
            ->assertSee('id="address"', false)
            ->assertSee('name="lat"', false)
            ->assertSee('id="lat"', false)
            ->assertSee('name="lang"', false)
            ->assertSee('id="lang"', false)
            ->assertSee('name="security"', false)
            ->assertSee('select2');

        $this->actingAs($organizer)->get(route('events.create'))->assertOk()
            ->assertDontSee('name="user_id"', false)
            ->assertSee('name="scanner_id"', false);

        $createdImage = null;
        $created = null;

        try {
            $this->actingAs($organizer)->post('/events', [
                'name' => 'Stored Event',
                'image' => UploadedFile::fake()->create('event-cover.jpg', 1, 'image/jpeg'),
                'start_time' => now()->addDay()->format('Y-m-d h:i A'),
                'end_time' => now()->addDays(2)->format('Y-m-d h:i A'),
                'category_id' => $category->id,
                'type' => 'offline',
                'address' => '12 Test Street, Lagos',
                'lat' => '6.5244',
                'lang' => '3.3792',
                'status' => 1,
                'description' => 'A stored test event.',
                'scanner_id' => $scanner->id,
                'people' => 120,
                'tags' => 'music,festival',
                'security' => 1,
            ])->assertRedirect(route('events.index'));

            $created = Event::where('name', 'Stored Event')->firstOrFail();
            $createdImage = $created->image;

            $this->assertSame((int) $organizer->id, (int) $created->user_id);
            $this->assertNotEmpty($createdImage);

            $this->actingAs($admin)->get(route('events.edit', $created->id))->assertOk()
                ->assertSee('id="org-for-event"', false);

            $this->actingAs($organizer)->get(route('events.edit', $created->id))->assertOk()
                ->assertSee('action="'.route('events.update', $created->id).'"', false)
                ->assertSee('method="POST"', false)
                ->assertSee('name="_method"', false)
                ->assertSee('value="PUT"', false)
                ->assertSee('class="event-form', false)
                ->assertSee('Stored Event')
                ->assertDontSee('id="org-for-event"', false)
                ->assertSee('class="location-detail', false);

            $this->put(route('events.update', $created->id), [
                'name' => 'Updated Stored Event',
                'start_time' => now()->addDay()->format('Y-m-d h:i A'),
                'end_time' => now()->addDays(2)->format('Y-m-d h:i A'),
                'category_id' => $category->id,
                'type' => 'online',
                'status' => 0,
                'description' => 'An updated test event.',
                'scanner_id' => $scanner->id,
                'people' => 150,
                'security' => 0,
            ])->assertRedirect(route('events.index'));

            $this->assertDatabaseHas('events', [
                'id' => $created->id,
                'name' => 'Updated Stored Event',
                'type' => 'online',
                'people' => 150,
                'status' => 0,
            ]);

            $ticket = $this->ticket($created);
            $this->delete(route('events.destroy', $created->id))->assertOk();
            $this->assertSame(1, (int) $created->fresh()->is_deleted);
            $this->assertSame('Deleted', $created->fresh()->event_status);
            $this->assertSame(1, (int) $ticket->fresh()->is_deleted);
        } finally {
            if ($createdImage) {
                @unlink(public_path('images/upload/'.$createdImage));
            }
        }

        $this->actingAs($this->userWithoutRole())->get(route('events.create'))->assertForbidden();
        $this->actingAs($this->organizer())->get(route('events.edit', $created->id))->assertForbidden();
    }

    #[Test]
    public function event_form_date_time_inputs_keep_flatpickr_contract()
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $start = now()->addDay()->setTime(9, 5, 0);
        $end = now()->addDay()->setTime(11, 30, 0);
        $event = $this->event($organizer, [
            'name' => 'Picker Event',
            'start_time' => $start,
            'end_time' => $end,
        ]);
        $event->refresh();

        $createHtml = $this->actingAs($organizer)->get(route('events.create'))
            ->assertOk()->getContent();
        $this->assertSame(2, substr_count($createHtml, 'data-flatpickr="datetime"'));

        $editHtml = $this->actingAs($organizer)->get(route('events.edit', $event->id))
            ->assertOk()->getContent();

        foreach (['start_time', 'end_time'] as $field) {
            $value = preg_quote($event->{$field}->format('Y-m-d h:i A'), '/');
            $this->assertMatchesRegularExpression(
                '/<input[^>]*id="'.$field.'"[^>]*value="'.$value.'"[^>]*data-flatpickr="datetime"[^>]*autocomplete="off"/',
                $editHtml
            );
        }
    }

    #[Test]
    public function event_form_option_tabs_keep_radio_contract_and_add_guidance()
    {
        $this->activateLicense();
        $organizer = $this->organizer();

        $response = $this->actingAs($organizer)->get(route('events.create'))->assertOk()
            ->assertSee('Choose how attendees will join this event.', false)
            ->assertSee('Add a physical address and map coordinates.', false)
            ->assertSee('Meet attendees online without a venue address.', false)
            ->assertSee('Choose who can find and book this event.', false)
            ->assertSee('Listed for everyone to find and book.', false)
            ->assertSee('Hidden from public listings and search.', false)
            ->assertSee('class="selectgroup', false)
            ->assertSee('class="location-detail', false);

        $html = $response->getContent();
        foreach ([['type', 'offline'], ['type', 'online'], ['security', '1'], ['security', '0']] as [$name, $value]) {
            $this->assertMatchesRegularExpression(
                '/<input[^>]*type="radio"[^>]*name="'.$name.'"[^>]*value="'.$value.'"[^>]*class="[^"]*tw-peer tw-sr-only/',
                $html
            );
        }
    }

    #[Test]
    public function event_detail_and_gallery_keep_ticket_and_dropzone_hooks()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'Detail', 'last_name' => 'Organizer']);
        $event = $this->event($organizer, [
            'name' => 'Detail Event',
            'gallery' => 'cover-a.jpg,cover-b.jpg',
        ]);
        $ticket = $this->ticket($event, ['name' => 'Detail Ticket', 'price' => 2500]);
        $customer = $this->customer();
        $this->order($event, $ticket, $customer, $organizer, ['quantity' => 2, 'payment' => 5000]);
        $emptyEvent = $this->event($organizer, ['name' => 'Empty Ticket Event']);

        $detailUrl = route('events.show', $event->id);
        $galleryUrl = url('event-gallery/'.$event->id);

        $this->actingAs($organizer)->get($detailUrl)->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Detail Event', false)
            ->assertSee('Detail Ticket', false)
            ->assertSee('Recent Sales', false)
            ->assertSee('href="'.url($event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets').'"', false)
            ->assertSee('2/50', false)
            ->assertDontSee('href="'.url($event->id.'/ticket/create').'"', false);

        $this->actingAs($organizer)->get(route('events.show', $emptyEvent->id))->assertOk()
            ->assertSee('No tickets', false)
            ->assertSee('href="'.url($emptyEvent->id.'/ticket/create').'"', false);

        $this->actingAs($admin)->get($detailUrl)->assertOk()->assertSee('Detail Event', false);

        $this->get($galleryUrl)->assertOk()
            ->assertSee('cover-a.jpg', false)
            ->assertSee('cover-b.jpg', false)
            ->assertSee('id="data_id"', false)
            ->assertSee('value="'.$event->id.'"', false)
            ->assertSee('data-toggle="dropzone"', false)
            ->assertSee('data-dropzone-url="'.url('add-event-gallery').'"', false)
            ->assertSee('id="dropzoneMultipleUpload"', false)
            ->assertSee('name="image[]"', false)
            ->assertSee('dz-preview', false)
            ->assertSee('href="'.url('remove-image/cover-a.jpg/'.$event->id).'"', false);

        $this->get(url('remove-image/cover-a.jpg/'.$event->id))->assertRedirect();
        $this->assertStringNotContainsString('cover-a.jpg', (string) $event->fresh()->gallery);
        $this->assertStringContainsString('cover-b.jpg', (string) $event->fresh()->gallery);

        $this->actingAs($this->organizer())->get($detailUrl)->assertForbidden();
        $this->get($galleryUrl)->assertForbidden();
        $this->actingAs($this->userWithoutRole())->get($detailUrl)->assertForbidden();
    }
}
