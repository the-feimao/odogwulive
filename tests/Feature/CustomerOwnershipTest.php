<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CustomerOwnershipTest extends TestCase
{
    #[Test]
    public function a_customer_cannot_read_another_customers_api_order()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $owner = $this->customer();
        $other = $this->customer();
        $order = $this->order($event, $ticket, $owner, $organizer);

        Passport::actingAs($other, ['*'], 'userApi');
        $this->getJson('/api/user/view-single-order/'.$order->id)->assertForbidden();

        Passport::actingAs($owner, ['*'], 'userApi');
        $this->getJson('/api/user/view-single-order/'.$order->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $order->id);
    }

    #[Test]
    public function a_customer_cannot_open_another_customers_web_order()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = $this->ticket($event);
        $owner = $this->customer();
        $other = $this->customer();
        $order = $this->order($event, $ticket, $owner, $organizer);

        $this->actingAs($other, 'appuser')
            ->get('/getOrder/'.$order->id)
            ->assertForbidden();

        $this->actingAs($owner, 'appuser')
            ->get('/getOrder/'.$order->id)
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
