@extends('notifications.email.base')

@section('content')
	<p>{{ trans('extensionOfTime.unreadEmailMessage', array('senderName' => $senderName)) }}</p>
@endsection