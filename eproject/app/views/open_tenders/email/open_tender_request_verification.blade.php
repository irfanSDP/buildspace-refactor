@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/openTender.requestToVerifyOpenTender', ['tenderName' => $tenderName], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email.projectName', ['projectName' => $project['title']], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $tender['current_tender_name']], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>
@endsection