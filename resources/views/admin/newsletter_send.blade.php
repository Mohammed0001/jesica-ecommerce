@extends('layouts.admin')

@section('title', 'Send Newsletter')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header">
            <h6>Send Newsletter</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.newsletter.send') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input name="subject" class="form-control" required value="{{ old('subject', 'Jesica Riad - ' . now()->format('F') . '\'s Newsletter') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Body (HTML allowed)</label>
                    <textarea name="body" rows="10" class="form-control" required>{{ old('body') }}</textarea>
                </div>

                <button class="btn btn-primary">Queue Newsletter</button>
            </form>
        </div>
    </div>
</div>
@endsection
