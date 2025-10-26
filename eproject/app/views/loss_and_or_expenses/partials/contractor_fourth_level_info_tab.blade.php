<div>
	<h4 role="tab" aria-selected="false" id="{{{ str_replace('%id%', $message->id, PCK\Forms\LOEMessageFourthLevelContractorForm::accordianId) }}}">
		<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

		{{{ PCK\Forms\LOEMessageFourthLevelContractorForm::formTitle }}}
	</h4>

	<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
		<p>
			<strong>Final Loss And/Or Expense Claim's Reference:</strong><br>
			{{{ $message->lossOrAndExpense->lossOrAndExpenseClaim->subject }}}
		</p>

		<p>
			<strong>Notice of Intention To Claim Loss And/Or Expense's Reference:</strong><br>
			{{{ $message->lossOrAndExpense->subject }}}
		</p>

		<p>
			<strong>Subject/Reference:</strong><br>
			{{{ $message->subject }}}
		</p>

		<p>
			<strong>Date Submitted:</strong><br>
			<span class="dateSubmitted">{{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($loe->project, $loe->created_at) }}}
		</p>

		<p>
			<strong>Deadline for Submission:</strong><br>
			{{{ $loe->project->getProjectTimeZoneTime($message->lossOrAndExpense->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
		</p>

		<p>
			<strong>Date of Submission:</strong><br>
			{{{ $loe->project->getProjectTimeZoneTime($message->lossOrAndExpense->lossOrAndExpenseClaim->created_at) }}}
		</p>

		<p>
			<strong>Final Claim Amount ({{{ $loe->project->modified_currency_code }}}):</strong><br>
			{{{ number_format($message->lossOrAndExpense->lossOrAndExpenseClaim->final_claim_amount, 2) }}}
		</p>

		<p>
			<strong>Letter to the Architect:</strong><br>
			{{{ $message->message }}}
		</p>

		@if ( ! $message->attachments->isEmpty() )
			<p>
				<strong>Attachment(s):</strong><br>

				@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $loe->project_id])
			</p>
		@endif
	</div>
</div>