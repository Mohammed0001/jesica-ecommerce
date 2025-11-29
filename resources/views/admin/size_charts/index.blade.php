@extends('layouts.admin')

@section('title', 'Size Charts')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>Size Charts</h6>
            <a href="{{ route('admin.size-charts.create') }}" class="btn btn-primary btn-sm">Create Size Chart</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($sizeCharts->count())
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Preview</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sizeCharts as $chart)
                            <tr>
                                <td>{{ $chart->name }}</td>
                                <td>
                                    @if($chart->image_url)
                                        <img src="{{ $chart->image_url }}" alt="{{ $chart->name }}" style="height:48px;">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.size-charts.destroy', $chart) }}" method="POST" onsubmit="return confirm('Delete this size chart?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $sizeCharts->links() }}
            @else
                <p>No size charts yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
