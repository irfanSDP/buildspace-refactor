@extends('notifications.email.base')

@section('content')
    <p>
        Company: {{{ $companyName }}}
    </p>
    @if($expiryPassed)
        <p>Your company account has expired ({{{ $expiryDate }}}). You will need to renew it to participate in projects.</p>
    @else
        <p>Your company account will expire soon ({{{ $expiryDate }}}). Please renew it to participate in projects.</p>
    @endif
@endsection