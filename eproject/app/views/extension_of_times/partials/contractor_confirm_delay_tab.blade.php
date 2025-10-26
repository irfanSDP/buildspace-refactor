<div id="s2" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		<div>
			<h4 role="tab" aria-selected="false" tabindex="0" id="{{{ str_replace('%id%', $eot->eotContractorConfirmDelay->id, PCK\Forms\EOTContractorConfirmDelayForm::accordianId) }}}">
				{{{ PCK\Forms\EOTContractorConfirmDelayForm::formTitle }}}
			</h4>

			<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
				<p>
					<strong>Subject/Reference:</strong><br>
					{{{ $eot->eotContractorConfirmDelay->subject }}}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->created_at) }}}</span> by {{{ $eot->eotContractorConfirmDelay->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
				</p>

				<p>
					<strong>Date on which the cause of delay is over:</strong><br>
					{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->date_on_which_delay_is_over) }}}
				</p>

				<p>
					<strong>Deadline to Submit the final EOT Claim ({{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay):</strong><br>
					{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
				</p>

				<p>
					<strong>Letter to Architect:</strong><br>
					{{{ $eot->eotContractorConfirmDelay->message }}}
				</p>

				@if ( ! $eot->eotContractorConfirmDelay->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $eot->eotContractorConfirmDelay->attachments, 'projectId' => $eot->project_id])
					</p>
				@endif
			</div>
		</div>

		<?php $messageCount = 0; ?>

		@foreach ( $eot->secondLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				@include('extension_of_times.partials.architect_second_level_info_tab', array('message' => $message))
			@else
				@include('extension_of_times.partials.contractor_second_level_info_tab', array('message' => $message))
			@endif

			<?php $messageCount++; ?>
		@endforeach
	</div>
</div>