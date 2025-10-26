@extends('notifications.email.base')

@section('content')
    <p>Project Title : {{ $projectTitle }}</p>
    <p>Vendor : {{ $companyName }}</p>

    <p>VPE Cycle Start Date : {{ $cycleStartDate }}</p>
    <p>VPE Cycle End Date : {{ $cycleEndDate }}</p>

    <p>The VPE cycle is going to end soon. Please complete your evaluation process as soon as possible.</p>
    <p>If you have submitted, kindly ignore this message.</p>
@endsection