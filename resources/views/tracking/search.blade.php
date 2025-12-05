@extends('layouts.app')

@section('title', 'Track Your Shipment')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-center mb-8">Track Your Shipment</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="GET" action="{{ route('tracking.show', '') }}" class="space-y-4">
                <div>
                    <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter Tracking Number
                    </label>
                    <input
                        type="text"
                        id="tracking_number"
                        name="tracking_number"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your tracking number"
                        value="{{ request('tracking_number') }}"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 transition-colors font-medium"
                >
                    Track Shipment
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>You can find your tracking number in your order confirmation email.</p>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-blue-600 hover:underline">
                ← Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
