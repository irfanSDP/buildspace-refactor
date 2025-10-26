<style>
    td {
        padding-right: 20px;
        padding-left:3px;
    }
</style>
{{ trans('email.toRecipientOfCompany', ['recipientName' => $recipientName, 'companyName' => $companyName], 'messages', $recipientLocale) }}

<p>
    {{ trans('projects.project', [], 'messages', $recipientLocale) }}: <strong>{{{ $projectTitle }}}</strong>
</p>

<hr/>

<p>
    {{ trans('email/callingTender.tenderClarificationMeeting', [], 'messages', $recipientLocale) }}
</p>

<hr/>

<p>
    {{ trans('email/callingTender.inviteFollowingTenderers', [], 'messages', $recipientLocale) }}:
</p>

<table>
    <tr>
        <td>
            &nbsp;
        </td>
        <td>
            <span style="text-align: center; text-decoration: underline">{{ trans('email.tenderer') }}</span>
        </td>
        <td>
            <span style="text-align: center; text-decoration: underline">{{ trans('email.time') }}</span>
        </td>
    </tr>
    <tr>
        <td style="text-align: right;">&nbsp;</td>
        <td style="text-align: left"><strong>{{{ strtoupper(trans('email.discussion')) }}}</strong></td>
        <td style="text-align: right" data-input="discussionTime">{{{ $discussionTime }}}</td>
    </tr>
    <?php $count = 1; ?>
    @foreach($tenderInterviews as $tenderInterview)
    <tr>
        <td style="text-align: right;">{{{ $count++ }}}.</td>
        <td style="text-align: left">{{{ $tenderInterview['company']['name'] }}}</td>
        <td style="text-align: right" data-id="{{{ $tenderInterview['company']['id'] }}}" data-type="tender-interview-preview-interview-time">{{{ $tenderInterview['date_and_time'] }}}</td>
    </tr>
    @endforeach
</table>

<br/>

<table>
    <tr>
        <th style="text-align: left">{{ trans('email.date') }}</th>
        <td style="width: 20px; text-align: left;">:</td>
        <td style="text-align: left" data-input="date">{{{ $date }}}</td>
    </tr>
    <tr>
        <th style="text-align: left">{{ trans('email.venue') }}</th>
        <td style="width: 20px; text-align: left;">:</td>
        <td style="text-align: left" data-input="venue">{{{ $venue }}}</td>
    </tr>
</table>

<p>
    {{ trans('email/callingTender.pleasePrepare', [], 'messages', $recipientLocale) }}
</p>

<p>{{ trans('general.regards', [], 'messages', $recipientLocale) }}</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')