@extends('notifications.email.base')

@section('content')
	<p>You have one unread message from "{{{ $senderName }}}" at <strong>"Inbox"</strong> Menu</p>
@endsection