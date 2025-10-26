@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email.projectName', ['projectName' => $projectName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $tenderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/callingTender.newTenderStartingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderStartDate }}}</p>

    <p>{{ trans('email/callingTender.newTenderClosingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderClosingDate }}}</p>

    @if(isset($technicalTenderClosingDate))
        <p>{{ trans('email/callingTender.newTechnicalSubmissionClosingDate', [], 'messages', $recipientLocale) }}: {{{ $technicalTenderClosingDate }}}</p>
    @endif

    <p>{{ trans('email/tender.tenderClosed', [], 'messages', $recipientLocale) }}</p>
@endsection