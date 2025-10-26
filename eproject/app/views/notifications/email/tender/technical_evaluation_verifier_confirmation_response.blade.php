@extends('notifications.email.base')

@section('content')
    <?php $response = $additionalData['confirmationResponse'] ? trans('general.accepted') : trans('general.declined');  ?>
    <p>{{ trans('email/openTender.technicalOpeningResponse', ['senderName' => $additionalData['senderName'], 'response' => $response], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.projectName', ['projectName' => $project['title']], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $modelName]) }}</p>

    <p>{{ trans('email.workCategory', ['workCategory' => $workCategory], 'messages', $recipientLocale) }}</p>
@endsection