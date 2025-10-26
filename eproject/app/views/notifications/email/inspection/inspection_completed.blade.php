@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/general.projectTitle', ['projectTitle' => $project_title], 'message', $recipientLocale) }}</p>

    <p>{{ trans('email/general.description', ['description' => $description], 'message', $recipientLocale) }}.</p>

    <p>{{ trans('email/inspection.inspectionCompletedByAllParties', [], 'message', $recipientLocale) }}</p>
@endsection