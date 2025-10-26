@extends('notifications.email.base')

@section('content')
    <p>{{{ $userName }}} has submitted a vendor registration form for approval.</p>
    <p>Vendor : {{{ $company }}}</p>
@endsection