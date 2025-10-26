<div>
	<h4 role="tab" aria-selected="false" id="{{ str_replace('%id%', $message->id, PCK\Forms\AEMessageFourthLevelArchitectQsForm::accordianId) }}">
		<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

		{{{ PCK\Forms\AEMessageFourthLevelArchitectQsForm::formTitleOne }}}
	</h4>

	<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
		<p>
			<strong>Final Additional Expense Claim's Reference:</strong><br>
			{{{ $message->additionalExpense->additionalExpenseClaim->subject }}}
		</p>

		<p>
			<strong>Notice of Intention To Claim Additional Expense's Reference:</strong><br>
			{{{ $message->additionalExpense->subject }}}
		</p>

		<p>
			<strong>Subject/Reference:</strong><br>
			{{{ $message->subject }}}
		</p>

		<p>
			<strong>Date Submitted:</strong><br>
			<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($ae->project, $ae->created_at) }}}
		</p>

		<p>
			<strong>Deadline for Submission:</strong><br>
			{{{ $ae->project->getProjectTimeZoneTime($message->additionalExpense->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
		</p>

		<p>
			<strong>Date of Submission:</strong><br>
			{{{ $ae->project->getProjectTimeZoneTime($message->additionalExpense->additionalExpenseClaim->created_at) }}}
		</p>

		<p>
			<strong>Final Claim Amount ({{{ $ae->project->modified_currency_code }}}):</strong><br>
			{{{ number_format($message->additionalExpense->additionalExpenseClaim->final_claim_amount, 2) }}}
		</p>

		<p>
			<strong>Letter to the Contractor:</strong><br>
			{{{ $message->message }}}
		</p>

		<p>
			<strong>Decision of the Architect:</strong><br>
			@include('additional_expense_fourth_level_messages.partials.architect_decision', array('aeLastArchitectMessage' => $message))
		</p>

		@if ( ! $message->attachments->isEmpty() )
			<p>
				<strong>Attachment(s):</strong><br>

				@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $ae->project_id])
			</p>
		@endif
	</div>
</div>