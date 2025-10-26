@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email.projectName', ['projectName' => $projectName], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email/project_documents.documentsUploadedAndShared', [], 'messages', $recipientLocale) }}</p>
@endsection