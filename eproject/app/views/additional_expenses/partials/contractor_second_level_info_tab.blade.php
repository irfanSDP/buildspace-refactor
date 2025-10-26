<div>
	<h4 role="tab" aria-selected="false" id="{{ str_replace('%id%', $message->id, PCK\Forms\AEMessageSecondLevelContractorForm::accordianId) }}">
		<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

		@if ( $messageCount == 0 )
			{{{ PCK\Forms\AEMessageSecondLevelContractorForm::formTitleOne }}}
		@else
			{{{ PCK\Forms\AEMessageSecondLevelContractorForm::formTitleTwo }}}
		@endif
	</h4>

	<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
		<p>
			<strong>Subject/Reference:</strong><br>
			{{{ $message->subject }}}
		</p>

		<p>
			<strong>Date Submitted:</strong><br>
			<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($ae->project, $ae->created_at) }}}
		</p>

		<p>
			<strong>Original Deadline for Submission:</strong><br>
			{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
		</p>

		<p>
			<strong>Requested New Deadline for Submission:</strong><br>
			{{{ $ae->project->getProjectTimeZoneTime($message->requested_new_deadline) }}}
		</p>

		<p>
			<strong>Letter to the Architect:</strong><br>
			{{{ $message->message }}}
		</p>

		@if ( ! $message->attachments->isEmpty() )
			<p>
				<strong>Attachment(s):</strong><br>

				@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $ae->project_id])
			</p>
		@endif
	</div>
</div>