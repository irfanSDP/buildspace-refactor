@extends('notifications.email.base')

@section('content')
    <p>
        Kindly issue a duly authorized Tax Invoice as per details below in your company's Letter Head and Company Chop:
    </p>

    <p>
        Workdone for Claim No {{{ $claimNo }}} (Cert Dated {{{ $certDueDate }}}): {{{ $currency }}} {{{ $amountBeforeTax }}}<br/>
        Tax {{{ $taxPercentage }}}% {{{ $currency }}} {{{ $taxAmount }}}<br/>
        Total Including Tax {{{ $taxPercentage }}}%: {{{ $currency }}} {{{ $totalAmount }}}
    </p>

    <p>
        Please make sure that the wording of 'Tax Invoice' and your GST Registration No are clearly printed in your Tax Invoice. And pass over this Tax Invoice to us when you come over to our office for collection of cheque. Please take note that we will not release any cheque without receiving the relevant proper Tax Inoice.
    </p>
@endsection