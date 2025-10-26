@extends('notifications.email.base')

@section('content')
    <p>"{{{ $senderName }}}" uploaded a new file into Tender Documents Module</p>

    <p>Project Name: {{{ $project['title'] }}}</p>
@endsection