@extends('errors.layout')

@section('code', '429')
@section('title', 'Too many requests')

@section('message')
    Requests are arriving from your connection faster than the store allows. This is a temporary throttle, not a block on your account.
@endsection

@section('hints')
    <li>Wait about a minute, then try again.</li>
    <li>Repeatedly refreshing a slow page makes this worse, not better.</li>
@endsection
