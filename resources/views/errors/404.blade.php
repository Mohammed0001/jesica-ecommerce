@extends('errors.layout')

@section('code', '404')
@section('title', 'Page not found')

@section('message')
    The page you asked for is not here. It may have been renamed, sold out and retired, or the link may have a typo.
@endsection

@section('hints')
    <li>Check the address for a stray character.</li>
    <li>Products that sold out are sometimes taken off the store.</li>
    <li>Use the search to find the piece by name.</li>
@endsection

@section('actions')
    <a class="err__btn err__btn--ghost" href="{{ url('/collections') }}">Browse collections</a>
    <a class="err__btn err__btn--ghost" href="{{ url('/search') }}">Search the store</a>
@endsection
