@extends('notifications.email.base')

@section('content')
    <p>Project Title : {{ $projectTitle }}</p>
    <p>Vendor : {{ $companyName }}</p>

    <p>VPE Cycle Start Date : {{ $cycleStartDate }}</p>
    <p>VPE Cycle End Date : {{ $cycleEndDate }}</p>

    <p>{{ $requestor }} has sent a VPE Project Removal Request.</p>

    @if($remarks != '')
    <p>{{ nl2br($remarks) }}</p>
    @endif
@endsection