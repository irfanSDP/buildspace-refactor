<p>You have one unread message</p>
{{ trans('email.visitThisLinkForMoreInformation') }}: <br/>
<a href="{{{ $route }}}">{{{ $route }}}</a>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')