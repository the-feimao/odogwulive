<?php

namespace App\Support;

use App\Models\Order;

final class PublicPaymentLink
{
    private const DEFAULT_TTL_MINUTES = 1440;

    /**
     * A short-lived capability token for the public Flutterwave payment page.
     *
     * The token is bound to immutable order fields, so it cannot be reused for
     * another order or customer. It is carried in the URL path rather than the
     * query string because payment gateways append their own query parameters
     * on redirect.
     */
    public static function token(Order $order): string
    {
        $createdAt = $order->created_at ? $order->created_at->timestamp : '';

        return hash_hmac(
            'sha256',
            implode('|', [$order->id, $order->customer_id, $createdAt]),
            (string) config('app.key')
        );
    }

    public static function url(Order $order): string
    {
        return route('frontend.publicPayment', [
            'order' => $order->id,
            'token' => self::token($order),
        ]);
    }

    public static function valid(Order $order, string $token): bool
    {
        if (!hash_equals(self::token($order), $token) || !$order->created_at) {
            return false;
        }

        $ttl = (int) config('app.public_payment_link_ttl_minutes', self::DEFAULT_TTL_MINUTES);

        return $order->created_at->greaterThanOrEqualTo(now()->subMinutes(max(1, $ttl)));
    }
}
