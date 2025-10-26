<div id="s3" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		<div>
			<h4 role="tab" aria-selected="false" tabindex="0" id="{{{ str_replace('%id%', $eot->extensionOfTimeClaim->id, PCK\Forms\EOTClaimForm::accordianId) }}}">
				{{{ PCK\Forms\EOTClaimForm::formTitle }}}
			</h4>

			<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
				<p>
					<strong>Subject/Reference:</strong><br>
					{{{ $eot->extensionOfTimeClaim->subject }}}
				</p>

				<p>
					<strong>Date Submitted:</strong><br>
					<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($eot->extensionOfTimeClaim->created_at) }}}</span> by {{{ $eot->extensionOfTimeClaim->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
				</p>

				<p>
					<strong>Deadline to Submit the final EOT Claim:</strong><br>
					{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
				</p>

				<p>
					<strong>Detailed Elaborations to Substantiate Claim/Cover Letter:</strong><br>
					{{{ $eot->extensionOfTimeClaim->message }}}
				</p>

				<p>
					<strong>EOT Claimed (Days):</strong><br>
					{{{ $eot->extensionOfTimeClaim->days_claimed }}}
				</p>

				@if ( ! $eot->extensionOfTimeClaim->attachments->isEmpty() )
					<p>
						<strong>Attachment(s):</strong><br>

						@include('file_uploads.partials.uploaded_file_show_only', ['files' => $eot->extensionOfTimeClaim->attachments, 'projectId' => $eot->project_id])
					</p>
				@endif
			</div>
		</div>

		@foreach ( $eot->thirdLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				@include('extension_of_times.partials.architect_third_level_info_tab', array('message' => $message))
			@else
				@include('extension_of_times.partials.contractor_third_level_info_tab', array('message' => $message))
			@endif
		@endforeach
	</div>
</div>