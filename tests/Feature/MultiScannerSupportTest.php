<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MultiScannerSupportTest extends TestCase
{
    private function activateLicense(): void
    {
        Setting::find(1)->update(['license_status' => 1]);
    }

    #[Test]
    public function admin_can_assign_and_replace_multiple_scanners(): void
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $first = $this->scannerUser($organizer);
        $second = $this->scannerUser($organizer);

        $this->actingAs($admin)->get(route('events.create'))->assertOk()
            ->assertSee('name="scanner_ids[]"', false)
            ->assertSee('id="additional-scanners"', false);

        $payload = [
            'name' => 'Multi Scanner Event',
            'start_time' => now()->addDay()->format('Y-m-d h:i A'),
            'end_time' => now()->addDays(2)->format('Y-m-d h:i A'),
            'category_id' => $this->category()->id,
            'user_id' => $organizer->id,
            'type' => 'offline',
            'address' => '12 Test Street, Lagos',
            'lat' => '6.5244',
            'lang' => '3.3792',
            'status' => 1,
            'description' => 'A multi scanner test event.',
            'scanner_id' => $first->id,
            'people' => 120,
            'security' => 1,
        ];

        $createdImage = null;

        try {
            $this->actingAs($admin)->post('/events', array_merge($payload, [
                'image' => UploadedFile::fake()->create('multi-scanner.jpg', 1, 'image/jpeg'),
                'scanner_ids' => [$second->id],
            ]))->assertRedirect(route('events.index'));

            $event = Event::where('name', 'Multi Scanner Event')->firstOrFail();
            $createdImage = $event->image;
            $this->assertEqualsCanonicalizing(
                [$first->id, $second->id],
                $event->scanners->pluck('id')->all()
            );

            $this->put(route('events.update', $event->id), $payload)
                ->assertRedirect(route('events.index'));

            $this->assertEquals([$first->id], $event->fresh()->scanners->pluck('id')->all());
        } finally {
            if ($createdImage) {
                @unlink(public_path('images/upload/'.$createdImage));
            }
        }
    }

    #[Test]
    public function an_organizer_cannot_assign_a_scanner_from_another_organization(): void
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $otherScanner = $this->scannerUser($this->organizer());

        $this->actingAs($organizer)->post('/events', [
            'name' => 'Invalid Scanner Event',
            'image' => UploadedFile::fake()->create('invalid-scanner.jpg', 1, 'image/jpeg'),
            'start_time' => now()->addDay()->format('Y-m-d h:i A'),
            'end_time' => now()->addDays(2)->format('Y-m-d h:i A'),
            'category_id' => $this->category()->id,
            'type' => 'offline',
            'address' => '12 Test Street, Lagos',
            'lat' => '6.5244',
            'lang' => '3.3792',
            'status' => 1,
            'description' => 'An invalid scanner assignment.',
            'scanner_id' => $otherScanner->id,
            'people' => 120,
        ])->assertSessionHasErrors(['scanner_id']);

        $this->assertNull(Event::where('name', 'Invalid Scanner Event')->first());
    }

    #[Test]
    public function scanner_api_accepts_pivot_only_assignments_and_rejects_unassigned_scanners(): void
    {
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);
        $otherScanner = $this->scannerUser($this->organizer());
        $event = $this->event($organizer, ['name' => 'Pivot Assigned Event']);
        $event->syncScanners([$scanner->id]);

        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'PIVOT-SCAN-1']);

        Passport::actingAs($scanner, ['*'], 'api');

        $this->getJson('/api/scanner/events')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Pivot Assigned Event')
            ->assertJsonPath('data.0.scanTicket', 0);

        $this->getJson('/api/scanner/scan-ticket/PIVOT-SCAN-1/'.$event->id)
            ->assertOk()
            ->assertJsonPath('success', true);

        Passport::actingAs($otherScanner, ['*'], 'api');

        $this->getJson('/api/scanner/events')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/scanner/scan-ticket/PIVOT-SCAN-1/'.$event->id)->assertForbidden();
    }

    #[Test]
    public function organization_api_can_set_additional_scanners_without_breaking_legacy_fields(): void
    {
        $organizer = $this->organizer();
        $first = $this->scannerUser($organizer);
        $second = $this->scannerUser($organizer);
        $otherScanner = $this->scannerUser($this->organizer());

        Passport::actingAs($organizer, ['*'], 'api');

        $payload = [
            'name' => 'API Multi Scanner Event',
            'image' => base64_encode('fake png bytes'),
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '13:00',
            'scanner_id' => $first->id,
            'scanner_ids' => [$second->id],
            'category_id' => $this->category()->id,
            'type' => 'offline',
            'address' => '12 Test Street, Lagos',
            'lat' => '6.5244',
            'lang' => '3.3792',
            'status' => 1,
            'description' => 'An API multi scanner test event.',
            'people' => 120,
        ];

        $createdImage = null;

        try {
            $this->postJson('/api/organization/add-event', $payload)
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonStructure(['data' => ['id', 'name', 'scanner_id']]);

            $event = Event::where('name', 'API Multi Scanner Event')->firstOrFail();
            $createdImage = $event->image;
            $this->assertEqualsCanonicalizing(
                [$first->id, $second->id],
                $event->scanners->pluck('id')->all()
            );
        } finally {
            if ($createdImage) {
                @unlink(public_path('images/upload/'.$createdImage));
            }
        }

        $this->postJson('/api/organization/add-event', array_merge($payload, [
            'name' => 'Foreign Scanner Event',
            'scanner_ids' => [$otherScanner->id],
        ]))->assertStatus(404);
    }

    #[Test]
    public function checkin_stats_show_every_scanner_without_duplicating_tickets(): void
    {
        $this->activateLicense();
        $organizer = $this->organizer();
        $first = $this->scannerUser($organizer);
        $second = $this->scannerUser($organizer);
        $first->update(['name' => null, 'first_name' => 'Assigned', 'last_name' => 'Scanner']);
        $second->update(['name' => null, 'first_name' => 'Additional', 'last_name' => 'Scanner']);
        $event = $this->event($organizer, [
            'name' => 'Shared Scanner Event',
            'scanner_id' => $first->id,
        ]);
        $event->syncScanners([$first->id, $second->id]);

        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'SHARED-IN', 'status' => 1]);
        $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'SHARED-OUT', 'status' => 0]);

        $this->actingAs($organizer)->get('/scanner')->assertOk()
            ->assertSee('Shared Scanner Event')
            ->assertSee('Assigned Scanner')
            ->assertSee('Additional Scanner')
            ->assertViewHas('events', function ($events) use ($event, $first, $second) {
                $row = $events->firstWhere('id', $event->id);

                return $row
                    && (int) $row->tickets_sold === 2
                    && (int) $row->checked_in === 1
                    && (int) $row->remaining === 1
                    && $row->scanner_names === 'Assigned Scanner, Additional Scanner'
                    && $row->assigned_scanners->pluck('id')->all() === [$first->id, $second->id];
            })
            ->assertViewHas('scanners', function ($scanners) use ($first, $second) {
                foreach ([$first, $second] as $scanner) {
                    $row = $scanners->firstWhere('id', $scanner->id);

                    if (! $row || (int) $row->total_event !== 1 || (int) $row->checked_in !== 1 || (int) $row->remaining !== 1) {
                        return false;
                    }
                }

                return true;
            });
    }
}
