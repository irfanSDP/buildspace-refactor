@extends('notifications.email.base')

@section('content')
    <p>{{ trans('projects.projectTitle') }}: {{{ $project }}}</p>
    <p>{{ trans('projectReport.title') }}: {{{ $title }}}</p>
    <p>{{ trans('projectReport.revision') }}: {{{ $revision }}}</p>
    <p>{{ trans('projectReport.readyForEditing') }}</p>
@endsection