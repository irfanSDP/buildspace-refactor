<div id="s2" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		<div>
			<h4 role="tab" aria-selected="false" tabindex="0" id="{{ str_replace('%id%', $ae->contractorConfirmDelay->id, PCK\Forms\AEContractorConfirmDelayForm::accordianId) }}">
				{{{ PCK\Forms\AEContractorConfirmDelayForm::formTitle }}}
			</h4>

			<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
				<p>
					<strong>Subject/Reference:</strong><br>
					{{{ $ae->contractorConfirmDelay->subject }}}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->created_at) }}}</span> by {{{ $ae->contractorConfirmDelay->createdBy->present()->byWhoAndRole($ae->project, $ae->created_at) }}}
				</p>

				<p>
					<strong>Date on which the matters referred to in the claim have ended:</strong><br>
					{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->date_on_which_delay_is_over) }}}
				</p>

				<p>
					<strong>Deadline to Submit the final Additional Expense Claim:</strong><br>
					{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</p>

				<p>
					<strong>Letter to Architect:</strong><br>
					{{{ $ae->contractorConfirmDelay->message }}}
				</p>

				@if ( ! $ae->contractorConfirmDelay->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ae->contractorConfirmDelay->attachments, 'projectId' => $ae->project_id])
					</p>
				@endif
			</div>
		</div>

		<?php $messageCount = 0; ?>

		@foreach ( $ae->secondLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				@include('additional_expenses.partials.architect_second_level_info_tab', array('message' => $message))
			@else
				@include('additional_expenses.partials.contractor_second_level_info_tab', array('message' => $message))
			@endif

			<?php $messageCount++; ?>
		@endforeach
	</div>
</div>