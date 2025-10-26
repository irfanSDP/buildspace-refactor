<h1>{{ trans('confide::confide.email.password_reset.subject') }}</h1>

<p>{{ trans('confide::confide.email.password_reset.greetings', array( 'name' => $user['name'])) }},</p>

<p>{{ trans('confide::confide.email.password_reset.body') }}</p>
<a href='{{ route('users.resetPassword', array($token)) }}'>
    {{ route('users.resetPassword', array($token))  }}
</a>

<p>{{ trans('confide::confide.email.password_reset.farewell') }}</p>

@include('notifications.email.partials.company_logo')