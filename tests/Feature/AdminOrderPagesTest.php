<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\OrderTax;
use App\Models\Setting;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminOrderPagesTest extends TestCase
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
            'name' => 'No Order Access',
            'first_name' => 'No',
            'last_name' => 'Access',
            'email' => 'no-order-'.uniqid().'@example.test',
            'password' => bcrypt('secret123'),
            'status' => 1,
            'language' => 'English',
        ]);
    }

    #[Test]
    public function order_list_keeps_status_hooks_and_organization_scope()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'Order', 'last_name' => 'Organizer']);
        $otherOrganizer = $this->organizer(['first_name' => 'Other', 'last_name' => 'Organizer']);
        $event = $this->event($organizer, ['name' => 'Order List Event']);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, ['order_id' => '#OWN-ORDER']);
        $otherEvent = $this->event($otherOrganizer, ['name' => 'Other List Event']);
        $otherTicket = $this->ticket($otherEvent);
        $otherOrder = $this->order($otherEvent, $otherTicket, $customer, $otherOrganizer, ['order_id' => '#OTHER-ORDER']);

        $this->actingAs($admin)->get(url('orders'))->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Order List', false)
            ->assertSee('#OWN-ORDER', false)
            ->assertSee('#OTHER-ORDER', false)
            ->assertSee('id="report_table"', false)
            ->assertSee('name="order_status"', false)
            ->assertSee('id="status-'.$order->id.'"', false)
            ->assertSee('onchange="changeOrderStatus('.$order->id.')"', false)
            ->assertSee('name="payment_status"', false)
            ->assertSee('id="payment-'.$order->id.'"', false)
            ->assertSee('onchange="changePaymentStatus('.$order->id.')"', false)
            ->assertSee('href="'.url('order-invoice/'.$order->id).'"', false);

        $this->actingAs($organizer)->get(url('orders'))->assertOk()
            ->assertSee('#OWN-ORDER', false)
            ->assertDontSee('#OTHER-ORDER', false);

        $this->actingAs($this->userWithoutRole())->get(url('orders'))->assertForbidden();

        $this->actingAs($admin)->post(url('order/changestatus'), [
            'id' => $order->id,
            'order_status' => 'Complete',
        ])->assertOk()->assertJson(['success' => true, 'msg' => 'Status Changed']);
        $this->assertSame('Complete', $order->fresh()->order_status);

        $this->actingAs($admin)->post(url('order/changepaymentstatus'), [
            'id' => $order->id,
            'payment_status' => 1,
        ])->assertOk()->assertJson(['success' => true, 'msg' => 'Status Changed']);
        $this->assertSame(1, (int) $order->fresh()->payment_status);

        $this->actingAs($otherOrganizer)->post(url('order/changestatus'), [
            'id' => $order->id,
            'order_status' => 'Pending',
        ])->assertForbidden();
        $this->actingAs($otherOrganizer)->post(url('order/changepaymentstatus'), [
            'id' => $order->id,
        ])->assertForbidden();
        $this->assertSame('Complete', $order->fresh()->order_status);
        $this->assertSame(1, (int) $order->fresh()->payment_status);
    }

    #[Test]
    public function order_detail_and_invoices_keep_payment_and_print_hooks()
    {
        $this->activateLicense();
        $currency = Currency::where('code', Setting::find(1)->currency)->first()->symbol;
        $admin = $this->admin();
        $organizer = $this->organizer(['first_name' => 'Invoice', 'last_name' => 'Organizer', 'email' => 'invoice-org@example.test']);
        $otherOrganizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Invoice Event']);
        $ticket = $this->ticket($event, ['name' => 'Invoice Ticket', 'price' => 5000]);
        $tax = $this->tax($organizer);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, [
            'order_id' => '#INV-ORDER',
            'order_status' => 'Complete',
            'payment_status' => 1,
            'payment_type' => 'STRIPE',
            'payment_token' => 'tok_test',
            'payment' => 5000,
            'coupon_discount' => 250,
            'tax' => 100,
        ]);
        OrderTax::create(['order_id' => $order->id, 'tax_id' => $tax->id, 'price' => 100]);
        $child = $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'INV-123']);

        $detailUrl = url('orders/'.$order->id);
        $invoiceUrl = url('order-invoice/'.$order->id);
        $printUrl = url('order-invoice-print/'.$order->id);

        $this->actingAs($admin)->get($detailUrl)->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('#INV-ORDER', false)
            ->assertSee('Invoice Event', false)
            ->assertSee('Total tickets', false)
            ->assertSee('Payment Status', false)
            ->assertSee('STRIPE', false)
            ->assertSee('tok_test', false)
            ->assertSee('Complete', false)
            ->assertSee($currency.'5150', false)
            ->assertSee('(-) '.$currency.'250', false)
            ->assertSee('(+) '.$currency.'100', false)
            ->assertSee($currency.'5000', false);

        $this->get($invoiceUrl)->assertOk()
            ->assertSee('invoice-print', false)
            ->assertSee('href="'.$printUrl.'"', false)
            ->assertSee('id="print_invoice"', false)
            ->assertSee('INV-123', false)
            ->assertSee('href="'.url('get-code/'.$child->id).'"', false)
            ->assertSee('Test VAT', false)
            ->assertSee('Token:', false)
            ->assertSee($currency.'5150', false)
            ->assertSee($currency.'5000', false)
            ->assertSee('invoice-org@example.test', false);

        $this->get($printUrl)->assertOk()
            ->assertSee('window.print();', false)
            ->assertSee('INV-123', false)
            ->assertSee('Test VAT', false)
            ->assertSee($currency.'5000', false)
            ->assertDontSee('https://stackpath.bootstrapcdn.com', false)
            ->assertDontSee('https://code.jquery.com', false)
            ->assertDontSee('https://use.fontawesome.com', false);

        $this->actingAs($organizer)->get($invoiceUrl)->assertOk()
            ->assertDontSee('invoice-org@example.test', false);

        $this->actingAs($otherOrganizer)->get($detailUrl)->assertForbidden();
        $this->actingAs($otherOrganizer)->get($invoiceUrl)->assertForbidden();
        $this->actingAs($otherOrganizer)->get($printUrl)->assertForbidden();
        $this->actingAs($this->userWithoutRole())->get($detailUrl)->assertForbidden();
    }

    #[Test]
    public function ticket_print_keeps_the_qr_code_and_scanned_state()
    {
        $this->activateLicense();
        $admin = $this->admin();
        $organizer = $this->organizer();
        $otherOrganizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Ticket Print Event']);
        $ticket = $this->ticket($event);
        $customer = $this->customer();
        $order = $this->order($event, $ticket, $customer, $organizer, ['payment_type' => 'LOCAL']);
        $usedChild = $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-ORDER-123', 'status' => 1]);
        $unusedChild = $this->orderChild($order, $ticket, $customer, ['ticket_number' => 'QR-ORDER-456', 'status' => 0]);

        $this->actingAs($admin)->get(url('get-code/'.$usedChild->id))->assertOk()
            ->assertSee('ds-admin')
            ->assertSee('Ticket Print Event', false)
            ->assertSee('QR-ORDER-123', false)
            ->assertSee(url('images/scan.png'), false)
            ->assertSee('QR code for QR-ORDER-123', false);

        $this->get(url('get-code/'.$unusedChild->id))->assertOk()
            ->assertSee('QR-ORDER-456', false)
            ->assertDontSee(url('images/scan.png'), false);

        $this->actingAs($organizer)->get(url('get-code/'.$usedChild->id))->assertOk();

        $this->actingAs($otherOrganizer)->get(url('get-code/'.$usedChild->id))->assertForbidden();
    }
}
