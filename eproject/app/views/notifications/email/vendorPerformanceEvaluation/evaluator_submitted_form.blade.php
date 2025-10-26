@extends('notifications.email.base')

@section('content')
    <p>Project Title : {{ $projectTitle }}</p>
    <p>Vendor : {{ $companyName }}</p>
    <p>Vendor Work Category : {{ $vendorWorkCategory }}</p>

    <p>VPE Cycle Start Date : {{ $cycleStartDate }}</p>
    <p>VPE Cycle End Date : {{ $cycleEndDate }}</p>

    The VPE evaluation form has been submitted by {{ $submitter }}.
@endsection