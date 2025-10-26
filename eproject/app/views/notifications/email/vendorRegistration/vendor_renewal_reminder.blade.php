@extends('notifications.email.base')

@section('content')
    <p>Company : {{{ $companyName }}} ({{{ $roc }}})</p>
    <p>Expiry Date : {{{ $expiryDate }}}</p>
    <p>Your account is due for renewal. Kindly perform renewal as soon as possible.</p>
@endsection