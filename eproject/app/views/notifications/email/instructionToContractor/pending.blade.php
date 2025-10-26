@extends('notifications.email.base')

@section('content')
    <p>{{ trans('instructiontocontractor.instructionToContractorPendingApproval', ['senderName' => $senderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $project_title], 'messages', $recipientLocale) }}</p>
@endsection