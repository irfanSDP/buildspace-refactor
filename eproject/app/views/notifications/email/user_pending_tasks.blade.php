@extends('notifications.email.base')

@section('content')
    <p>You have pending task(s) that requires your attention.</p>
    <p>Kindly attend to those promptly.</p>

    <p>{{ trans('email.visitThisLinkForMoreInformation') }}:</p>

    <p><a href="{{{ getenv('APPLICATION_URL') }}}">{{{ getenv('APPLICATION_URL') }}}</a></p>
@endsection