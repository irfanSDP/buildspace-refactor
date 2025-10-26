@extends('notifications.email.base')

@section('content')
	<p>You have one unread message from "{{{ $senderName }}}" at <strong>"Engineer's Instruction"</strong> Menu</p>
@endsection