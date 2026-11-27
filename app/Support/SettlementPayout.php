<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Server-side organisation payout lifecycle.
 *
 * Amounts are always recalculated from paid orders. The browser is never
 * trusted with a payout amount, and completing a settlement is idempotent so
 * a Paystack webhook retry cannot pay an organisation twice.
 */
class SettlementPayout
{
    public function availableAmount(int $organizationId): int
    {
        $amount = Order::where('organization_id', $organizationId)
            ->where('payment_status', 1)
            ->where(function ($query) {
                $query->whereNull('org_pay_status')->orWhere('org_pay_status', 0);
            })
            ->sum(DB::raw('org_commission + tax'));

        return max(0, (int) round((float) $amount));
    }

    public function start(int $organizationId): Settlement
    {
        $amount = $this->availableAmount($organizationId);

        if ($amount <= 0) {
            throw new \RuntimeException('There is no commission available to pay.');
        }

        do {
            $reference = 'PAYOUT-'.$organizationId.'-'.Str::upper(Str::random(12));
        } while (Settlement::where('payment_token', $reference)->exists());

        return Settlement::create([
            'user_id' => $organizationId,
            'payment' => $amount,
            'payment_token' => $reference,
            'payment_type' => 'PAYSTACK',
            'payment_status' => 0,
            'currency' => DashboardMetrics::currencyCode(),
        ]);
    }

    public function complete(Settlement $settlement): bool
    {
        return DB::transaction(function () use ($settlement) {
            $locked = Settlement::whereKey($settlement->id)->lockForUpdate()->first();

            if (! $locked || (int) $locked->payment_status === 1) {
                return false;
            }

            $locked->payment_status = 1;
            $locked->paid_at = now();
            $locked->failure_reason = null;
            $locked->save();

            Order::where('organization_id', $locked->user_id)
                ->where('payment_status', 1)
                ->where(function ($query) {
                    $query->whereNull('org_pay_status')->orWhere('org_pay_status', 0);
                })
                ->update(['org_pay_status' => 1]);

            return true;
        });
    }

    public function fail(Settlement $settlement, string $reason): void
    {
        DB::transaction(function () use ($settlement, $reason) {
            $locked = Settlement::whereKey($settlement->id)->lockForUpdate()->first();

            if (! $locked || (int) $locked->payment_status === 1) {
                return;
            }

            $locked->payment_status = 2;
            $locked->failure_reason = Str::limit($reason, 240);
            $locked->save();
        });
    }
}
