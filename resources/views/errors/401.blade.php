@extends('errors.layout')

@section('code', '401')
@section('title', 'Sign in required')

@section('message')
    This page is only visible once you are signed in.
@endsection

@section('hints')
    <li>Your session may simply have expired after 2 hours of inactivity.</li>
    <li>You can also check out as a guest without an account.</li>
@endsection

@section('actions')
    <a class="err__btn err__btn--ghost" href="{{ url('/login') }}">Sign in</a>
@endsection
