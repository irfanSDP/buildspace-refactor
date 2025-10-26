@extends('notifications.email.base')

@section('content')
    @if (! empty($projectTitle))
        <p>{{ trans('projects.projectTitle') }}: {{{ $projectTitle }}}</p>
    @endif
    @if (! empty($reportTitle))
        <p>{{ trans('projectReport.title') }}: {{{ $reportTitle }}}</p>
    @endif
    @if (! empty($body))
        <p>{{ nl2br($body) }}</p>
    @endif
@endsection