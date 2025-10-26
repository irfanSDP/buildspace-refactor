<div>
	<h4 role="tab" aria-selected="false" id="{{{ str_replace('%id%', $message->id, PCK\Forms\EOTMessageSecondLevelArchitectForm::accordianId) }}}">
		<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

		{{{ PCK\Forms\EOTMessageSecondLevelArchitectForm::formTitle }}}
	</h4>

	<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
		<p>
			<strong>Subject/Reference:</strong><br>
			{{{ $message->subject }}}
		</p>

		<p>
			<strong>Date Submitted:</strong><br>
			<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
		</p>

		<p>
			<strong>Original Deadline for Submission:</strong><br>
			{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
		</p>

		<p>
			<strong>Requested New Deadline for Submission:</strong><br>
			{{{ $eot->project->getProjectTimeZoneTime($message->requested_new_deadline) }}}
		</p>

		@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
			<p>
				<strong>Decision of the Architect:</strong><br>
				@if ( $message->decision == PCK\ExtensionOfTimeSecondLevelMessages\ExtensionOfTimeSecondLevelMessage::REJECT_DEADLINE )
					Application Rejected
				@else
					Deadline extended to {{{ $eot->project->getProjectTimeZoneTime($message->grant_different_deadline) }}}
				@endif
			</p>

			<p>
				<strong>Letter to Contractor:</strong><br>
				{{{ $message->message }}}
			</p>
		@else
			<p>
				<strong>Letter to Architect:</strong><br>
				{{{ $message->message }}}
			</p>
		@endif

		@if ( ! $message->attachments->isEmpty() )
			<p>
				<strong>Attachment(s):</strong><br>

				@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $eot->project_id])
			</p>
		@endif
	</div>
</div>