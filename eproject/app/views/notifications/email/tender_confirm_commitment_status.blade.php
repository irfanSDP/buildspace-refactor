<p>{{ trans('email/base.to', [], 'messages', $recipientLocale) }}: {{{ $recipientName }}}</p>

<p>
    {{ trans('projects.project', [], 'messages', $recipientLocale) }}: <strong>{{{ $projectTitle }}}</strong>
</p>

<table>
    <tr>
        <td>{{ trans('email.dateOfTenderCalling', [], 'messages', $recipientLocale) }} {{{ ($tenderStage != \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER) ? "(". trans('email.tentative', [], 'messages', $recipientLocale) .")" : "" }}}</td>
        <td>: <strong>{{{ $tenderCallingDate }}}</strong></td>
    </tr>
    <tr>
        <td>{{ trans('email.dateOfTenderClosing', [], 'messages', $recipientLocale) }} {{{ ($tenderStage != \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER) ? "(". trans('email.tentative', [], 'messages', $recipientLocale) .")" : "" }}}</td>
        <td>: <strong>{{{ $tenderClosingDate }}}</strong></td>
    </tr>
</table>

<p>
    {{ trans('email/confirmTenderCommitmentStatus.tenderInvitation', ['companyName' => $companyName, 'employerName' => $employerName], 'messages', $recipientLocale) }}
</p>

<p data-id="emailMessage">
    {{ $emailMessage }}
</p>

<p>
    @if($tenderStage != \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER)
        {{ trans('email.kindlyReplyByClickingLinkBelow', [], 'messages', $recipientLocale) }}:
        <br/>
        <br/>
        @if(isset($disableLink) && $disableLink)
            <a href="javascript:void(0)">{{{ $link }}}</a>
        @else
            {{ HTML::link($link) }}
        @endif
    @endif
</p>

<p>{{ trans('general.regards', [], 'messages', $recipientLocale) }}</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')