@extends('notifications.email.base')

@section('content')
    <p>Company: {{{ $companyName }}}</p>
    <p>{{ $contents }}</p>
@endsection