@extends('notifications.email.base')

@section('content')
    <p>Your Company ({{{ $company_name }}}) has been assigned to join Project ({{{ $project_title }}}).</p>
@endsection