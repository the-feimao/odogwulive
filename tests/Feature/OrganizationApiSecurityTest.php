<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OrganizationApiSecurityTest extends TestCase
{
    #[Test]
    public function only_organizations_can_log_in_to_the_organization_api()
    {
        $scanner = $this->scannerUser($this->organizer());

        $this->postJson('/api/organization/login', [
            'email' => $scanner->email,
            'password' => 'secret123',
            'device_token' => 'device-token',
        ])->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('msg', 'Only organization can login.')
            ->assertJsonPath('data.token', null);
    }

    #[Test]
    public function an_organization_can_still_log_in_to_the_organization_api()
    {
        $organizer = $this->organizer(['password' => Hash::make('secret123')]);

        $this->postJson('/api/organization/login', [
            'email' => $organizer->email,
            'password' => 'secret123',
            'device_token' => 'device-token',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token']]);
    }

    #[Test]
    public function an_organization_cannot_read_another_organizers_event()
    {
        $owner = $this->organizer();
        $other = $this->organizer();
        $event = $this->event($owner);

        Passport::actingAs($other, ['*'], 'api');

        $this->getJson('/api/organization/eventDetail/'.$event->id)
            ->assertForbidden();
    }

    #[Test]
    public function an_organization_cannot_delete_another_organizers_tax()
    {
        $owner = $this->organizer();
        $other = $this->organizer();
        $tax = $this->tax($owner);

        Passport::actingAs($other, ['*'], 'api');

        $this->getJson('/api/organization/delete-tax/'.$tax->id)
            ->assertForbidden();

        $this->assertNotNull($tax->fresh());
    }
}
