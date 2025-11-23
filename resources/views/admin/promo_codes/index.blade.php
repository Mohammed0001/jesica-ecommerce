@extends('layouts.admin')

@section('title', 'Promo Codes')

@section('content')
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Promo Codes</h1>
        <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-primary">Create Promo Code</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Uses</th>
                        <th>Expires At</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promoCodes as $promo)
                    <tr>
                        <td>{{ $promo->code }}</td>
                        <td>{{ ucfirst($promo->type) }}</td>
                        <td>{{ $promo->value }}</td>
                        <td>{{ $promo->usage_count }}{{ $promo->max_uses ? ' / ' . $promo->max_uses : '' }}</td>
                        <td>{{ $promo->expires_at?->format('M d, Y') ?? '-' }}</td>
                        <td>{{ $promo->active ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('admin.promo-codes.edit', $promo) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this promo code?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">{{ $promoCodes->links() }}</div>
        </div>
    </div>
</div>
@endsection
