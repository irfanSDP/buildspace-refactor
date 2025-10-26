<div class="modal" id="aiInterimClaim-{{{ $ai->architectInstructionInterimClaim->id }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">
					{{{ PCK\Forms\ArchitectInstructionInterimClaimForm::formTitle }}}
				</h4>
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			</div>

			<div class="modal-body">
				<p>
					<strong>Project:</strong><br>
					{{{ $ai->project->title }}}
				</p>

				<p>
					<strong>AI's Reference:</strong><br>
					{{{ $ai->reference }}}
				</p>

				<p>
					<strong>Subject/Reference:</strong><br>
					{{{ $ai->architectInstructionInterimClaim->subject }}}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $ai->project->getProjectTimeZoneTime($ai->architectInstructionInterimClaim->created_at) }}}</span> by {{{ $ai->architectInstructionInterimClaim->createdBy->present()->byWhoAndRole($ai->project, $ai->created_at) }}}
				</p>

				<p>
					<strong>Letter to the Contractor:</strong><br>
					{{{ $ai->architectInstructionInterimClaim->letter_to_contractor }}}
				</p>

				<p>
					<strong>Set-off is made in the following Interim Certificate:</strong><br>
					{{ link_to_route('ic.show', "Interim Certificate No: {$ai->architectInstructionInterimClaim->interimClaim->claim_no}", array($ai->project_id, $ai->architectInstructionInterimClaim->interimClaim->id)) }}
				</p>

				@if ( ! $ai->architectInstructionInterimClaim->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ai->architectInstructionInterimClaim->attachments, 'projectId' => $ai->project_id])
					</p>
				@endif
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>