@extends('layouts.admin')

@section('title', 'Clients Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Clients</h1>
            <div class="d-flex gap-2">
                <span class="page-meta">{{ $clients->total() }} registered clients</span>
            </div>
        </div>
    </div>

    <!-- Clients List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>All Clients</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>All Clients</option>
                            <option>Verified</option>
                            <option>Pending Verification</option>
                            <option>Admin Users</option>
                        </select>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Search clients...">
                            <button class="btn btn-outline-secondary btn-sm" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($clients->count() > 0)
                        <div class="table-responsive">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Orders</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        {{ substr($client->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="client-name">{{ $client->name }}</div>
                                                        @if($client->date_of_birth)
                                                            <div class="client-age">Age: {{ $client->age ?? 'N/A' }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="client-email">{{ $client->email }}</div>
                                                @if($client->email_verified_at)
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle"></i> Verified
                                                    </small>
                                                @else
                                                    <small class="text-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="client-phone">{{ $client->phone ?? 'Not provided' }}</span>
                                            </td>
                                            <td>
                                                <span class="orders-count">{{ $client->orders_count }} orders</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $client->role && $client->role->name === 'ADMIN' ? 'danger' : 'primary' }}">
                                                    {{ $client->role ? $client->role->name : 'CLIENT' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($client->email_verified_at)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>{{ $client->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.clients.show', $client) }}"
                                                       class="btn btn-sm btn-outline-primary" title="View Profile">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="mailto:{{ $client->email }}"
                                                       class="btn btn-sm btn-outline-secondary" title="Send Email">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $clients->links() }}
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No clients found</p>
                            <p class="text-muted">Client registrations will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.client-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.client-age {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.client-email {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.client-phone {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--primary-color);
}

.orders-count {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: var(--secondary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
}
</style>
@endpush
@endsection
