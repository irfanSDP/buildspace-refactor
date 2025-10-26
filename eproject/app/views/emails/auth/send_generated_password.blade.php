<h1>{{ trans('auth.thankYouForConfirmingYourAccount') }}</h1>

<p>{{ trans('confide::confide.email.account_confirmation.greetings', array('name' => $name)) }},</p>

<p>{{ trans('auth.youCanNowLogin', array('route' => route('users.login'))) }}:</p>

<p><strong>{{ trans('auth.username') }}:</strong> {{{ $loginEmail }}}</p>
<p><strong>{{ trans('auth.password') }}:</strong> {{{ $password }}}</p>

<p>{{ trans('confide::confide.email.account_confirmation.farewell') }}</p>

@include('notifications.email.partials.company_logo')