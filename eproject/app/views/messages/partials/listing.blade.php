<table id="message_inbox-table" class="table table-bordered table-condensed table-striped table-hover">
	<thead>
		<tr>
			<th style="width:auto;">{{ trans('messaging.subject') }}</th>
			<th class="text-center" style="width: 380px;">{{ trans('messaging.author') }}</th>
			<th class="text-center" style="width: 180px;">{{ trans('messaging.purposeOfIssue') }}</th>
			<th class="text-center" style="width: 180px;">{{ trans('general.createdAt') }}</th>
		</tr>
	</thead>
	<tbody>
		@if ( $conversations->isEmpty() )
			<tr>
				<td style="text-align: center;" colspan="4">{{ trans('messaging.currentlyNoMessages') }}</td>
			</tr>
		@else
			@foreach ( $conversations as $conversation )
				<tr id="msg{{{ $conversation->id }}}" class="{{{ ! $conversation->viewerGroups[0]->pivot->read ? 'danger' : null }}}">
					<td class="inbox-data-subject" data-is-draft="{{{ $conversation->isDraft() ? route('message.edit', array($conversation->project_id, $conversation->id)) : false }}}" data-is-draft="{{{ $conversation->isDraft() }}}" data-message-url="{{{ route('message.show', [$conversation->project_id, $conversation->id]) }}}">
						{{{ \PCK\Helpers\StringOperations::shorten($conversation->subject, 50) }}}
					</td>
					<td class="inbox-data-from text-center" data-is-draft="{{{ $conversation->isDraft() ? route('message.edit', array($conversation->project_id, $conversation->id)) : false }}}" data-message-url="{{{ route('message.show', [$conversation->project_id, $conversation->id]) }}}">
						{{{ \PCK\Helpers\StringOperations::shorten($conversation->createdBy->name, 30) }}}
						<br />
						{{{ \PCK\Helpers\StringOperations::shorten($conversation->createdBy->getProjectCompanyName($conversation->project, $conversation->created_at), 50) }}}
					</td>
					<td class="inbox-data-purpose-of-issue text-center" data-is-draft="{{{ $conversation->isDraft() ? route('message.edit', array($conversation->project_id, $conversation->id)) : false }}}" data-message-url="{{{ route('message.show', [$conversation->project_id, $conversation->id]) }}}">
						{{ $conversation->purpose_of_issued }}
					</td>
					<td class="inbox-data-date text-center">
						{{{ $conversation->project->getProjectTimeZoneTime($conversation->updated_at) }}}
					</td>
				</tr>
			@endforeach
		@endif
	</tbody>
</table>
<div id="inbox-pagination-links" class="pull-right">
	{{ $conversations->appends(array('messageType' => 'messageTypePlaceholder'))->links() }}
</div>