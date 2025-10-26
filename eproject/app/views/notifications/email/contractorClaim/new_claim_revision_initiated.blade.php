@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/contractorClaims.newClaimActivated', ['activatedBy' => $subsidiary], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/contractorClaims.nowAbleToSubmitClaims', [], 'messages', $recipientLocale) }}</p>
@endsection