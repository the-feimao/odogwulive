<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Settlement;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shared money and ticket metrics for the admin and organization dashboards.
 *
 * The commission definition intentionally matches the existing settlement and
 * revenue reports: the organization is owed `org_commission + tax`, while the
 * platform keeps the remainder of a paid order.
 */
class DashboardMetrics
{
    /**
     * @return array{gross_revenue:float,platform_revenue:float,organizer_payable:float,outstanding_payable:float,paid_out:float,tickets_sold:int,paid_orders:int}
     */
    public static function forScope(?int $organizationId = null): array
    {
        $orders = Order::query();
        if ($organizationId !== null) {
            $orders->where('organization_id', $organizationId);
        }

        $totals = (clone $orders)->selectRaw('
            COALESCE(SUM(CASE WHEN payment_status = 1 THEN payment ELSE 0 END), 0) as gross_revenue,
            COALESCE(SUM(CASE WHEN payment_status = 1 THEN org_commission + tax ELSE 0 END), 0) as organizer_payable,
            COALESCE(SUM(CASE WHEN payment_status = 1 THEN payment - org_commission - tax ELSE 0 END), 0) as platform_revenue,
            COALESCE(SUM(CASE WHEN payment_status = 1 AND org_pay_status = 0 THEN org_commission + tax ELSE 0 END), 0) as outstanding_payable,
            COALESCE(SUM(CASE WHEN payment_status = 1 THEN quantity ELSE 0 END), 0) as tickets_sold,
            COALESCE(SUM(CASE WHEN payment_status = 1 THEN 1 ELSE 0 END), 0) as paid_orders
        ')->first();

        $settlements = Settlement::where('payment_status', 1);
        if ($organizationId !== null) {
            $settlements->where('user_id', $organizationId);
        }

        $paidOut = (float) ($settlements->selectRaw('COALESCE(SUM(payment), 0) as paid_out')->first()?->paid_out ?? 0);

        return [
            'gross_revenue' => (float) ($totals->gross_revenue ?? 0),
            'platform_revenue' => (float) ($totals->platform_revenue ?? 0),
            'organizer_payable' => (float) ($totals->organizer_payable ?? 0),
            'outstanding_payable' => (float) ($totals->outstanding_payable ?? 0),
            'paid_out' => $paidOut,
            'tickets_sold' => (int) ($totals->tickets_sold ?? 0),
            'paid_orders' => (int) ($totals->paid_orders ?? 0),
        ];
    }

    /**
     * Six completed months of paid revenue, including empty months.
     *
     * @return array<int,array{key:string,label:string,revenue:float,payable:float}>
     */
    public static function monthly(?int $organizationId = null, int $months = 6): array
    {
        $months = max(1, min(12, $months));
        $start = now()->subMonths($months - 1)->startOfMonth();

        $orders = Order::where('payment_status', 1)
            ->where('created_at', '>=', $start);

        if ($organizationId !== null) {
            $orders->where('organization_id', $organizationId);
        }

        $period = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $rows = (clone $orders)
            ->selectRaw($period.' as period, SUM(payment) as revenue, SUM(org_commission + tax) as payable')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $series = [];
        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $month = Carbon::now()->subMonths($offset);
            $key = $month->format('Y-m');
            $row = $rows->get($key);

            $series[] = [
                'key' => $key,
                'label' => $month->format('M Y'),
                'revenue' => (float) ($row?->revenue ?? 0),
                'payable' => (float) ($row?->payable ?? 0),
            ];
        }

        return $series;
    }

    public static function currencyCode(): string
    {
        return (string) (Setting::find(1)?->currency ?: 'NGN');
    }
}
