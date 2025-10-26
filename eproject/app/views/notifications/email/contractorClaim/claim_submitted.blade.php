@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/contractorClaims.newClaimSubmittedBy', ['contractor' => $contractor], 'messages', $recipientLocale) }}</p>
@endsection