@extends('notifications.email.base')

@section('content')
    <p>{{ trans('email/listOfTenderer.lotRequestForPayment', [], 'messages', $recipientLocale) }}.</p>

    <p>{{ trans('email.projectName', ['projectName' => $projectTitle], 'messages', $recipientLocale) }}</p>

    <p>{{ trans('email.tenderName', ['tenderName' => $current_tender_name], 'messages', $recipientLocale) }}</p>

    <p><a href="{{$paymentLink}}">{{ trans('email/listOfTenderer.linkToPaymentGateway') }}</a></p>


@endsection