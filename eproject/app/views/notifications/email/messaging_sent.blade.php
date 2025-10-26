@extends('notifications.email.base')

@section('content')
	<p>You have one unread message from "{{{ $senderName }}}" at <strong>"Sent"</strong> Menu</p>
@endsection