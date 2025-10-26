@extends('notifications.email.base')

@section('content')
	<p>{{ trans('lossAndExpenses.unreadEmailMessage', array('senderName' => $senderName)) }}</p>
@endsection