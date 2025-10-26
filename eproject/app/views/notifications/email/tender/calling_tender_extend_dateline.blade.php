@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/callingTender.ctExtendDatelineRequest', ['senderName' => $senderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $tenderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderStartingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderStartDate }}}</p>

    <p>{{ trans('email.tenderClosingDate', [], 'messages', $recipientLocale) }}: {{{ $tenderClosingDate }}}</p>

    @if(isset($technicalTenderClosingDate))
        <p>{{ trans('tenders.technicalTenderClosingDate', [], 'messages', $recipientLocale) }}: {{{ $technicalTenderClosingDate }}}</p>
    @endif
@endsection