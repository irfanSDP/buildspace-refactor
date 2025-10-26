@extends('notifications.email.base')

@section('content')
    <p>Project Title : {{ $projectTitle }}</p>

    <p>VPE Cycle Start Date : {{ $cycleStartDate }}</p>
    <p>VPE Cycle End Date : {{ $cycleEndDate }}</p>

    You have been assigned as an evaluator for the above Vendor Performance Evaluation Cycle.
@endsection