@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/openTenderAwardRecommendation.awardRecommendationApproved', [], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email.projectName', ['projectName' => $project_title], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $current_tender_name], 'messages', $recipientLocale) }}</p>
@endsection