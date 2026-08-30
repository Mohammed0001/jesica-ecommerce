@extends('errors.layout')

@section('code', '419')
@section('title', 'Your session expired')

@section('message')
    The page sat open long enough for its security token to expire, so the form was not submitted. Nothing was saved or charged.
@endsection

@section('hints')
    <li>Go back, reload the page, and submit the form again.</li>
    <li>Anything in your bag is still there.</li>
@endsection

@section('actions')
    <a class="err__btn err__btn--ghost" href="{{ url('/cart') }}">View my bag</a>
@endsection
