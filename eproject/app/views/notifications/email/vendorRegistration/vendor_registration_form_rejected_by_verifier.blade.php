@extends('notifications.email.base')

@section('content')
    <p>Vendor registration has been rejected.</p>
    <p>Vendor : {{ $company }}</p>
    <p>Kindly make amendments and resubmit again.</p>
@endsection