<div class="modal" id="aeInterimClaim-{{{ $ae->additionalExpenseInterimClaim->id }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">
					{{{ PCK\Forms\AdditionalExpenseInterimClaimForm::formTitle }}}
				</h4>
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			</div>

			<div class="modal-body">
				<p>
					<strong>Project:</strong><br>
					{{{ $ae->project->title }}}
				</p>

				<p>
					<strong>Contractor's Additional Expense Claim Reference:</strong><br>
					{{{ $ae->subject }}}
				</p>

				<p>
					<strong>Reference of the Interim Certificate in which the payment is made:</strong><br>
					{{ link_to_route('ic.show', "Interim Certificate No: {$ae->additionalExpenseInterimClaim->interimClaim->claim_no}", array($ae->project_id, $ae->additionalExpenseInterimClaim->interimClaim->id)) }}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseClaim->created_at) }}}</span> by {{{ $ae->additionalExpenseInterimClaim->createdBy->present()->byWhoAndRole($ae->project, $ae->created_at) }}}
				</p>

				@if ( ! $ae->additionalExpenseInterimClaim->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ae->additionalExpenseInterimClaim->attachments, 'projectId' => $ae->project_id])
					</p>
				@endif
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>