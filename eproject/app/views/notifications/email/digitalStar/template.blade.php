@if(isset($recipientName))
    <p>{{ trans('digitalStar/email.to') }}: {{ $recipientName }} @if(! empty($recipientCompany))({{ $recipientCompany }})@endif</p>
@endif

@if (! empty($projectTitle))
    <p>{{ trans('digitalStar/email.projectTitle') }} : {{ $projectTitle }}</p>
@endif
@if (! empty($contractNo))
    <p>{{ trans('digitalStar/email.contractNo') }} : {{ $contractNo }}</p>
@endif

<p>{{ trans('digitalStar/email.vendor') }} : {{ $companyName }}</p>
<p>{{ trans('digitalStar/email.digitalStarCycleStartDate') }} : {{ $cycleStartDate }}</p>
<p>{{ trans('digitalStar/email.digitalStarCycleEndDate') }} : {{ $cycleEndDate }}</p>

@yield('message')

@if(isset($link))
    <p>{{ trans('digitalStar/email.visitThisLinkForMoreInformation') }}:</p>
    <p><a href="{{ $link }}">{{ trans('digitalStar/email.clickToView') }}</a></p>
@endif

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')