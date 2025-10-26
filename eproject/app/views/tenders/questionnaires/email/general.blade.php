@if(isset($recipientName))
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Dear {{{ $recipientName }}} @if(isset($recipientCompany))({{{ $recipientCompany }}})@endif</p>
@endif

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>{{{ trans('projects.projectTitle') }}}:</strong> {{{$projectTitle}}}</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>{{{ trans('projects.contractNumber') }}}:</strong> {{{$contractNumber}}}</p>

@yield('content')

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')