Paymob Integration

This project includes a basic Paymob gateway implementation at `app/Services/Gateways/PaymobGateway.php` and wiring in `app/Services/PaymentService.php`.

Setup (env variables)
- `PAYMENT_PROVIDER` = `paymob` (or `mock` for local development)
- `PAYMOB_API_KEY` = your Paymob API key
- `PAYMOB_INTEGRATION_ID` = your Paymob integration ID (integer)
- `PAYMOB_IFRAME_ID` = iframe ID provided in Paymob dashboard
- `PAYMOB_BASE_URL` = optional, defaults to `https://accept.paymob.com`

Behavior
- When using Paymob, the payment flow creates a Paymob order and a payment token
  and returns an `iframe_url` in the payment meta. The controller redirects the
  user to that URL so they can complete the payment in Paymob's iframe.
- The `PaymentService` now records payment `status` returned by the gateway
  (e.g. `pending` or `succeeded`).
- The app does NOT automatically decrement stock or clear the cart for pending
  gateway payments. You must implement a webhook or a polling mechanism to
  confirm payment success and complete the order (decrement stock, mark order
  paid, clear cart).

Recommended next steps
- Implement a webhook endpoint to receive Paymob payment notifications and
  update the corresponding `Payment` and `Order` records.
- Secure the webhook and verify signatures according to Paymob docs.
- Add retry/error handling and logging for production.

Testing locally
- For local development use `PAYMENT_PROVIDER=mock` to simulate credit card
  processing without calling the real Paymob API.

Webhook example
---------------
Paymob will POST payment notifications to the webhook URL. Example curl to
simulate a webhook payload (replace `http://your-site.test` with your site):

```bash
curl -X POST "http://your-site.test/payments/webhook/paymob" \
  -H "Content-Type: application/json" \
  -d '{"id":"SAMPLE_TX_ID","success":true,"obj":{"is_paid":true}}'
```

The webhook handler attempts to match the incoming payload to an existing
`Payment` record using `provider_transaction_id` or JSON meta fields. When a
successful payment is detected the Order status is updated (`paid_deposit` or
`paid_full`) and stock quantities are decremented. The webhook route is
exempted from CSRF verification in `routes/web.php`.

Security
--------
In production you must validate webhook authenticity. Paymob supports signing
webhooks — verify signatures before trusting payloads. The minimal handler in
this repo does not perform signature validation.

Signature verification
----------------------
This integration now supports webhook signature verification using the
`PAYMOB_WEBHOOK_SECRET` env variable. The handler will look for common
signature headers (`X-Paymob-Signature`, `X-Signature`, `X-Hub-Signature`) and
verify HMAC SHA-512 / SHA-256 against the raw request body. Set
`PAYMOB_WEBHOOK_SECRET` in your `.env` to enable verification.

Database migration
------------------
This change adds an `orders.stock_decremented` boolean column to make stock
decrements idempotent. Run migrations after pulling these changes:

```bash
php artisan migrate
```


