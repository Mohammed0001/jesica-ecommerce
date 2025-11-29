@extends('layouts.app')

@section('title', 'Customer Services')

@section('content')
<main class="page-customer-services">
    <section class="container py-5">
        <h1 class="mb-4">Customer Services</h1>
        <p>Welcome to our Customer Services page. Here you can find contact information, service hours, and support options.</p>

        <h3 class="mt-4">Contact Support</h3>
        <p>If you need help with an order, please <a href="{{ route('contact') }}">contact us</a> or email support at <strong>{{ config('mail.contact_email', 'contact@example.com') }}</strong>.</p>

        <h3 class="mt-4">Hours</h3>
        <p>Support is available Sunday to Thursday, 9:00 — 17:00.</p>
    </section>
</main>
@endsection
