<div>
	<h4 role="tab" aria-selected="false" id="{{{ str_replace('%id%', $message->id, PCK\Forms\LOEMessageThirdLevelArchitectQsForm::accordianId) }}}">
		<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

		@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
			{{{ PCK\Forms\LOEMessageThirdLevelArchitectQsForm::formTitleOne }}}
		@else
			{{{ PCK\Forms\LOEMessageThirdLevelArchitectQsForm::formTitleTwo }}}
		@endif
	</h4>

	<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
		<p>
			<strong>Subject/Reference:</strong><br>
			{{{ $message->subject }}}
		</p>

		<p>
			<strong>Date Submitted:</strong><br>
			<span class="dateSubmitted">{{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($loe->project, $loe->created_at) }}}
		</p>

		<p>
			<strong>Cover Letter:</strong><br>
			{{{ $message->message }}}
		</p>

		<p>
			<strong>Deadline to Comply With:</strong><br>
			{{{ $loe->project->getProjectTimeZoneTime($message->deadline_to_comply_with) }}}
		</p>

		@if ( ! $message->attachments->isEmpty() )
			<p>
				<strong>Attachment(s):</strong><br>

				@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $loe->project_id])
			</p>
		@endif
	</div>
</div>