<table id="email_inbox-table" class="table table-bordered table-condensed table-striped table-hover">
    <thead>
        <tr>
            <th style="width:auto;">{{ trans('messaging.subject') }}</th>
            <th style="width: 320px;">{{ trans('messaging.message') }}</th>
            <th class="text-center" style="width: 240px;">{{ trans('messaging.author') }}</th>
            <th class="text-center" style="width: 180px;">{{ trans('general.createdAt') }}</th>
        </tr>
    </thead>
    <tbody>
        @if (empty($emails))
            <tr>
                <td style="text-align: center;" colspan="4">{{ trans('messaging.currentlyNoMessages') }}</td>
            </tr>
        @else
            @foreach ( $emails as $email )
                <tr id="email{{{ $email->id }}}">
                    <td 
                        class="inbox-email-subject" data-is-draft="{{{ $email->isDraft() ? 'true' : 'false' }}}" 
                        @if ($email->isDraft())
                            data-edit-url="{{ route('email_notifications.edit', [$email->project_id, $email->id]) }}"
                        @endif 
                        data-email-url="{{ route('email_notifications.show', [$email->project_id, $email->id]); }}">
                        {{{ \PCK\Helpers\StringOperations::shorten($email->subject, 50) }}}
                    </td>
                    <td class="inbox-email-message" data-is-draft="{{{ $email->isDraft() ? 'true' : 'false' }}}" 
                        @if ($email->isDraft())
                            data-edit-url="{{ route('email_notifications.edit', [$email->project_id, $email->id]) }}"
                        @endif 
                        data-email-url="{{ route('email_notifications.show', [$email->project_id, $email->id]) }}">
                        {{{ \PCK\Helpers\StringOperations::shorten($email->message, 50) }}}
                    </td>
                    <td class="inbox-email-author text-center" data-is-draft="{{{ $email->isDraft() ? 'true' : 'false' }}}" 
                        @if ($email->isDraft())
                            data-edit-url="{{ route('email_notifications.edit', [$email->project_id, $email->id]) }}"
                        @endif 
                        data-email-url="{{ route('email_notifications.show', [$email->project_id, $email->id]) }}">
                        {{{ \PCK\Helpers\StringOperations::shorten($email->createdBy->name, 30) }}}
                    </td>
                    <td class="inbox-email-datetime text-center" data-is-draft="{{{ $email->isDraft() ? 'true' : 'false' }}}" 
                        @if ($email->isDraft())
                            data-edit-url="{{ route('email_notifications.edit', [$email->project_id, $email->id]) }}"
                        @endif 
                        data-email-url="{{ route('email_notifications.show', [$email->project_id, $email->id]) }}">
                        {{{ $email->project->getProjectTimeZoneTime($email->updated_at) }}}
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>