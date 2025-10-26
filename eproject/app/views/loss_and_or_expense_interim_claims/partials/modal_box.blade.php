<div class="modal" id="loeInterimClaim-{{{ $loe->lossOrAndExpenseInterimClaim->id }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">
					{{{ PCK\Forms\LossAndOrExpenseInterimClaimForm::formTitle }}}
				</h4>
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			</div>

			<div class="modal-body">
				<p>
					<strong>Project:</strong><br>
					{{{ $loe->project->title }}}
				</p>

				<p>
					<strong>Contractor's L &amp; E Claim Reference:</strong><br>
					{{{ $loe->subject }}}
				</p>

				<p>
					<strong>Reference of the Interim Certificate in which the payment is made:</strong><br>
					{{ link_to_route('ic.show', "Interim Certificate No: {$loe->lossOrAndExpenseInterimClaim->interimClaim->claim_no}", array($loe->project_id, $loe->lossOrAndExpenseInterimClaim->interimClaim->id)) }}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseInterimClaim->created_at) }}}</span> by {{{ $loe->lossOrAndExpenseInterimClaim->createdBy->present()->byWhoAndRole($loe->project, $loe->created_at) }}}
				</p>

				@if ( ! $loe->lossOrAndExpenseInterimClaim->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $loe->lossOrAndExpenseInterimClaim->attachments, 'projectId' => $loe->project_id])
					</p>
				@endif
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>