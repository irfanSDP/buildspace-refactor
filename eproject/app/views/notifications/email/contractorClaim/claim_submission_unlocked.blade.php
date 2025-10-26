@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/contractorClaims.claimRejected', ['subsidiary' => $subsidiary], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/contractorClaims.loginToResubmit', [], 'messages', $recipientLocale) }}</p>
@endsection