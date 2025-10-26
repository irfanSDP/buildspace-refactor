<p>{{ trans('email.to', [], 'messages', $recipientLocale) }}: {{{ $recipientName }}}</p>

<p>
    {{ trans('projects.project', [], 'messages', $recipientLocale) }}: <strong>{{{ $projectTitle }}}</strong>
</p>

<p>
    {{ trans('email/confirmTenderCommitmentStatus.replyHasBeenGivenBy', ['companyName' => $companyName, 'repliedAtTime' => $repliedAtTime], 'messages', $recipientLocale) }}.
</p>

<p>
    {{ trans('email.reply', [], 'messages', $recipientLocale) }}: <strong>{{{ $status }}}</strong>
</p>

<p>
    @if($loggedInUser)
        {{ trans('email/confirmTenderCommitmentStatus.replyWasSentWhen', ['loggedInUser' => $loggedInUser['name'], 'companyName' => \PCK\Companies\Company::find($loggedInUser['company_id'])->name], 'messages', $recipientLocale) }}    
    @endif
</p>

<p>{{ trans('general.regards', [], 'messages', $recipientLocale) }}</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')