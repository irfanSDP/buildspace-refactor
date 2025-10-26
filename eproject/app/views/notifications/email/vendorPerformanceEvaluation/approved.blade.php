@extends('notifications.email.base')

@section('content')
    <p>Project Title : {{ $project }}</p>
    <p>Vendor : {{ $company }}</p>
    <p>Vendor Work Category : {{ $vendorWorkCategory }}</p>

    <p>VPE Cycle Start Date : {{ $cycleStartDate }}</p>
    <p>VPE Cycle End Date : {{ $cycleEndDate }}</p>

    <p>VPE form has been approved successfully.</p>
@endsection