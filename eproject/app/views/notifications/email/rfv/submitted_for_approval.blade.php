@extends('notifications.email.base')

@section('content')
    <p>A RFV has been submitted for your approval.</p>

    <p>Project Name: {{{ $project_title }}}</p>

    <p>RFV Description: {{{ $request_for_variation_description }}}</p>
@endsection