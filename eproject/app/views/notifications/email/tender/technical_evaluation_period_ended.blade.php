@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email.projectName', ['projectName' => $projectName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $tenderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/tender.technicalEvaluationEndedMessage', [], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/callingTender.technicalSubmissionClosingDate', [], 'messages', $recipientLocale) }}: {{{ $closingDate }}}</p>
@endsection