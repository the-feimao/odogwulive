<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CheckoutCalculator
{
    /**
     * Ensure the ticket is still sellable for this event.
     */
    public function assertSellable(Event $event, Ticket $ticket): void
    {
        if (
            (int) $ticket->event_id !== (int) $event->id
            || (int) $ticket->status !== 1
            || (int) $ticket->is_deleted !== 0
            || (int) $event->status !== 1
            || (int) $event->is_deleted !== 0
        ) {
            throw ValidationException::withMessages([
                'ticket_id' => 'This ticket is no longer available.',
            ]);
        }
    }

    /**
     * Remaining seats for a ticket, accounting for all existing orders.
     */
    public function availableQuantity(Ticket $ticket): int
    {
        $sold = (int) Order::where('ticket_id', $ticket->id)->sum('quantity');

        return max(0, (int) $ticket->quantity - $sold);
    }

    /**
     * Reject quantities above the per-order limit or remaining availability.
     */
    public function assertQuantityAvailable(Ticket $ticket, int $quantity): void
    {
        $limit = min(
            max(1, (int) $ticket->ticket_per_order),
            $this->availableQuantity($ticket)
        );

        if ($quantity < 1 || $quantity > $limit) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity is not available for this ticket.',
            ]);
        }
    }

    /**
     * Resolve and validate a coupon against the event and current date.
     */
    public function validateCoupon($couponId, Event $event): ?Coupon
    {
        if (empty($couponId)) {
            return null;
        }

        $today = Carbon::today()->toDateString();

        $coupon = Coupon::whereKey($couponId)
            ->where('user_id', $event->user_id)
            ->where('event_id', $event->id)
            ->where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->whereColumn('use_count', '<', 'max_use')
            ->first();

        if (!$coupon) {
            throw ValidationException::withMessages([
                'coupon_id' => 'This coupon is not valid for the selected event.',
            ]);
        }

        return $coupon;
    }

    /**
     * Recalculate every monetary field the browser is not allowed to choose.
     *
     * @return array{subtotal:int,discount:int,tax:int,payment:int,commission:int,taxes:Collection}
     */
    public function calculate(Event $event, Ticket $ticket, int $quantity, ?Coupon $coupon, string $paymentType): array
    {
        $isFree = $paymentType === 'FREE' || (int) $ticket->price === 0;
        $subtotal = $isFree ? 0 : (int) round((float) $ticket->price * $quantity);

        $discount = 0;
        if ($coupon && !$isFree) {
            $discount = (int) round($subtotal * ((float) $coupon->discount / 100));
            $discount = max(0, min($discount, $subtotal));
        }

        $taxes = $isFree
            ? collect()
            : Tax::where([
                'user_id' => $event->user_id,
                'status' => 1,
                'allow_all_bill' => 1,
            ])->orderBy('id')->get(['id', 'price']);

        $tax = (int) $taxes->sum('price');
        $payment = max(0, $subtotal - $discount + $tax);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'payment' => $payment,
            'commission' => $this->commission($payment, $tax, $paymentType),
            'taxes' => $taxes,
        ];
    }

    /**
     * Gateway amount in the units expected by Stripe.
     */
    public function gatewayAmount(int $payment): int
    {
        $currency = Setting::find(1)->currency;

        return in_array($currency, ['USD', 'EUR'], true) ? $payment * 100 : $payment;
    }

    private function commission(int $payment, int $tax, string $paymentType): int
    {
        if ($paymentType === 'FREE') {
            return 0;
        }

        $setting = Setting::find(1, ['org_commission_type', 'org_commission']);
        if (!$setting) {
            return 0;
        }

        if ($setting->org_commission_type === 'percentage') {
            return (int) round(($payment - $tax) * ((float) $setting->org_commission / 100));
        }

        if ($setting->org_commission_type === 'amount') {
            return (int) $setting->org_commission;
        }

        return 0;
    }
}
