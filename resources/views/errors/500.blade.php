@extends('errors.layout')

@section('code', '500')
@section('title', 'Something broke on our side')

@section('message')
    The store hit an unexpected error while building this page. The fault is logged on our end; nothing you did caused it and no payment was taken.
@endsection

@section('hints')
    <li>Your bag and account are unaffected.</li>
    <li>Trying again in a moment often works, as most of these are transient.</li>
@endsection

@section('actions')
    <a class="err__btn err__btn--ghost" href="{{ url('/cart') }}">View my bag</a>
@endsection

@section('reference')
    {{-- Same value that was written to the log line for this request. --}}
    If this keeps happening, contact us and quote reference <code>{{ \App\Support\ErrorReference::current() }}</code>.
@endsection
