@extends('notifications.email.base')

@section('content')
    <p>A new RFV has been approved.</p>

    <p>Project Name: {{{ $project_title }}}</p>

    <p>RFV Description: {{{ $request_for_variation_description }}}</p>
@endsection