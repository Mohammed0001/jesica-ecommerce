<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order {{ $order->order_number ?? $order->id }} — {{ ucfirst($order->status) }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
            color: #333333;
        }

        .container {
            width: 100%;
            padding: 20px 0;
        }

        .main {
            width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
        }

        .header {
            background: #000;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .hero img {
            width: 100%;
            display: block;
        }

        .content {
            padding: 25px;
        }

        .content h2 {
            margin-top: 0;
            font-size: 20px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .summary th,
        .summary td {
            border: 1px solid #f0f0f0;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }

        .muted {
            color: #666;
            font-size: 13px;
        }

        .footer {
            background: #f0f0f0;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>

    <!-- Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" class="container">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table class="main" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header">
                            <h1>Jesica Riad</h1>
                        </td>
                    </tr>

                    <!-- Hero Image -->
                    <tr>
                        <td class="hero" style="background-color:#ffffff; text-align:center;">
                            @if (file_exists(public_path('images/signature-logo.png')))
                                @php
                                    $__path = public_path('images/signature-logo.png');
                                    $__mime = function_exists('mime_content_type') ? mime_content_type($__path) : null;
                                    if (!$__mime) {
                                        $__ext = strtolower(pathinfo($__path, PATHINFO_EXTENSION));
                                        $__mime =
                                            $__ext === 'png'
                                                ? 'image/png'
                                                : ($__ext === 'jpg' || $__ext === 'jpeg'
                                                    ? 'image/jpeg'
                                                    : 'image/png');
                                    }
                                    $__data = base64_encode(file_get_contents($__path));
                                    $__src = 'data:' . $__mime . ';base64,' . $__data;
                                @endphp
                                <img src="{!! $__src !!}" alt="Jesica Riad"
                                    style="max-height:300px; object-fit:cover; width:40%; filter: invert(1);margin:auto; display:block;" />
                            @else
                                <img src="{{ asset('images/order-hero.jpg') }}" alt="Jesica Riad"
                                    style="max-height:300px; object-fit:cover; width:100%;" />
                            @endif
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">
                            <h2>Order #{{ $order->order_number ?? $order->id }} — Status Updated</h2>
                            <p class="muted">Hi {{ $order->user?->name ?? 'Customer' }},</p>

                            <p style="font-size:15px; line-height:1.6;">Your order status changed to
                                <strong>{{ ucfirst($order->status) }}</strong>.</p>

                            @if (!empty($notes))
                                <p style="font-size:15px; line-height:1.6;"><strong>Notes:</strong> {{ $notes }}
                                </p>
                            @endif

                            <h3 style="font-size:16px; margin-top:18px;">Order summary</h3>
                            <table class="summary">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td>{{ $item->product_snapshot['title'] ?? 'Product' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="2" style="text-align:right"><strong>Total</strong></td>
                                        <td><strong>{{ number_format($order->total_amount, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>

                            @if ($order->status === 'shipped')
                                <p style="margin-top:16px;">Your order has been shipped. Tracking:
                                    <strong>{{ $order->tracking_number ?? 'N/A' }}</strong></p>
                            @endif

                            <div style="text-align:center; margin:30px 0;">
                                <a href="{{ route('orders.show', $order->id) }}" class="btn">View Order</a>
                            </div>

                            <hr style="border:0; border-top:1px solid #e5e5e5; margin:20px 0;">

                            <p class="muted" style="text-align:center;">Follow us for daily inspiration — <a
                                    href="https://www.instagram.com/jessica.riad/"
                                    style="color:#000; text-decoration:none;">Instagram</a></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            © {{ date('Y') }} Jesica Riad — All rights reserved.<br>
                            You are receiving this email because you placed an order with us.
                        </td>
                    </tr>

                </table>
                <!-- End Main Container -->

            </td>
        </tr>
    </table>

</body>

</html>
