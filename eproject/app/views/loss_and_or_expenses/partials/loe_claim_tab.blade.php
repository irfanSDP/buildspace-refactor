<div id="s3" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		<div>
			<h4 role="tab" aria-selected="false" tabindex="0" id="{{{ str_replace('%id%', $loe->lossOrAndExpenseClaim->id, PCK\Forms\LOEClaimForm::accordianId) }}}">
				{{{ PCK\Forms\LOEClaimForm::formTitle }}}
			</h4>

			<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
				<p>
					<strong>Subject/Reference:</strong><br>
					{{{ $loe->lossOrAndExpenseClaim->subject }}}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}</span> by {{{ $loe->lossOrAndExpenseClaim->createdBy->present()->byWhoAndRole($loe->project, $loe->created_at) }}}
				</p>

				<p>
					<strong>Deadline to Submit the final Loss And/Or Expense Claim:</strong><br>
					{{{ $loe->project->getProjectTimeZoneTime($loe->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</p>

				<p>
					<strong>Detailed Elaborations to Substantiate Claim/Cover Letter:</strong><br>
					{{{ $loe->lossOrAndExpenseClaim->message }}}
				</p>

				<p>
					<strong>Final Claim Amount ({{{ $loe->project->modified_currency_code }}}):</strong><br>
					{{{ number_format($loe->lossOrAndExpenseClaim->final_claim_amount, 2) }}}
				</p>

				@if ( ! $loe->lossOrAndExpenseClaim->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $loe->lossOrAndExpenseClaim->attachments, 'projectId' => $loe->project_id])
					</p>
				@endif
			</div>
		</div>

		@foreach ( $loe->thirdLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
				@include('loss_and_or_expenses.partials.contractor_third_level_info_tab', array('message' => $message))
			@else
				@include('loss_and_or_expenses.partials.architect_qs_third_level_info_tab', array('message' => $message))
			@endif
		@endforeach
	</div>
</div>