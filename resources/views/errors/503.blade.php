@extends('errors.layout')

@section('code', '503')
@section('title', 'Down for maintenance')

@section('message')
    The store is briefly offline while we deploy an update. It is usually back within a few minutes.
@endsection

@section('hints')
    <li>Nothing in your bag or your order history is affected.</li>
    <li>Refresh in a few minutes to check.</li>
@endsection
