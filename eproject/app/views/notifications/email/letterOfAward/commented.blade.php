@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/letterOfAward.letterOfAwardReviewed', [], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email/letterOfAward.haveUnreadComments', ['unreadCommentCount' => $unreadCommentCount], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $project_title], 'messages', $recipientLocale) }}</p>
@endsection