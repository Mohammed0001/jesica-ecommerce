@extends('layouts.admin')

@section('title', 'Shipment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Shipment Details</h1>
            <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Shipments
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Shipment Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Shipment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Tracking Number</label>
                            <p class="mb-0 fw-semibold">{{ $shipment->tracking_number ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">AWB Number</label>
                            <p class="mb-0 fw-semibold">{{ $shipment->awb_number ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge
                                    @if($shipment->status == 'delivered') bg-success
                                    @elseif($shipment->status == 'in_transit' || $shipment->status == 'out_for_delivery') bg-primary
                                    @elseif($shipment->status == 'cancelled') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Provider</label>
                            <p class="mb-0 fw-semibold">{{ strtoupper($shipment->provider) }}</p>
                        </div>

                        @if($shipment->is_cod)
                        <div class="col-md-6">
                            <label class="text-muted small">COD Amount</label>
                            <p class="mb-0 fw-semibold">EGP {{ number_format($shipment->cod_amount, 2) }}</p>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label class="text-muted small">Created At</label>
                            <p class="mb-0 fw-semibold">{{ $shipment->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        @if($shipment->delivered_at)
                        <div class="col-md-6">
                            <label class="text-muted small">Delivered At</label>
                            <p class="mb-0 fw-semibold">{{ $shipment->delivered_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Tracking URL -->
                    @if($shipment->tracking_number)
                    <div class="mt-4 pt-4 border-top">
                        <a href="{{ $shipment->getTrackingUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Track on BOSTA Website
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Order Number</label>
                            <p class="mb-0 fw-semibold">
                                <a href="{{ route('admin.orders.show', $shipment->order) }}" class="text-decoration-none">
                                    #{{ $shipment->order->id }}
                                </a>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Customer</label>
                            <p class="mb-0 fw-semibold">{{ $shipment->order->user->name ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Order Status</label>
                            <p class="mb-0">
                                <span class="badge bg-info">{{ ucfirst($shipment->order->status) }}</span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Total Amount</label>
                            <p class="mb-0 fw-semibold">EGP {{ number_format($shipment->order->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking History -->
            @if($shipment->tracking_history)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tracking History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach(array_reverse($shipment->tracking_history) as $event)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $event['status'] ?? 'Update' }}</h6>
                                @if(isset($event['message']))
                                <p class="text-muted mb-1">{{ $event['message'] }}</p>
                                @endif
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('M d, Y H:i') }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 1rem;">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($shipment->tracking_number)
                        <form action="{{ route('admin.shipments.update-tracking', $shipment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync-alt me-2"></i> Update Tracking
                            </button>
                        </form>
                        @endif

                        {{-- @if($shipment->bosta_delivery_id)
                        <a href="{{ route('admin.shipments.print-label', $shipment) }}" target="_blank"
                           class="btn btn-success w-100">
                            <i class="fas fa-print me-2"></i> Print AWB Label
                        </a>
                        @endif --}}

                        {{-- @if($shipment->canBeCancelled())
                        <form action="{{ route('admin.shipments.cancel', $shipment) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this shipment?')">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-2"></i> Cancel Shipment
                            </button>
                        </form>
                        @endif --}}

                        <a href="{{ route('admin.orders.show', $shipment->order) }}"
                           class="btn btn-secondary w-100">
                            <i class="fas fa-shopping-cart me-2"></i> View Order
                        </a>
                    </div>

                    @if($shipment->notes)
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="mb-2">Notes</h6>
                        <p class="text-muted small mb-0">{{ $shipment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0;
    bottom: -20px;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #0d6efd;
}

.timeline-content {
    padding: 0;
}
</style>
@endsection
