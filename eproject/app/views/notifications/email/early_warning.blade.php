@extends('notifications.email.base')

@section('content')
	<p>{{ trans('earlyWarnings.unreadEmailMessage', array('senderName' => $senderName)) }}</p>
@endsection