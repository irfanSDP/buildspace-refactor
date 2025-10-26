@extends('notifications.email.base')

@section('content')
    <p>{{ trans('costData.costData', [], 'messages', $recipientLocale) }}: {{{ $costDataName }}}</p>
    <p>
        {{ trans('email/costData.costDataInstructions', [], 'messages', $recipientLocale) }}
    </p>
@endsection