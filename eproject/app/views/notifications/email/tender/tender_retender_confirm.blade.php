@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/openTender.tenderResubmissionApproved', ['senderName' => $senderName, 'tenderRevision' => trans("tenders.tenderRevision", [], 'messages', $recipientLocale)], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $tenderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>
@endsection