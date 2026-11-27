<?php

namespace App\Support;

use App\Models\Order;
use QrCode;

/**
 * Builds the QR value used by the scanner without changing the ticket number
 * or any API payload. The QR content is the existing OrderChild ticket number.
 */
class TicketQr
{
    public function generate(string $ticketNumber, int $size = 160): string
    {
        return QrCode::size($size)->generate($ticketNumber);
    }

    public function isPaid(Order $order): bool
    {
        return (int) $order->payment_status === 1 || $order->payment_type === 'FREE';
    }

    /**
     * Attach generated QR markup to a collection of order children.
     * Returns whether the order is eligible for scannable tickets.
     */
    public function attach(Order $order, $children, int $size = 160): bool
    {
        $paid = $this->isPaid($order);

        foreach ($children as $child) {
            $child->qrCode = $paid
                ? $this->generate((string) $child->ticket_number, $size)
                : null;
        }

        return $paid;
    }
}
