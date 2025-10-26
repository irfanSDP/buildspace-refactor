{{ trans('email.toRecipientOfCompany', ['recipientName' => $recipientName, 'companyName' => $companyName], 'messages', $recipientLocale) }}

<p>
    {{ trans('projects.project', [], 'messages', $recipientLocale) }}: <strong>{{{ $projectTitle }}}</strong>
</p>

<hr/>

<p>
    {{ trans('email/callingTender.noticeOfTenderInterview', [], 'messages', $recipientLocale) }}
</p>

<hr/>

<p>
    {{ trans('email/callingTender.youHaveBeenInvited', [], 'messages', $recipientLocale) }}:
</p>

<table>
    <tr>
        <th style="text-align: left">{{ trans('email.date') }}</th>
        <td style="text-align: left" data-input="date">: {{{ $date }}}</td>
    </tr>
    <tr>
        <th style="text-align: left">{{ trans('email.time') }}</th>
        <td style="text-align: left">: {{{ $time }}}</td>
    </tr>
    <tr>
        <th style="text-align: left">{{ trans('email.venue') }}</th>
        <td style="text-align: left" data-input="venue">: {{{ $venue }}}</td>
    </tr>
</table>

<p>
    {{ trans('email/callingTender.confirmYourAttendance', [], 'messages', $recipientLocale) }}:<br/>
    @if(isset($disableLink) && $disableLink)
        <a href="javascript:void(0)">{{{ $link }}}</a>
    @else
        {{ HTML::link($link) }}
    @endif
</p>

<p>{{ trans('general.regards', [], 'messages', $recipientLocale) }}</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')