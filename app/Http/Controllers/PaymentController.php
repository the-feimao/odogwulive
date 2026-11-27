<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Settlement;
use App\Models\Setting;
use App\Support\SettlementPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Paystack;

class PaymentController extends Controller
{
    /**
     * Start a Paystack payment for an order created by the web checkout.
     * The legacy POST payload is still accepted for existing callers.
     */
    public function redirectToGateway(Request $request)
    {
        $order = null;

        try {
            if ($request->filled('order')) {
                $order = Order::whereKey($request->input('order'))
                    ->where('payment_type', 'PAYSTACK')
                    ->where('payment_status', 0)
                    ->firstOrFail();

                if ((int) $order->customer_id !== (int) Auth::guard('appuser')->id()) {
                    abort(403);
                }

                if ((int) (PaymentSetting::find(1)?->paystack ?? 0) !== 1) {
                    throw new \RuntimeException('Paystack is not enabled.');
                }

                $customer = $order->customer;
                $reference = $order->payment_token ?: 'ODG-'.Str::upper(Str::random(12));
                $order->payment_token = $reference;
                $order->save();

                // Paystack expects the amount in the currency subunit (kobo,
                // cents, etc.), while orders store whole currency units.
                $amount = (int) round((float) $order->payment * 100);

                if ($amount <= 0) {
                    throw new \RuntimeException('The order has no payable amount.');
                }

                return Paystack::getAuthorizationUrl([
                    'amount' => $amount,
                    'reference' => $reference,
                    'email' => $customer->email,
                    'first_name' => $customer->first_name ?: $customer->name,
                    'last_name' => $customer->last_name ?: '',
                    'callback_url' => route('paystack.callback'),
                    'currency' => Setting::find(1)?->currency ?: 'NGN',
                    'metadata' => json_encode([
                        'order_id' => $order->id,
                        'custom_fields' => [[
                            'display_name' => 'Order',
                            'variable_name' => 'order_id',
                            'value' => $order->id,
                        ]],
                    ]),
                ])->redirectNow();
            }

            // Backwards-compatible path for any existing form that posts the
            // Paystack fields directly.
            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }

            report($e);

            // Never send the user to the tickets page on a failed init: it
            // looks like a completed purchase, while the order is still
            // pending. Give them an explicit retry for the same order.
            if ($order) {
                return response()->view('frontend.payment-error', [
                    'order' => $order,
                    'title' => __('Payment could not be started'),
                ], 502);
            }

            return Redirect::to('my-tickets')->with('error', __('Unable to start Paystack checkout. Please try again.'));
        }
    }

    /**
     * Verify the callback with Paystack's API before marking an order paid.
     */
    public function handleGatewayCallback(Request $request)
    {
        $reference = (string) ($request->input('reference') ?: $request->input('trxref') ?: '');

        if ($reference === '') {
            return Redirect::to('my-tickets')->with('error', __('No payment reference was returned.'));
        }

        try {
            $order = Order::where('payment_type', 'PAYSTACK')
                ->where('payment_token', $reference)
                ->first();

            if (! $order) {
                return Redirect::to('my-tickets')->with('error', __('We could not match that payment to an order.'));
            }

            // The package verifies the transaction against the trxref query
            // parameter, so make sure it matches the reference we generated.
            $request->merge(['trxref' => $reference]);
            $paymentDetails = Paystack::getPaymentData();

            if (! $this->completeOrderFromPaystackData($order, $paymentDetails['data'] ?? [])) {
                throw new \RuntimeException('Paystack payment verification failed.');
            }

            return Redirect::to('my-tickets')->with('status', __('Payment verified. Your tickets are ready.'));
        } catch (\Throwable $e) {
            report($e);

            return Redirect::to('my-tickets')->with('error', __('Unable to verify your Paystack payment. Please contact support if you were charged.'));
        }
    }

    /**
     * Receive signed Paystack webhook events. This is additive: the redirect
     * callback still works, and webhooks make completion more reliable when a
     * customer closes the browser before the callback runs.
     */
    public function handleWebhook(Request $request)
    {
        $rawBody = $request->getContent();

        if (! $this->verifyWebhookSignature($request, $rawBody)) {
            Log::warning('Rejected a Paystack webhook with an invalid signature.');

            return response()->json(['received' => false], 401);
        }

        try {
            $event = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['received' => false], 400);
        }

        $eventName = (string) ($event['event'] ?? '');

        if (str_starts_with($eventName, 'transfer.')) {
            return $this->handleTransferWebhook($eventName, (array) ($event['data'] ?? []));
        }

        // Only successful charges can complete an order. Every other event is
        // acknowledged so Paystack does not keep retrying it.
        if ($eventName !== 'charge.success') {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $data = $event['data'] ?? [];
        $reference = (string) ($data['reference'] ?? '');

        if ($reference === '') {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $order = Order::where('payment_type', 'PAYSTACK')
            ->where('payment_token', $reference)
            ->first();

        if (! $order) {
            Log::warning('Paystack webhook reference did not match an order.', ['reference' => $reference]);

            return response()->json(['received' => true, 'ignored' => true]);
        }

        $metadataOrderId = $data['metadata']['order_id'] ?? null;
        if ($metadataOrderId !== null && (int) $metadataOrderId !== (int) $order->id) {
            Log::warning('Paystack webhook metadata did not match the order.', [
                'order_id' => $order->id,
                'metadata_order_id' => $metadataOrderId,
            ]);

            return response()->json(['received' => true, 'ignored' => true]);
        }

        if ((int) $order->payment_status === 1) {
            // Paystack resends events; completing the order is idempotent.
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        if (! $this->completeOrderFromPaystackData($order, $data)) {
            Log::warning('Paystack webhook failed order verification.', [
                'order_id' => $order->id,
                'reference' => $reference,
            ]);

            return response()->json(['received' => true, 'ignored' => true]);
        }

        return response()->json(['received' => true, 'completed' => true]);
    }

    /**
     * Verify the same Paystack transaction data used by the redirect callback.
     */
    private function completeOrderFromPaystackData(Order $order, array $data): bool
    {
        $reference = (string) ($data['reference'] ?? $order->payment_token ?? '');

        if ($reference === '' || strtolower($reference) !== strtolower((string) $order->payment_token)) {
            return false;
        }

        if (strtolower((string) ($data['status'] ?? '')) !== 'success') {
            return false;
        }

        $expectedAmount = (int) round((float) $order->payment * 100);
        if ((int) ($data['amount'] ?? 0) !== $expectedAmount) {
            return false;
        }

        DB::transaction(function () use ($order, $reference) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if ($locked && (int) $locked->payment_status !== 1) {
                $locked->payment_status = 1;
                $locked->order_status = 'Complete';
                $locked->payment_token = $reference;
                $locked->save();
            }
        });

        return true;
    }

    /**
     * Signed Paystack transfer events settle organisation payouts. The amount
     * is checked against the settlement we created so a mismatched event can
     * never mark unrelated orders as paid.
     */
    private function handleTransferWebhook(string $eventName, array $data)
    {
        $reference = (string) ($data['reference'] ?? '');
        if ($reference === '') {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $settlement = Settlement::where('payment_type', 'PAYSTACK')
            ->where('payment_token', $reference)
            ->first();

        if (! $settlement) {
            Log::warning('Paystack transfer webhook reference did not match a settlement.', ['reference' => $reference]);

            return response()->json(['received' => true, 'ignored' => true]);
        }

        if (isset($data['amount']) && (int) $data['amount'] !== (int) round((float) $settlement->payment * 100)) {
            Log::warning('Paystack transfer webhook amount did not match the settlement.', ['settlement_id' => $settlement->id]);

            return response()->json(['received' => true, 'ignored' => true]);
        }

        $payout = app(SettlementPayout::class);

        if ($eventName === 'transfer.success') {
            $completed = $payout->complete($settlement);

            return response()->json(array_merge(
                ['received' => true],
                $completed ? ['completed' => true] : ['duplicate' => true]
            ));
        }

        if (in_array($eventName, ['transfer.failed', 'transfer.reversed'], true)) {
            $payout->fail($settlement, (string) ($data['reason'] ?? 'Paystack reported a failed transfer.'));

            return response()->json(['received' => true, 'failed' => true]);
        }

        return response()->json(['received' => true, 'ignored' => true]);
    }

    /**
     * Support both Paystack signature formats without trusting parsed JSON.
     */
    private function verifyWebhookSignature(Request $request, string $rawBody): bool
    {
        $signedHeader = (string) $request->header('Paystack-Signature', '');

        if ($signedHeader !== '') {
            return $this->verifyTimestampedWebhookSignature($rawBody, $signedHeader);
        }

        $legacySignature = (string) $request->header('x-paystack-signature', '');
        $secretKey = (string) config('paystack.secretKey', '');

        if ($legacySignature === '' || $secretKey === '') {
            return false;
        }

        if (! preg_match('/^[a-f0-9]{128}$/i', $legacySignature)) {
            return false;
        }

        return hash_equals(
            hash_hmac('sha512', $rawBody, $secretKey, true),
            hex2bin($legacySignature)
        );
    }

    /**
     * Paystack's newer Paystack-Signature header: t=<unix>,v1=<kid>:<hex>.
     */
    private function verifyTimestampedWebhookSignature(string $rawBody, string $header): bool
    {
        $secrets = config('paystack.webhookSecrets', []);
        if (! is_array($secrets) || $secrets === []) {
            return false;
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $part) {
            $separator = strpos($part, '=');
            if ($separator === false) {
                continue;
            }

            $key = strtolower(trim(substr($part, 0, $separator)));
            $value = trim(substr($part, $separator + 1));

            if ($key === 't') {
                if ($timestamp !== null || ! ctype_digit($value)) {
                    return false;
                }

                $timestamp = (int) $value;
                continue;
            }

            if ($key === 'v1') {
                $colon = strpos($value, ':');
                if ($colon !== false) {
                    $signatures[] = substr($value, $colon + 1);
                }
            }
        }

        if ($timestamp === null || $signatures === []) {
            return false;
        }

        // Prevent replay of captured events outside a 5 minute window.
        if (abs(time() - $timestamp) > 300) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$rawBody;

        foreach ($secrets as $secret) {
            $expected = hash_hmac('sha512', $signedPayload, (string) $secret, true);

            foreach ($signatures as $signature) {
                if (! preg_match('/^[a-f0-9]{128}$/i', $signature)) {
                    continue;
                }

                if (hash_equals($expected, hex2bin($signature))) {
                    return true;
                }
            }
        }

        return false;
    }
}
