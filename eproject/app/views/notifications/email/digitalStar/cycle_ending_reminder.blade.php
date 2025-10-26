@extends('notifications.email.digitalStar.template')

@section('message')
    <p>{{ trans('digitalStar/email.completeTaskAndSubmissionsBeforeCycleEnd') }}</p>
@endsection