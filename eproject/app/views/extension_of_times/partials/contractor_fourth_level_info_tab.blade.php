<div>
	<h4 role="tab" aria-selected="false" id="{{{ str_replace('%id%', $message->id, PCK\Forms\EOTMessageFourthLevelContractorForm::accordianId) }}}">
		<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

		{{{ PCK\Forms\EOTMessageFourthLevelContractorForm::formTitle }}}
	</h4>

	<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
		<p>
			<strong>Final EOT Claim's Reference:</strong><br>
			{{{ $message->extensionOfTime->extensionOfTimeClaim->subject }}}
		</p>

		<p>
			<strong>Notice of Intention To Claim EOT's Reference:</strong><br>
			{{{ $message->extensionOfTime->subject }}}
		</p>

		<p>
			<strong>Subject/Reference:</strong><br>
			{{{ $message->subject }}}
		</p>

		<p>
			<strong>Date Submitted:</strong><br>
			<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
		</p>

		<p>
			<strong>Deadline for Submission:</strong><br>
			{{{ $eot->project->getProjectTimeZoneTime($message->extensionOfTime->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
		</p>

		<p>
			<strong>Date of Submission:</strong><br>
			{{{ $eot->project->getProjectTimeZoneTime($message->extensionOfTime->extensionOfTimeClaim->created_at) }}}
		</p>

		<p>
			<strong>EOT Claimed:</strong><br>
			{{{ $message->extensionOfTime->extensionOfTimeClaim->days_claimed }}} day(s)
		</p>

		<p>
			<strong>Letter to the Architect:</strong><br>
			{{{ $message->message }}}
		</p>

		@if ( ! $message->attachments->isEmpty() )
			<p>
				<strong>Attachment(s):</strong><br>

				@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $eot->project_id])
			</p>
		@endif
	</div>
</div>