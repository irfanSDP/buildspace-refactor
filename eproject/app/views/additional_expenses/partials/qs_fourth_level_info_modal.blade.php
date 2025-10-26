<!-- Modal -->
<div class="modal" id="step-4-{{ $message->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">
					{{{ PCK\Forms\AEMessageFourthLevelArchitectQsForm::formTitleTwo }}}
				</h4>
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			</div>

			<div class="modal-body">
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
					<strong>Letter to the Architect:</strong><br>
					{{{ $message->message }}}
				</p>

				<p>
					<strong>Decision of the QS Consultant:</strong><br>
					@if ( $message->decision == PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage::REJECT )
						Application Rejected
					@else
						QS granted ({{{ $ae->project->modified_currency_code }}}) {{{ number_format($message->grant_different_amount, 2) }}}
					@endif
				</p>

				@if ( ! $message->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $ae->project_id])
					</p>
				@endif
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>