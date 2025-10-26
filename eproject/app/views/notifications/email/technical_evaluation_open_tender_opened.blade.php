@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/openTender.technicalEvaluationTenderOpened', [], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $tenderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderStartingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderStartingDate }}}</p>

    <p>{{ trans('email.tenderClosingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderClosingDate }}}</p>
@endsection