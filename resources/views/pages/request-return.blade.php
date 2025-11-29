@extends('layouts.app')

@section('title', 'Request Return')

@section('content')
<main class="page-request-return">
    <section class="container py-5">
        <h1 class="mb-4">Request a Return</h1>
        <p>If you need to return an item, please fill the form below and our support team will contact you.</p>

        <form method="POST" action="#" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label for="order_number" class="form-label">Order Number</label>
                <input type="text" class="form-control" id="order_number" name="order_number">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="col-12">
                <label for="reason" class="form-label">Reason for Return</label>
                <textarea id="reason" name="reason" class="form-control" rows="4"></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Submit Return Request</button>
            </div>
        </form>
    </section>
</main>
@endsection
