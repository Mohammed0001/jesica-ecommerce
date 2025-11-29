@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="alert alert-success">
        You have been unsubscribed from the newsletter. We're sorry to see you go.
    </div>
    <a href="{{ route('home') }}" class="btn btn-primary">Return home</a>
</div>
@endsection
