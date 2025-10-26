@extends('notifications.email.base')

@section('content')
    <p>Project Title : {{ $projectTitle }}</p>
    <p>Vendor : {{ $companyName }}</p>
    <p>Vendor Work Category : {{ $vendorWorkCategory }}</p>

    <p>VPE Cycle Start Date : {{ $cycleStartDate }}</p>
    <p>VPE Cycle End Date : {{ $cycleEndDate }}</p>

    <p>
        User {{{ $requestor }}} has requested to change the VPE form due to the following reason:
    </p>
    @if($remarks)
    <p>{{ nl2br($remarks) }}</p>
    @endif
@endsection