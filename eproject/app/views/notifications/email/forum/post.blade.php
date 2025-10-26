@extends('notifications.email.base')

@section('content')
    <p>Project: {{{ $projectTitle }}}</p>
    <p>Topic: {{{ $threadTitle }}}</p>

    <p>
        {{ trans('forum.postNotification') }}
    </p>

    <p>
        {{{ $posterName }}} ({{{ $postedAt }}}):
    </p>

    <q>{{ $postContent }}</q>
@endsection