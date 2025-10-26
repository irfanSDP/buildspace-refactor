@extends('notifications.email.base')

@section('content')
    <p>
        Your payment for [{{{ $projectReference }}}] Claim No. {{{ $claimNo }}} is now ready for collection.
    </p>
@endsection