@extends('layouts.admin')

@section('title', 'Delivery Areas')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Delivery Areas</h1>
            <p class="text-muted small mb-0">Configure per-area delivery fees. Cairo districts have individual pricing; other governorates have a single fee. International orders use the global international fee.</p>
        </div>
        <a href="{{ route('admin.delivery-areas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Area
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($areas->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-map-marker-alt fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No delivery areas configured yet. All cities will use the global delivery fee.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Area Name</th>
                            <th>Type</th>
                            <th>Delivery Fee</th>
                            <th>Matched Name(s)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areas as $area)
                        <tr>
                            <td class="fw-semibold">{{ $area->name }}</td>
                            <td>
                                @if($area->type === 'cairo_district')
                                    <span class="badge bg-warning text-dark">Cairo District</span>
                                @else
                                    <span class="badge bg-primary">Governorate</span>
                                @endif
                            </td>
                            <td>
                                @if($area->delivery_fee !== null)
                                    <span class="badge bg-success fs-6">{{ number_format($area->delivery_fee, 2) }} EGP</span>
                                @else
                                    <span class="badge bg-secondary">Using global fee</span>
                                @endif
                            </td>
                            <td>
                                @if($area->city_names)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($area->city_names as $city)
                                            <span class="badge bg-light text-dark border">{{ $city }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.delivery-areas.edit', $area) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('admin.delivery-areas.destroy', $area) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this delivery area?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('admin.settings.edit') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-cog me-1"></i> Global Settings (fallback fee)
        </a>
    </div>
</div>
@endsection
