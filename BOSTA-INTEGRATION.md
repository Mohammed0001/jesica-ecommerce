# BOSTA Egypt Shipping Integration

This document provides comprehensive instructions for setting up and using the BOSTA shipping integration in your e-commerce application.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [Usage](#usage)
- [Webhooks](#webhooks)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

## Overview

BOSTA is Egypt's leading shipping and logistics provider. This integration provides:
- Automatic shipment creation when orders are confirmed
- Real-time tracking and status updates
- Cash on Delivery (COD) support
- AWB (Air Waybill) label printing
- Webhook notifications for shipment status changes
- Customer tracking portal

## Prerequisites

Before you begin, ensure you have:
1. A BOSTA account (sign up at https://business.bosta.co/)
2. BOSTA API credentials (API Key)
3. Your warehouse/pickup address details
4. Laravel application with order management system

## Installation

### Step 1: Run Database Migration

Run the migration to create the shipments table:

```bash
php artisan migrate
```

This will create the `shipments` table with all necessary fields for tracking BOSTA shipments.

### Step 2: Configure Environment Variables

Add the following variables to your `.env` file:

```env
# BOSTA Shipping Configuration
BOSTA_API_KEY=your-bosta-api-key-here
BOSTA_SANDBOX=true
BOSTA_WEBHOOK_SECRET=your-webhook-secret-here
BOSTA_BUSINESS_LOCATION_ID=

# BOSTA Pickup Address (Your warehouse/store location)
BOSTA_PICKUP_ADDRESS_LINE1="Your store address line 1"
BOSTA_PICKUP_ADDRESS_LINE2="Building/Floor/Apartment"
BOSTA_PICKUP_CITY=Cairo
BOSTA_PICKUP_ZONE="Nasr City"
BOSTA_PICKUP_DISTRICT=""

# BOSTA Package Settings
BOSTA_PACKAGE_TYPE=Parcel
BOSTA_PACKAGE_SIZE=SMALL
BOSTA_PICKUP_SLOT="10:00 to 13:00"
BOSTA_ALLOW_OPEN_PACKAGE=false
```

**Important Notes:**
- Customer addresses must include a valid **phone number** (E.164 format: +201234567890)
- Customer addresses must include a **city** and **district** for proper delivery routing
- Cities must match BOSTA's city names (Cairo, Giza, Alexandria, etc.)
- The system automatically maps city names to BOSTA city IDs (28 major Egyptian cities supported)

### Step 3: Set Up Pickup Location in BOSTA Dashboard

**IMPORTANT**: Before creating any shipments, you must set up a pickup location in your BOSTA dashboard:

1. Log in to [BOSTA Business Dashboard](https://business.bosta.co/) (or [Sandbox](https://stg-business.bosta.co/))
2. Navigate to **Settings** → **Pickup Locations**
3. Click **Add Pickup Location**
4. Enter your warehouse/store details (address, district, building, floor, etc.)
5. Set it as your **Default Location**
6. (Optional) Copy the Location ID and add it to `BOSTA_BUSINESS_LOCATION_ID` in `.env`

> **Note**: If you don't provide `BOSTA_BUSINESS_LOCATION_ID`, BOSTA will use your default pickup location automatically.

### Step 4: Get Your BOSTA API Key

1. Log in to your BOSTA business account
2. Navigate to **Settings** → **API Integration**
3. Click **Request OTP** and enter the code sent to your phone
4. Click **Create API Key** and set:
   - **Name**: Your application name
   - **Permission**: Choose **Read/Write** (minimum) or **Full Access**
5. Copy your API Key (you won't be able to see it again!)
6. Replace `your-bosta-api-key-here` in your `.env` file

> **Important**: Make sure to choose **Read/Write** or **Full Access** permission level. Read-only keys cannot create shipments.

## Configuration

### Package Settings

Configure default package settings in your `.env`:

- **BOSTA_PACKAGE_TYPE**: Type of package (default: `Parcel`)
- **BOSTA_PACKAGE_SIZE**: Package size - `SMALL`, `MEDIUM`, or `LARGE`
- **BOSTA_PICKUP_SLOT**: Preferred pickup time slot (e.g., "10:00 to 13:00")
- **BOSTA_ALLOW_OPEN_PACKAGE**: Whether customers can open packages before accepting (default: `false`)

### Sandbox vs Production

- Set `BOSTA_SANDBOX=true` for testing with BOSTA's staging environment
- Set `BOSTA_SANDBOX=false` when ready for production

The integration automatically switches between:
- Sandbox: `https://stg-app.bosta.co/api/v2`
- Production: `https://app.bosta.co/api/v2`

## Features

### 1. Automatic Shipment Creation

Shipments are automatically created when an order status changes to **"processing"**. This is handled by the `OrderObserver`.

**What happens:**
- When you update an order status to "processing" in the admin panel
- A shipment is automatically created in BOSTA
- The system stores the tracking number and shipment details
- The order is linked to the shipment

### 2. Manual Shipment Creation

Admins can manually create shipments from the order details page:

1. Navigate to Admin → Orders → [Order Details]
2. Find the "Shipment" section
3. Click "Create BOSTA Shipment"

### 3. Cash on Delivery (COD)

The integration automatically detects COD orders:
- If a payment method is `cod` or `cash_on_delivery`
- If the order has zero paid amount
- The COD amount is sent to BOSTA for collection

### 4. Tracking

**For Customers:**
- View tracking information on the order details page
- Access dedicated tracking page at `/track-shipment`
- See embedded BOSTA tracking widget
- View tracking history timeline

**For Admins:**
- View all shipments at Admin → Shipments
- Filter by status, tracking number, or order number
- View detailed shipment information
- Update tracking status manually

### 5. AWB Label Printing

Print Air Waybill labels for shipments:

1. Go to Admin → Shipments → [Shipment Details]
2. Click "Print AWB Label"
3. The BOSTA AWB will open in a new window for printing

### 6. Shipment Management

**Available Actions:**
- **Update Tracking**: Fetch latest status from BOSTA
- **Cancel Shipment**: Cancel before pickup/delivery
- **Request Pickup**: Schedule pickup for multiple shipments

### 7. Status Synchronization

Shipment statuses are synchronized from BOSTA:
- `pending`: Shipment created but not submitted
- `created`: Successfully created in BOSTA
- `in_transit`: Package is in transit
- `out_for_delivery`: Package is out for delivery
- `delivered`: Package successfully delivered
- `returned`: Package returned to sender
- `cancelled`: Shipment cancelled
- `failed`: Delivery failed

## Usage

### Admin Workflow

1. **Order Received**: Customer places an order
2. **Order Confirmation**: Admin reviews and confirms the order
3. **Update Status**: Change order status to "processing"
4. **Automatic Shipment**: System automatically creates BOSTA shipment
5. **Print Label**: Print AWB label for the package
6. **Request Pickup**: Schedule BOSTA pickup
7. **Track**: Monitor shipment status in real-time

### Customer Workflow

1. **Order Placed**: Customer completes checkout
2. **Order Confirmation**: Receives email with order details
3. **Shipment Created**: Gets tracking number when order is processed
4. **Track Shipment**: Can track via order page or tracking portal
5. **Receive Package**: Pays COD amount upon delivery (if applicable)

### Viewing Shipments

**Admin Panel:**
```
/admin/shipments - View all shipments
/admin/shipments/{id} - View shipment details
/admin/orders/{id} - View order with shipment info
```

**Customer Portal:**
```
/orders/{id} - View order with tracking info
/track-shipment - Search for tracking number
/track-shipment/{tracking_number} - View shipment details
```

## Webhooks

BOSTA can send webhook notifications for shipment status updates.

### Setting Up Webhooks

1. **Configure Webhook URL** in BOSTA dashboard:
   ```
   https://yourdomain.com/webhooks/bosta
   ```

2. **Set Webhook Secret** in your `.env`:
   ```env
   BOSTA_WEBHOOK_SECRET=your-secret-key
   ```

3. **Webhook Events**: BOSTA will send updates for:
   - Shipment status changes
   - Delivery confirmations
   - Return notifications

### Webhook Security

The integration verifies webhook signatures using HMAC-SHA256 to ensure requests are from BOSTA.

## Testing

### Test in Sandbox Mode

1. Set `BOSTA_SANDBOX=true` in `.env`
2. Use test API credentials from BOSTA
3. Create test orders and shipments
4. Monitor in BOSTA staging dashboard

### Test Scenarios

1. **Create Shipment**: Place order → Change status to "processing"
2. **Track Shipment**: View tracking page with test tracking number
3. **Cancel Shipment**: Cancel a pending shipment
4. **Webhook Testing**: Use BOSTA webhook testing tools

### Example Test Order

```php
// Create a test order with COD
$order = Order::create([
    'order_number' => 'TEST-001',
    'user_id' => 1,
    'total_amount' => 500.00,
    'status' => 'pending',
    // ... other fields
]);

// Add shipping address
$order->update([
    'shipping_address_snapshot' => [
        'street_address' => '123 Test Street',
        'city' => 'Cairo',
        'state' => 'Cairo',
        'phone' => '01234567890',
    ],
]);

// Update to processing to trigger shipment creation
$order->update(['status' => 'processing']);
```

## Troubleshooting

### Common Issues

**1. Shipment Not Created Automatically**
- Check order status is set to "processing"
- Verify shipping address is complete
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure BOSTA API credentials are correct

**2. Invalid API Credentials (Error 1028)**
```
Error: Invalid authorization token or API key
```
**Solution:**
- Verify `BOSTA_API_KEY` in `.env` is correct
- Make sure API key has **Read/Write** or **Full Access** permission
- Check if using correct API key for sandbox/production environment
- API key should be used directly (not as Bearer token)

**3. Business Location Required (Error 1073)**
```
Error: You should have a business location before creating an order
```
**Solution:**
- Set up a default pickup location in BOSTA dashboard
- Go to Settings → Pickup Locations → Add Location
- Set one location as default
- Optionally add the location ID to `BOSTA_BUSINESS_LOCATION_ID` in `.env`

**4. Missing Phone Number (Error 777)**
```
Error: receiver.phone is not allowed to be empty
```
**Solution:**
- Ensure all customer addresses include a phone number
- Phone must be in E.164 format (e.g., +201234567890)
- Update user profile with valid phone number
- Checkout process automatically includes user's phone in shipping address

**5. Missing Shipping Address**
```
Error: No shipping address found for order
```
- Ensure order has `shipping_address_snapshot` or `shipping_address_id`
- Verify address contains required fields: address_line_1, city, district, phone

**6. City/District Validation (Errors 3001-3003)**
```
Error: City Not Found / districtName missing required peer cityId
```
**Solution:**
- The system automatically maps 28 major Egyptian cities to BOSTA city IDs
- Supported cities: Cairo, Giza, Alexandria, Luxor, Aswan, Port Said, Suez, Ismailia, Dakahlia, Sharqia, Gharbia, Damietta, Behira, El Kalioubia, Monufia, Kafr Alsheikh, Fayoum, Bani Suif, Menya, Assuit, Sohag, Qena, Red Sea, North Coast, Matrouh, New Valley, South Sinai, North Sinai
- Ensure customer address includes both `city` and `district` fields
- City names must match BOSTA's city list (case-insensitive)
- District/zone names should be familiar neighborhoods within the city

**7. COD Amount Exceeded (Error 3007)**
```
Error: The COD amount should be less than or equal 30000 EGP
```
**Solution:**
- BOSTA has a 30,000 EGP limit for COD orders
- For higher amounts, use alternative payment methods

**8. Webhook Not Working**
- Verify webhook URL is publicly accessible (not localhost)
- Check `BOSTA_WEBHOOK_SECRET` is set correctly
- Review webhook logs in BOSTA dashboard
- Ensure CSRF protection is disabled for webhook route

### Debug Mode

Enable detailed logging by checking Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Look for entries containing:
- `BOSTA shipment created`
- `BOSTA API Error`
- `BOSTA webhook received`

### API Response Errors

Common BOSTA API errors:

| Error Code | Description | Solution |
|------------|-------------|----------|
| 400 | Bad Request | Check shipment data format |
| 401 | Unauthorized | Verify API key |
| 404 | Not Found | Check tracking number/delivery ID |
| 422 | Validation Error | Review required fields |
| 500 | Server Error | Retry or contact BOSTA support |
| 1028 | Invalid API Key | Generate new API key with proper permissions |
| 1073 | No Business Location | Set up pickup location in dashboard |
| 3001-3009 | Address/COD Errors | Check address format and COD limits |

## Admin Navigation

Add shipments link to your admin menu:

```blade
<a href="{{ route('admin.shipments.index') }}">
    <i class="fas fa-shipping-fast"></i> Shipments
</a>
```

## Additional Resources

- **BOSTA Documentation**: https://docs.bosta.co/
- **BOSTA Business Portal**: https://business.bosta.co/
- **BOSTA Support**: support@bosta.co
- **BOSTA API Reference**: https://docs.bosta.co/api-reference

## Support

For issues specific to this integration:
1. Check this documentation
2. Review Laravel logs
3. Check BOSTA API documentation
4. Contact your development team

For BOSTA-specific issues:
- Email: support@bosta.co
- Phone: Check BOSTA website for support hotline
- Business Portal: https://business.bosta.co/

## Version History

- **v1.0.0** (2025-12-05): Initial release
  - Automatic shipment creation
  - COD support
  - Tracking integration
  - Webhook handling
  - Admin management interface

---

**Last Updated**: December 5, 2025
