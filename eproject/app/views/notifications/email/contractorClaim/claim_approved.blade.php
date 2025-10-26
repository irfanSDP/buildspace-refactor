@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/contractorClaims.claimApproved', ['subsidiary' => $subsidiary], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/contractorClaims.loginToReview', [], 'messages', $recipientLocale) }}</p>
@endsection