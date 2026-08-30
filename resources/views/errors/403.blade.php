@extends('errors.layout')

@section('code', '403')
@section('title', 'Not allowed')

@section('message')
    You are signed in, but this page is not available to your account.
@endsection

@section('hints')
    <li>Admin pages require an administrator account.</li>
    <li>If you switched accounts recently, sign in again.</li>
@endsection

@section('actions')
    <a class="err__btn err__btn--ghost" href="{{ url('/login') }}">Sign in</a>
@endsection
