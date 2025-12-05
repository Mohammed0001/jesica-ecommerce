# BOSTA Webhook Service Documentation

## Overview

The `BostaWebhookService` handles incoming webhook notifications from BOSTA's shipping API. It processes status updates, tracking events, and synchronizes shipment and order statuses automatically.

## Features

- **Secure Signature Verification**: Validates webhook authenticity using HMAC-SHA256
- **Flexible Payload Parsing**: Handles various BOSTA webhook payload formats
- **Automatic Status Mapping**: Maps BOSTA states to internal shipment statuses
- **Order Status Sync**: Automatically updates order status based on shipment events
- **Comprehensive Logging**: Logs all webhook events to `api_logs` table
- **Tracking History**: Maintains detailed event timeline for each shipment
- **Error Handling**: Graceful error handling with detailed logging

## Webhook Events

### Supported Event Types

| BOSTA Event | Internal Status | Description |
|-------------|-----------------|-------------|
| `delivery:created` | `created` | Shipment created in BOSTA system |
| `delivery:picked_up` | `in_transit` | Package picked up from sender |
| `delivery:in_transit` | `in_transit` | Package in transit to destination |
| `delivery:out_for_delivery` | `out_for_delivery` | Out for final delivery |
| `delivery:delivered` | `delivered` | Successfully delivered |
| `delivery:returned` | `returned` | Returned to sender |
| `delivery:cancelled` | `cancelled` | Shipment cancelled |
| `delivery:failed` | `failed` | Delivery failed |

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# BOSTA Webhook Configuration
BOSTA_WEBHOOK_SECRET=your-webhook-secret-from-bosta-dashboard
```

### Get Webhook Secret

1. Login to [BOSTA Dashboard](https://business.bosta.co/)
2. Navigate to **Settings** → **Webhooks**
3. Create or view webhook configuration
4. Copy the **Webhook Secret**
5. Add to `.env` file

### Configure Webhook URL

In BOSTA dashboard, set your webhook URL to:

```
Production: https://yourdomain.com/webhooks/bosta
Staging: https://staging.yourdomain.com/webhooks/bosta
Local (ngrok): https://your-ngrok-url.ngrok.io/webhooks/bosta
```

**Important**: The webhook URL must be publicly accessible (HTTPS required for production)

## Usage

### Webhook Endpoint

The webhook is already configured in `routes/web.php`:

```php
Route::post('/webhooks/bosta', [ShipmentController::class, 'webhook'])
    ->name('webhooks.bosta');
```

### Manual Processing (for testing)

```php
use App\Services\BostaWebhookService;
use Illuminate\Http\Request;

$webhookService = new BostaWebhookService();

// Create a test request
$request = Request::create('/webhooks/bosta', 'POST', [
    'trackingNumber' => '17294525',
    '_id' => 'yGs1Rp6c0MsVTxZkJVjJA',
    'state' => 'delivered',
    'timestamp' => now()->toISOString(),
]);

// Process webhook
$result = $webhookService->processWebhook($request);

if ($result['success']) {
    echo "Webhook processed successfully!\n";
    echo "Shipment ID: " . $result['shipment']->id . "\n";
    echo "Status: " . $result['shipment']->status . "\n";
} else {
    echo "Error: " . $result['message'] . "\n";
}
```

## Webhook Payload Examples

### Example 1: Delivery Created

```json
{
  "type": "delivery:created",
  "trackingNumber": "17294525",
  "_id": "yGs1Rp6c0MsVTxZkJVjJA",
  "state": "created",
  "timestamp": "2025-12-05T10:30:00.000Z",
  "message": "Delivery created successfully"
}
```

### Example 2: Out for Delivery

```json
{
  "type": "delivery:out_for_delivery",
  "trackingNumber": "17294525",
  "_id": "yGs1Rp6c0MsVTxZkJVjJA",
  "state": "out for delivery",
  "timestamp": "2025-12-05T14:00:00.000Z",
  "location": "Cairo Hub",
  "hub": "Cairo Main",
  "message": "Package is out for delivery"
}
```

### Example 3: Delivered

```json
{
  "type": "delivery:delivered",
  "trackingNumber": "17294525",
  "_id": "yGs1Rp6c0MsVTxZkJVjJA",
  "state": "delivered",
  "timestamp": "2025-12-05T16:30:00.000Z",
  "location": "Customer Address",
  "message": "Package delivered successfully",
  "signature": "https://bosta.co/signature.png"
}
```

## Status Mapping

### BOSTA States → Internal Statuses

```php
'created', 'scheduled for pickup', 'pending pickup' 
    → STATUS_CREATED

'picking up', 'picked up', 'in transit', 'received at warehouse', 
'at warehouse', 'in hub', 'transferred' 
    → STATUS_IN_TRANSIT

'out for delivery', 'with courier' 
    → STATUS_OUT_FOR_DELIVERY

'delivered', 'delivered successfully' 
    → STATUS_DELIVERED

'returned', 'return to sender', 'returning', 'returned to warehouse' 
    → STATUS_RETURNED

'cancelled', 'canceled' 
    → STATUS_CANCELLED

'failed delivery', 'delivery failed', 'exception', 'on hold' 
    → STATUS_FAILED
```

## Order Status Synchronization

The webhook service automatically updates order status based on shipment status:

| Shipment Status | Order Status Update | Condition |
|----------------|---------------------|-----------|
| `in_transit` or `out_for_delivery` | `shipped` | If order status is `processing` |
| `delivered` | `delivered` | If order status is not already `delivered` |
| `cancelled` or `returned` | (logged only) | Manual review recommended |

## Security

### Signature Verification

The service verifies webhook authenticity using HMAC-SHA256:

```php
// Signature calculation
$signature = hash_hmac('sha256', $request->getContent(), $webhookSecret);

// Secure comparison
hash_equals($expectedSignature, $receivedSignature);
```

### Headers Checked

- `X-Bosta-Signature`
- `X-BOSTA-Signature`
- `x-bosta-signature`

**Note**: If `BOSTA_WEBHOOK_SECRET` is not configured, signature verification is skipped (not recommended for production).

## Logging

### API Logs

All webhook events are logged to `api_logs` table:

```php
[
    'service' => 'bosta',
    'method' => 'POST',
    'endpoint' => '/webhooks/bosta',
    'request_data' => [...],
    'response_data' => [...],
    'status_code' => 200,
    'duration' => 0.123,
    'loggable_type' => 'App\Models\Shipment',
    'loggable_id' => 1,
]
```

### Application Logs

Check `storage/logs/laravel.log` for detailed webhook processing:

```
[2025-12-05 10:30:00] local.INFO: BOSTA webhook received
[2025-12-05 10:30:00] local.INFO: Shipment status updated
[2025-12-05 10:30:00] local.INFO: Order status updated from shipment webhook
```

## Testing Webhooks

### Local Testing with ngrok

1. Install ngrok: `https://ngrok.com/download`
2. Start Laravel: `php artisan serve`
3. Start ngrok: `ngrok http 8000`
4. Copy ngrok URL (e.g., `https://abc123.ngrok.io`)
5. Add webhook in BOSTA dashboard: `https://abc123.ngrok.io/webhooks/bosta`
6. Test by creating a shipment or updating status in BOSTA

### Manual Webhook Test

Send a POST request to test the endpoint:

```bash
curl -X POST http://localhost:8000/webhooks/bosta \
  -H "Content-Type: application/json" \
  -H "X-Bosta-Signature: test-signature" \
  -d '{
    "trackingNumber": "17294525",
    "_id": "yGs1Rp6c0MsVTxZkJVjJA",
    "state": "delivered",
    "timestamp": "2025-12-05T10:30:00.000Z"
  }'
```

### Test Event Detection

The service can detect test events:

```php
if ($webhookService->isTestEvent($payload)) {
    // Handle test event
    return response()->json(['test' => true]);
}
```

## Troubleshooting

### Webhook Not Received

1. **Check URL accessibility**: Ensure webhook URL is publicly accessible
2. **Verify HTTPS**: Production requires HTTPS
3. **Check firewall**: Ensure server allows incoming POST requests
4. **Review BOSTA dashboard**: Verify webhook is configured and active

### Signature Verification Failed

1. **Check secret**: Verify `BOSTA_WEBHOOK_SECRET` matches dashboard
2. **Review headers**: Check which header BOSTA is using
3. **Test without secret**: Temporarily disable by removing from `.env`

### Shipment Not Found

1. **Check tracking number**: Verify tracking number matches database
2. **Check delivery ID**: Verify BOSTA delivery ID is saved
3. **Review webhook payload**: Check what identifiers BOSTA is sending

### Order Status Not Updating

1. **Check shipment status**: Verify shipment status updated correctly
2. **Review conditions**: Check order status update conditions in `updateOrderStatus()`
3. **Check logs**: Review `storage/logs/laravel.log` for status update logs

## Advanced Usage

### Custom Event Handlers

Extend the service to handle custom events:

```php
namespace App\Services;

class CustomBostaWebhookService extends BostaWebhookService
{
    protected function processEvent(Shipment $shipment, array $webhookData): void
    {
        parent::processEvent($shipment, $webhookData);
        
        // Custom logic
        if ($webhookData['type'] === 'delivery:exception') {
            // Send admin notification
            \Mail::to('admin@example.com')->send(
                new ShipmentExceptionNotification($shipment)
            );
        }
    }
}
```

### Event Broadcasting

Broadcast shipment updates to frontend:

```php
protected function processEvent(Shipment $shipment, array $webhookData): void
{
    parent::processEvent($shipment, $webhookData);
    
    // Broadcast to frontend
    broadcast(new ShipmentStatusUpdated($shipment));
}
```

### Customer Notifications

Send email notifications on status changes:

```php
protected function updateOrderStatus(Shipment $shipment): void
{
    parent::updateOrderStatus($shipment);
    
    // Send email notification
    if ($shipment->status === Shipment::STATUS_DELIVERED) {
        \Mail::to($shipment->order->user->email)->send(
            new OrderDeliveredNotification($shipment->order)
        );
    }
}
```

## API Reference

### BostaWebhookService Methods

#### `processWebhook(Request $request): array`
Main method to process incoming webhook. Returns:
```php
[
    'success' => bool,
    'message' => string,
    'shipment' => Shipment|null
]
```

#### `verifySignature(Request $request): bool`
Verifies webhook signature. Returns `true` if valid.

#### `extractWebhookData(array $payload): ?array`
Extracts relevant data from webhook payload.

#### `findShipment(array $webhookData): ?Shipment`
Finds shipment by tracking number or delivery ID.

#### `mapBostaStateToStatus(?string $bostaState): ?string`
Maps BOSTA state to internal status constant.

#### `getEventType(array $payload): ?string`
Gets event type from webhook payload.

#### `isTestEvent(array $payload): bool`
Checks if webhook is a test event.

## Best Practices

1. **Always configure webhook secret** in production
2. **Monitor webhook logs** regularly for failures
3. **Set up alerts** for webhook processing errors
4. **Test thoroughly** in staging before production
5. **Handle edge cases** for partial or malformed data
6. **Keep tracking history** for audit trails
7. **Document custom modifications** to the service

## Related Files

- `app/Services/BostaWebhookService.php` - Main webhook service
- `app/Http/Controllers/ShipmentController.php` - Webhook endpoint
- `app/Models/Shipment.php` - Shipment model
- `app/Models/ApiLog.php` - API logging model
- `routes/web.php` - Webhook route definition
- `config/services.php` - BOSTA configuration

## Support

For BOSTA webhook issues:
- BOSTA Documentation: https://docs.bosta.co/
- BOSTA Support: support@bosta.co
- Dashboard: https://business.bosta.co/

For application issues:
- Check logs: `storage/logs/laravel.log`
- Review API logs: `api_logs` table
- Enable debug mode: `APP_DEBUG=true` in `.env`
