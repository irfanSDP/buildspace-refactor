@extends('notifications.email.base')

@section('content')
    <p>{{ trans('eBidding.eBiddingApproved', [], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $project_title], 'messages', $recipientLocale) }}</p>
@endsection