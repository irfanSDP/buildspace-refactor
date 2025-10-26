@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/letterOfAward.letterOfAwardRejected', ['senderName' => $senderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $project_title], 'messages', $recipientLocale) }}</p>
@endsection