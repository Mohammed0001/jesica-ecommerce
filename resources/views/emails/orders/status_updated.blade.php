@component('mail::message')

# Order #{{ $order->id }} — Status Updated

Hello {{ $order->user->name ?? 'Customer' }},

Your order status has changed to **{{ ucfirst($order->status) }}**.

@isset($notes)
**Note from support:**

{{ $notes }}
@endisset

**Order summary**

- **Order #:** {{ $order->id }}
- **Total:** {{ $order->formattedTotal ?? $order->total ?? 'N/A' }}

@if($order->orderItems && $order->orderItems->count())
@foreach($order->orderItems as $item)
- {{ $item->quantity }} × {{ $item->product->title ?? $item->name }} — {{ $item->formatted_price ?? '' }}
@endforeach
@endif

@component('mail::button', ['url' => route('orders.show', $order->id)])
View your order
@endcomponent

Thanks,
{{ config('app.name') }}

@endcomponent
