@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/callingTender.callingTenderFormIssued', [], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $currentTenderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderStartingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderStartDate }}}</p>

    <p>{{ trans('email.tenderClosingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderClosingDate }}}</p>

    @if(isset($technicalTenderClosingDate))
        <p>{{ trans('email/callingTender.technicalSubmissionClosingDate', [], 'messages', $recipientLocale) }}: {{{ $technicalTenderClosingDate }}}</p>
    @endif
@endsection