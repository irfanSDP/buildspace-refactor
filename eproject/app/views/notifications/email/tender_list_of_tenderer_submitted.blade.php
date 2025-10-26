@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/listOfTenderer.lotApproved', [], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email.projectName', ['projectName' => $project['title']], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $model['current_tender_name']], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>
@endsection