<div id="s3" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		<div>
			<h4 role="tab" aria-selected="false" tabindex="0" id="{{ str_replace('%id%', $ae->additionalExpenseClaim->id, PCK\Forms\AEClaimForm::accordianId) }}">
				{{{ PCK\Forms\AEClaimForm::formTitle }}}
			</h4>

			<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
				<p>
					<strong>Subject/Reference:</strong><br>
					{{{ $ae->additionalExpenseClaim->subject }}}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseClaim->created_at) }}}</span> by {{{ $ae->additionalExpenseClaim->createdBy->present()->byWhoAndRole($ae->project, $ae->created_at) }}}
				</p>

				<p>
					<strong>Deadline to Submit the final Additional Expense Claim:</strong><br>
					{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</p>

				<p>
					<strong>Detailed Elaborations to Substantiate Claim/Cover Letter:</strong><br>
					{{{ $ae->additionalExpenseClaim->message }}}
				</p>

				<p>
					<strong>Final Claim Amount ({{{ $ae->project->modified_currency_code }}}):</strong><br>
					{{{ number_format($ae->additionalExpenseClaim->final_claim_amount, 2) }}}
				</p>

				@if ( ! $ae->additionalExpenseClaim->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ae->additionalExpenseClaim->attachments, 'projectId' => $ae->project_id])
					</p>
				@endif
			</div>
		</div>

		@foreach ( $ae->thirdLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
				@include('additional_expenses.partials.contractor_third_level_info_tab', array('message' => $message))
			@else
				@include('additional_expenses.partials.architect_qs_third_level_info_tab', array('message' => $message))
			@endif
		@endforeach
	</div>
</div>