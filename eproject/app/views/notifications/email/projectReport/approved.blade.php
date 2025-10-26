@extends('notifications.email.base')

@section('content')
    <p>{{ trans('projectReport.projectReportApproved', ['senderName' => $senderName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $project_title], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('projectReport.projectReportType', ['projectReportType' => $project_report_type], 'messages', $recipientLocale) }}: {{ $project_report_type }}</p>

    <p>{{ trans('projectReport.projectReportTitle', ['projectReportTitle' => $project_report_title], 'messages', $recipientLocale) }}: {{ $project_report_title }}</p>

    <p>{{ trans('projectReport.revision', ['projectReportRevision' => $project_report_revision], 'messages', $recipientLocale) }}: {{ $project_report_revision }}</p>
@endsection