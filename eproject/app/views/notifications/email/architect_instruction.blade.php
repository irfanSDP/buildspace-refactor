@extends('notifications.email.base')

@section('content')
	<p>{{ trans('architectInstructions.unreadEmailMessage', array('senderName' => $senderName)) }}</p>
@endsection