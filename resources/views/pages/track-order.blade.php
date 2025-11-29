@extends('layouts.app')

@section('title', 'Track My Order')

@section('content')
<main class="page-track-order">
    <section class="container py-5">
        <h1 class="mb-4">Track My Order</h1>
        <p>Enter your order number and email to track the status of your order.</p>

        <form method="GET" action="#" class="row g-3">
            <div class="col-md-6">
                <label for="order_number" class="form-label">Order Number</label>
                <input type="text" class="form-control" id="order_number" name="order_number" placeholder="e.g. 0001">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Track</button>
            </div>
        </form>
    </section>
</main>
@endsection
