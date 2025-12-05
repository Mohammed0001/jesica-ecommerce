@extends('layouts.admin')

@section('title', 'Shipments Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Shipments</h1>
            <div class="d-flex gap-2">
                <span class="page-meta">{{ $shipments->total() }} total shipments</span>
            </div>
        </div>
    </div>

    <!-- Shipments List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>All Shipments</h6>
                    <div class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form ? this.form.submit() : window.location.href = updateQueryString('status', this.value)">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" name="tracking_number" value="{{ request('tracking_number') }}" class="form-control form-control-sm" placeholder="Tracking number...">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="this.previousElementSibling.form ? this.previousElementSibling.form.submit() : window.location.href = updateQueryString('tracking_number', this.previousElementSibling.value)">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($shipments->count() > 0)
                        <div class="table-responsive" style="overflow-x:auto; overflow-y:hidden;">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th>Tracking #</th>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>COD Amount</th>
                                        <th>Created</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shipments as $shipment)
                                        <tr>
                                            <td>
                                                <div class="order-number">
                                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="text-decoration-none">
                                                        {{ $shipment->tracking_number ?? 'N/A' }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $shipment->order) }}" class="text-decoration-none">
                                                    #{{ $shipment->order->id }}
                                                </a>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="customer-name">{{ $shipment->order->user->name ?? 'N/A' }}</div>
                                                    <div class="customer-email">{{ $shipment->order->user->email ?? 'N/A' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge
                                                    @if($shipment->status == 'delivered') bg-success
                                                    @elseif($shipment->status == 'in_transit' || $shipment->status == 'out_for_delivery') bg-primary
                                                    @elseif($shipment->status == 'cancelled') bg-danger
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($shipment->is_cod)
                                                    <div class="order-total">EGP {{ number_format($shipment->cod_amount, 2) }}</div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $shipment->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @if($shipment->canBeCancelled())
                                                    <form action="{{ route('admin.shipments.cancel', $shipment) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to cancel this shipment?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">No shipments found.</p>
                        </div>
                    @endif

                <!-- Pagination -->
                @if($shipments->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $shipments->firstItem() }} to {{ $shipments->lastItem() }} of {{ $shipments->total() }} shipments
                        </div>
                        <div>
                            {{ $shipments->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
