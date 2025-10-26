@extends('notifications.email.base')

@section('content')
    <p>{{{ $senderName }}} has rejected your estimate cost.</p>

    <p>Project Name: {{{ $project_title }}}</p>

    <p>RFV Description: {{{ $request_for_variation_description }}}</p>
@endsection