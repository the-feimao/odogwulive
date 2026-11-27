<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;

/**
 * Decides when an online event's joining link may be shown publicly.
 *
 * Free online events keep the old behaviour. Once an event has paid tickets,
 * the raw link is only exposed to a signed-in attendee who already has a valid
 * order for that event.
 */
class OnlineEventAccess
{
    public function hasValidLink(Event $event): bool
    {
        return $event->type === 'online'
            && preg_match('#^https?://#i', trim((string) $event->event_link)) === 1;
    }

    public function requiresOrder(Event $event): bool
    {
        if (! $this->hasValidLink($event)) {
            return false;
        }

        return Ticket::query()
            ->where('event_id', $event->id)
            ->where('is_deleted', 0)
            ->where('type', 'paid')
            ->exists();
    }

    public function joinUrl(Event $event, ?int $customerId): ?string
    {
        if (! $this->hasValidLink($event)) {
            return null;
        }

        if (! $this->requiresOrder($event)) {
            return trim((string) $event->event_link);
        }

        if (! $customerId) {
            return null;
        }

        $hasValidOrder = Order::query()
            ->where('event_id', $event->id)
            ->where('customer_id', $customerId)
            ->where(function ($query) {
                $query->where('order_status', '!=', 'Cancel')
                    ->where(function ($paidQuery) {
                        $paidQuery->where('payment_status', 1)
                            ->orWhere('order_status', 'Complete');
                    });
            })
            ->exists();

        return $hasValidOrder ? trim((string) $event->event_link) : null;
    }
}
