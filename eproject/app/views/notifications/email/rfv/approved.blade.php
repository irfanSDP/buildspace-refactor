@extends('notifications.email.base')

@section('content')
    <p>{{{ $senderName }}} has approved RFV.</p>

    <p>Project Name: {{{ $project_title }}}</p>

    <p>RFV Description: {{{ $request_for_variation_description }}}</p>
@endsection