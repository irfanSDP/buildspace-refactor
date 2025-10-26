<h1>{{ $subject }}</h1>

<p>{{ trans('confide::confide.email.account_confirmation.greetings', array('name' => $name)) }},</p>

<p>{{ trans('confide::confide.email.account_confirmation.body') }}</p>
<a href='{{{ route('users.confirm', array($confirmationCode)) }}}'>
    {{{ route('users.confirm', array($confirmationCode)) }}}
</a>

<p>
    @if(isset($additionalContent))
        {{ nl2br($additionalContent) }}
    @endif
</p>

<p>{{ trans('confide::confide.email.account_confirmation.farewell') }}</p>

@include('notifications.email.partials.company_logo')