@extends('layouts.app')

@section('title', 'Track Shipment - ' . $shipment->tracking_number)

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Shipment Tracking</h1>
            <a href="{{ route('tracking.search') }}" class="text-blue-600 hover:underline">
                Track Another Shipment
            </a>
        </div>

        <!-- Shipment Status -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Tracking Number</p>
                    <p class="text-lg font-semibold">{{ $shipment->tracking_number }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-1">Status</p>
                    <p>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                            @if($shipment->status == 'delivered') bg-green-100 text-green-800
                            @elseif($shipment->status == 'in_transit' || $shipment->status == 'out_for_delivery') bg-blue-100 text-blue-800
                            @elseif($shipment->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                        </span>
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-1">Order Number</p>
                    <p class="text-lg font-semibold">{{ $shipment->order->order_number }}</p>
                </div>
            </div>

            @if($shipment->delivered_at)
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-green-900">Delivered</p>
                        <p class="text-sm text-green-700">{{ $shipment->delivered_at->format('l, F j, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- BOSTA Tracking Widget -->
        @if($shipment->tracking_number)
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <h2 class="text-2xl font-bold mb-6">Live Tracking</h2>

            <!-- Embed BOSTA tracking iframe -->
            <div class="relative" style="padding-bottom: 600px; height: 0; overflow: hidden;">
                <iframe
                    src="https://bosta.co/tracking-shipment/?track_id={{ $shipment->tracking_number }}"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                    allowfullscreen
                ></iframe>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ $shipment->getTrackingUrl() }}" target="_blank" class="text-blue-600 hover:underline">
                    Open in new window →
                </a>
            </div>
        </div>
        @endif

        <!-- Tracking History -->
        @if($shipment->tracking_history)
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6">Tracking History</h2>

            <div class="space-y-6">
                @foreach(array_reverse($shipment->tracking_history) as $index => $event)
                <div class="flex">
                    <div class="flex-shrink-0 relative">
                        <div class="w-4 h-4 {{ $index === 0 ? 'bg-blue-600' : 'bg-gray-400' }} rounded-full mt-1.5"></div>
                        @if($index < count($shipment->tracking_history) - 1)
                        <div class="absolute top-6 left-1.5 w-0.5 h-full bg-gray-300"></div>
                        @endif
                    </div>
                    <div class="ml-6 flex-1 pb-8">
                        <p class="font-semibold text-lg">{{ $event['status'] ?? 'Update' }}</p>
                        @if(isset($event['message']))
                        <p class="text-gray-600 mt-1">{{ $event['message'] }}</p>
                        @endif
                        <p class="text-sm text-gray-500 mt-2">
                            {{ \Carbon\Carbon::parse($event['timestamp'])->format('l, F j, Y \a\t g:i A') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Order Details Link -->
        @auth
            @if($shipment->order->user_id === auth()->id())
            <div class="mt-8 text-center">
                <a href="{{ route('orders.show', $shipment->order) }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">
                    View Full Order Details
                </a>
            </div>
            @endif
        @endauth
    </div>
</div>
@endsection
