@extends('notifications.email.base')

@section('content')
    <p>A Recommendation of Tender has been submitted.</p>

    <p>Project Name: {{{ $project['title'] }}}</p>

    <p>Tender Name: {{{ $model['current_tender_name'] }}}</p>

    <p>Description of Work: {{{ $workCategory }}}</p>
@endsection