@extends('notifications.email.digitalStar.template')

@section('message')
    <p>{{ trans('digitalStar/email.completeTaskAndSubmissionsAsCycleStarts') }}</p>
@endsection