<?php

namespace Tests\Concerns;

use App\Models\AppUser;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderChild;
use App\Models\Tax;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

trait CreatesFixtures
{
    protected function organizer(array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Test Organizer',
            'first_name' => 'Test',
            'last_name' => 'Organizer',
            'email' => 'organizer-'.uniqid().'@example.test',
            'password' => Hash::make('secret123'),
            'status' => 1,
            'language' => 'English',
        ], $attributes));

        $user->assignRole(Role::findByName('organization', 'web'));

        return $user;
    }

    protected function admin(): User
    {
        $user = $this->organizer([
            'name' => 'Test Admin',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin-'.uniqid().'@example.test',
        ]);
        $user->assignRole(Role::findByName('admin', 'web'));

        return $user;
    }

    protected function scannerUser(User $organizer): User
    {
        $user = User::create([
            'name' => 'Test Scanner',
            'first_name' => 'Test',
            'last_name' => 'Scanner',
            'email' => 'scanner-'.uniqid().'@example.test',
            'password' => Hash::make('secret123'),
            'status' => 1,
            'org_id' => $organizer->id,
            'language' => 'English',
        ]);
        $user->assignRole(Role::findByName('scanner', 'web'));

        return $user;
    }

    protected function customer(array $attributes = []): AppUser
    {
        return AppUser::create(array_merge([
            'name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'customer-'.uniqid().'@example.test',
            'password' => Hash::make('secret123'),
            'phone' => '1234567890',
            'status' => 1,
            'provider' => 'LOCAL',
            'language' => 'English',
        ], $attributes));
    }

    protected function category(): Category
    {
        return Category::create([
            'name' => 'Test Category',
            'image' => 'default.png',
            'status' => 1,
        ]);
    }

    protected function event(User $organizer, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'name' => 'Test Event '.uniqid(),
            'user_id' => $organizer->id,
            'type' => 'offline',
            'address' => 'Test Venue, Lagos',
            'category_id' => $this->category()->id,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(3),
            'image' => 'default.png',
            'gallery' => '',
            'people' => 100,
            'lat' => '6.5244',
            'lang' => '3.3792',
            'description' => 'A test event.',
            'status' => 1,
            'event_status' => 'Pending',
            'is_deleted' => 0,
        ], $attributes));
    }

    protected function ticket(Event $event, array $attributes = []): Ticket
    {
        return Ticket::create(array_merge([
            'event_id' => $event->id,
            'user_id' => $event->user_id,
            'name' => 'General Admission',
            'type' => 'paid',
            'quantity' => 50,
            'ticket_per_order' => 10,
            'start_time' => now()->subDay(),
            'end_time' => now()->addDay(),
            'price' => 1000,
            'description' => 'Test ticket',
            'status' => 1,
            'is_deleted' => 0,
        ], $attributes));
    }

    protected function tax(User $organizer, array $attributes = []): Tax
    {
        return Tax::create(array_merge([
            'user_id' => $organizer->id,
            'name' => 'Test VAT',
            'price' => 100,
            'status' => 1,
            'allow_all_bill' => 1,
        ], $attributes));
    }

    protected function coupon(User $organizer, Event $event, array $attributes = []): Coupon
    {
        return Coupon::create(array_merge([
            'name' => 'Test Coupon',
            'user_id' => $organizer->id,
            'event_id' => $event->id,
            'coupon_code' => 'TEST'.strtoupper(uniqid()),
            'discount' => 10,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'description' => 'Test coupon',
            'max_use' => 10,
            'use_count' => 0,
            'status' => 1,
        ], $attributes));
    }

    protected function order(Event $event, Ticket $ticket, AppUser $customer, User $organizer, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'order_id' => '#'.uniqid(),
            'customer_id' => $customer->id,
            'organization_id' => $organizer->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'coupon_discount' => 0,
            'payment' => 2000,
            'tax' => 0,
            'org_commission' => 1,
            'payment_type' => 'LOCAL',
            'payment_status' => 0,
            'order_status' => 'Pending',
        ], $attributes));
    }

    protected function orderChild(Order $order, Ticket $ticket, AppUser $customer, array $attributes = []): OrderChild
    {
        return OrderChild::create(array_merge([
            'order_id' => $order->id,
            'ticket_id' => $ticket->id,
            'customer_id' => $customer->id,
            'ticket_number' => uniqid('TKT'),
            'status' => 0,
        ], $attributes));
    }
}
