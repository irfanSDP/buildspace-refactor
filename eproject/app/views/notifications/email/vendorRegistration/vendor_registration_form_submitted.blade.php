@extends('notifications.email.base')

@section('content')
    <p>
        {{{ $company }}} has submitted their registration form for approval.
    </p>
@endsection