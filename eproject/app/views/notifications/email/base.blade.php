@if(isset($recipientName))
    <p>{{ trans('email/base.to', [], 'messages', $recipientLocale) }}: {{{ $recipientName }}} @if(isset($recipientCompany))({{{ $recipientCompany }}})@endif</p>
@endif

@yield('content')

@if(isset($toRoute))
    <p>{{ trans('email.visitThisLinkForMoreInformation', [], 'messages', $recipientLocale) }}:</p>

    <p><a href="{{{ $toRoute }}}">{{{ $toRoute }}}</a></p>
@endif

<p>{{ trans('confide::confide.email.account_confirmation.farewell') }}</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')