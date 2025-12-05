# BOSTA Webhook Service - Quick Start Guide

## What's Been Created

✅ **BostaWebhookService** (`app/Services/BostaWebhookService.php`)
- Comprehensive webhook processing service
- Signature verification
- Status mapping
- Order synchronization
- API logging

✅ **Updated ShipmentController** (`app/Http/Controllers/ShipmentController.php`)
- Integrated webhook service
- Simplified webhook handling
- Better error responses

✅ **Test Command** (`app/Console/Commands/TestBostaWebhook.php`)
- Test webhooks locally
- Simulate different statuses
- View results in terminal

✅ **Documentation** (`BOSTA-WEBHOOK.md`)
- Complete webhook documentation
- Examples and troubleshooting
- API reference

## Quick Setup (5 minutes)

### 1. Get Your Webhook Secret

1. Login to [BOSTA Business Dashboard](https://business.bosta.co/)
2. Go to **Settings** → **Webhooks**
3. Copy your **Webhook Secret**

### 2. Configure .env

```env
# Required
BOSTA_API_KEY=your-api-key-here
BOSTA_WEBHOOK_SECRET=your-webhook-secret-here

# Optional (defaults shown)
BOSTA_SANDBOX=true
```

### 3. Set Webhook URL in BOSTA Dashboard

**For Local Testing (using ngrok):**
```bash
# Terminal 1: Start Laravel
php artisan serve

# Terminal 2: Start ngrok
ngrok http 8000

# Use the ngrok URL in BOSTA dashboard:
https://abc123.ngrok.io/webhooks/bosta
```

**For Production:**
```
https://yourdomain.com/webhooks/bosta
```

## Testing

### Test with Command (Recommended for Development)

```bash
# Test delivered status
php artisan bosta:test-webhook 17294525 --status=delivered

# Test in transit status
php artisan bosta:test-webhook 17294525 --status="in transit"

# Test with specific delivery ID
php artisan bosta:test-webhook 17294525 --status=delivered --delivery-id=yGs1Rp6c0MsVTxZkJVjJA
```

### Test with cURL

```bash
curl -X POST http://localhost:8000/webhooks/bosta \
  -H "Content-Type: application/json" \
  -d '{
    "trackingNumber": "17294525",
    "_id": "yGs1Rp6c0MsVTxZkJVjJA",
    "state": "delivered",
    "timestamp": "2025-12-05T10:30:00.000Z"
  }'
```

### Test with Postman

1. Create POST request to `http://localhost:8000/webhooks/bosta`
2. Add header: `Content-Type: application/json`
3. Body (raw JSON):
```json
{
  "trackingNumber": "17294525",
  "_id": "yGs1Rp6c0MsVTxZkJVjJA",
  "state": "delivered",
  "type": "delivery:delivered",
  "timestamp": "2025-12-05T10:30:00.000Z",
  "message": "Package delivered successfully"
}
```

## How It Works

### 1. BOSTA Sends Webhook
When shipment status changes, BOSTA sends POST request to your webhook URL.

### 2. Signature Verification
Service verifies HMAC-SHA256 signature to ensure authenticity.

### 3. Shipment Lookup
Finds shipment by `trackingNumber` or `_id` (delivery ID).

### 4. Status Update
Maps BOSTA state to internal status and updates shipment.

### 5. Order Sync
Automatically updates order status:
- `in_transit` → Order becomes `shipped`
- `delivered` → Order becomes `delivered`

### 6. Logging
Saves to `api_logs` table and `laravel.log`.

## Status Flow

```
Created → In Transit → Out for Delivery → Delivered
              ↓              ↓               ↓
          Returned     Failed Delivery   Cancelled
```

## What Gets Updated

### Shipment Table
- `status` - Current shipment status
- `tracking_history` - JSON array of all events
- `picked_up_at` - When picked up from sender
- `delivered_at` - When delivered to customer
- `cancelled_at` - When cancelled/returned

### Order Table
- `status` - Order status synced from shipment
- `shipped_at` - When order was shipped

### API Logs Table
- Complete webhook request/response
- Processing duration
- Success/failure status

## Common Statuses

| BOSTA State | Internal Status | Order Impact |
|------------|-----------------|--------------|
| `created` | `created` | No change |
| `picked up` | `in_transit` | → `shipped` |
| `in transit` | `in_transit` | → `shipped` |
| `out for delivery` | `out_for_delivery` | → `shipped` |
| `delivered` | `delivered` | → `delivered` |
| `returned` | `returned` | Logged only |
| `cancelled` | `cancelled` | Logged only |

## Monitoring

### Check Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# Filter for webhooks
tail -f storage/logs/laravel.log | grep "BOSTA webhook"
```

### Check Database
```sql
-- Recent webhook logs
SELECT * FROM api_logs 
WHERE service = 'bosta' 
AND endpoint = '/webhooks/bosta'
ORDER BY created_at DESC 
LIMIT 10;

-- Shipment tracking history
SELECT id, tracking_number, status, tracking_history 
FROM shipments 
WHERE tracking_number = '17294525';
```

## Troubleshooting

### "Invalid webhook signature"
→ Check `BOSTA_WEBHOOK_SECRET` matches BOSTA dashboard

### "Shipment not found"
→ Verify tracking number exists in database

### Webhook not received
→ Ensure URL is publicly accessible (use ngrok for local testing)

### Status not updating
→ Check logs for mapping errors: `storage/logs/laravel.log`

## Next Steps

1. ✅ Test webhook with command: `php artisan bosta:test-webhook`
2. ✅ Set up ngrok for local testing
3. ✅ Configure webhook URL in BOSTA dashboard
4. ✅ Create test shipment and verify webhook received
5. ✅ Review logs to ensure proper processing
6. ✅ Test in production with real shipments

## Files Reference

- **Service**: `app/Services/BostaWebhookService.php`
- **Controller**: `app/Http/Controllers/ShipmentController.php`
- **Route**: `routes/web.php` (line ~188)
- **Config**: `config/services.php` (bosta section)
- **Model**: `app/Models/Shipment.php`
- **Test Command**: `app/Console/Commands/TestBostaWebhook.php`
- **Full Docs**: `BOSTA-WEBHOOK.md`

## Support

**BOSTA Support:**
- Documentation: https://docs.bosta.co/
- Email: support@bosta.co
- Dashboard: https://business.bosta.co/

**Application Issues:**
- Check: `storage/logs/laravel.log`
- Database: `api_logs` table
- Debug: Set `APP_DEBUG=true`

---

**Ready to test?** Run: `php artisan bosta:test-webhook 17294525 --status=delivered`
