# Paystack on a local machine

This project still uses the Paystack redirect callback. A webhook is now
supported as an additional, more reliable way to complete orders.

## HTTPS / cURL certificates on Windows

The local PHP install has no `curl.cainfo` or `openssl.cafile`, so Paystack
initialization fails with:

```text
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

Do **not** change the machine-wide `php.ini` just to run this project. A public
CA bundle is kept at `certs/cacert.pem`, and the app now passes it directly to
the Paystack package's Guzzle client through
`App\Support\PaystackWithLocalCa`. Plain `php artisan serve` can therefore use
Paystack without special PHP flags.

You can still start Laravel with the bundle applied process-wide:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\serve-with-paystack.ps1
```

Extra Artisan arguments are passed through, for example:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\serve-with-paystack.ps1 --host=0.0.0.0 --port=8000
```

Use `0.0.0.0` when testing from a phone. `127.0.0.1` only accepts connections
from this computer, so Expo Go and a phone browser cannot reach it.

The bundle comes from <https://curl.se/ca/cacert.pem>. It is public data, not a
secret, and can be refreshed by downloading that file again. Set
`PAYSTACK_CA_BUNDLE` in `.env` only if you want to point at a different file.

## Callback URL

You do not need to add a callback URL in the Paystack dashboard. The app sends
the callback URL with each transaction initialization request:

```text
route('paystack.callback')  ->  http://localhost/paystack/callback
```

When the app is deployed later, set `APP_URL` to the real public URL in `.env`
and the callback becomes `https://your-domain/paystack/callback`.

## Webhook (optional but recommended)

The webhook endpoint is:

```text
POST /paystack/webhook
```

It is public and CSRF-exempt, and accepts only signed requests. It verifies the
raw request body using either:

- the newer `Paystack-Signature` header (`t=<unix>,v1=<kid>:<hex>`) with a
  `whsec_...` signing secret, or
- the legacy `x-paystack-signature` header signed with your Paystack secret key.

To use the newer format:

1. In Paystack dashboard, open **Developers -> API configuration** and generate
   a webhook signing secret.
2. Add it to your local `.env` (never to `.env.example` or Git):

   ```dotenv
   PAYSTACK_WEBHOOK_SECRET=whsec_your_secret_here
   ```

3. If you use config cache, run `php artisan config:clear`.
4. Configure the dashboard webhook URL to `https://your-domain/paystack/webhook`
   when the site is publicly deployed. Paystack cannot reach `localhost`.

The handler processes `charge.success` for orders and `transfer.*` for
organisation payouts. It checks the transaction reference, status, amount and
optional `metadata.order_id`, then completes the order inside a locked
transaction. Retried events are acknowledged and ignored, so a resend cannot
double-complete an order.

If no `PAYSTACK_WEBHOOK_SECRET` is set, legacy `x-paystack-signature` events
still work using the existing Paystack secret key. If neither signature is
valid, the endpoint returns `401`.

## Organisation payouts (direct transfers)

Payouts use Paystack's **transfer** API rather than checkout splits, so the
existing checkout flow and API contracts are unchanged.

1. An organisation opens `/profile`, selects the bank and enters its account
   number. It can optionally verify the account with Paystack. The account
   number is encrypted at rest and is never returned by the user API.
2. The super admin opens `/admin-report/settlement`. Admins see only the bank
   name, a masked account number and the Paystack status for each organisation.
3. The **Pay with Paystack** button posts only `user_id`. The amount is always
   recalculated on the server as unpaid `org_commission + tax`; any `payment`
   value supplied by the browser is ignored.
4. The app creates and caches a Paystack transfer recipient, starts a transfer
   from the Paystack balance using a `PAYOUT-{organization}-{random}` reference,
   and records a settlement row. The legacy manual payment button is still
   available and unchanged.
5. `transfer.success`, `transfer.failed` and `transfer.reversed` webhooks are
   handled by the same signed `/paystack/webhook` endpoint. The webhook amount
   must match the saved settlement exactly. Completion is idempotent.

Use **test keys** while developing. Test transfers do not move real money. A
public URL is required before Paystack can deliver payout webhooks; on
`localhost`, an immediate `success` transfer response completes the payout,
but a `pending` transfer will remain pending until a webhook can be received.

Do not switch to live keys or click the payout button on production data until
the bank details and webhook have been verified in Paystack's dashboard.

You can verify the webhook and payout logic locally without Paystack reaching your machine:

```powershell
php artisan test --filter=PaystackCheckoutTest
```
