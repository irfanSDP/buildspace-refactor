@extends('notifications.email.base')

@section('content')
    <p>{{{ $senderName }}} has submitted a RFV for you to work out the nett amount.</p>

    <p>Project Name: {{{ $project_title }}}</p>

    <p>RFV Description: {{{ $request_for_variation_description }}}</p>
@endsection