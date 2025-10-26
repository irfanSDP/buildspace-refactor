<li>
	The Contractor to give written notice to the Architect his intention to claim for EOT by <strong>{{{ $eot->deadline_to_submit_notice_to_claim }}}</strong>. Written notice was given by Contractor to Architect on {{{ $eot->project->getProjectTimeZoneTime($eot->created_at) }}}.

	@if ( ! $eot->firstLevelMessages->isEmpty() )
		<ul>
			@foreach ( $eot->firstLevelMessages as $message )
				<li>
					<?php $lastFirstMessageType = $message->type; $lastFirstMessageStatus = $message->decision; ?>

					@include('extension_of_times.reminders.first_level_responses')
				</li>
			@endforeach

			@if ( ! $lastFirstMessageStatus and $lastFirstMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>Contractor to appeal {{ link_to_route('eotFirstLevelMessage.create', 'here', array($eot->project_id, $eot->id)) }}.</li>
			@endif
		</ul>
	@endif
</li>

@if ( ! $eot->eotContractorConfirmDelay )
	<li>
		@if ( $isEditor )
			The Contractor to confirm that {{ link_to_route('eotContractorConfirmDelay.create', '"The Cause of the Delay is Over"', array($eot->project_id, $eot->id)) }}. The deadline for the Contractor to submit the final claim for EOT is {{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay.
		@else
			The Contractor to confirm that "The Cause of the Delay is Over". The deadline for the Contractor to submit the final claim for EOT is {{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay.
		@endif
	</li>
@else
	<?php $hashTagDelayIsOver = '#' . str_replace('%id%', $eot->eotContractorConfirmDelay->id, PCK\Forms\EOTContractorConfirmDelayForm::accordianId); ?>

	<li>
		The Contractor {{ HTML::link($hashTagDelayIsOver, 'confirmed') }} that the date of the end of the cause of delay is {{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->date_on_which_delay_is_over) }}}. The deadline for the Contractor to submit the final claim for EOT is <strong>{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}</strong> ({{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay).

		@if ( ! $eot->secondLevelMessages->isEmpty() )
			<?php $messageCount = 0; ?>

			<ul>
				@foreach($eot->secondLevelMessages as $message)
				<?php $lastSecondMessageType = $message->type; ?>
				<li>
					@include('extension_of_times.reminders.second_level_responses')
				</li>
				<?php $messageCount++; ?>
				@endforeach

				@if ( $lastSecondMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
					<li>Contractor to appeal {{ link_to_route('eotSecondLevelMessage.create', 'here', array($eot->project_id, $eot->id)) }}.</li>
				@endif
			</ul>

			@if ( ! $eot->extensionOfTimeClaim )
				Anyway, the Contractor may still submit the EOT claim{{ $isEditor ? link_to_route('eotClaim.create', ' here', array($eot->project_id, $eot->id)) : null }}.
			@else
				<?php $hashTag = '#' . str_replace('%id%', $eot->extensionOfTimeClaim->id, PCK\Forms\EOTClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTag, 'submitted') }} the final claim on {{{ $eot->project->getProjectTimeZoneTime($eot->extensionOfTimeClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			@if ( $isEditor )
				The Contractor may request the Architect to {{ link_to_route('eotSecondLevelMessage.create', 'Extend the Deadline', array($eot->project_id, $eot->id)) }} to submit EOT claim.
			@else
				The Contractor may request the Architect to Extend the Deadline to submit EOT claim.
			@endif

			<br>
			<br>

			@if ( ! $eot->extensionOfTimeClaim )
				Otherwise, the Contractor to submit the EOT claim{{ $isEditor ? link_to_route('eotClaim.create', ' here', array($eot->project_id, $eot->id)) : null }}.
			@else
				<?php $hashTag = '#' . str_replace('%id%', $eot->extensionOfTimeClaim->id, PCK\Forms\EOTClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTag, 'submitted') }} the final claim on {{{ $eot->project->getProjectTimeZoneTime($eot->extensionOfTimeClaim->created_at) }}}.
			@endif
		@endif
	</li>
@endif

@if ( $eot->extensionOfTimeClaim )
	<li>
		The Architect may, before <strong>{{{ $eot->extensionOfTimeClaim->deadline_to_request_further_particulars }}}</strong> (i.e. within {{{ $eot->project->pam2006Detail->deadline_architect_request_info_from_contractor_eot_claim }}} days from the date of submission by the contractor), request Contractor to provide further particulars for the EOT Claim.

		<ul>
			@foreach ( $eot->thirdLevelMessages as $message )
				<li>
					<?php $lastThirdMessageType = $message->type; $lastMessageThirdComplyDate = $eot->project->getProjectTimeZoneTime($message->deadline_to_comply_with); ?>

					@include('extension_of_times.reminders.third_level_responses')
				</li>
			@endforeach

			@if ( ! $eot->thirdLevelMessages->isEmpty() and $lastThirdMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>Contractor to comply with the Architect's request {{ link_to_route('eotThirdLevelMessage.create', 'here', array($eot->project_id, $eot->id)) }} by {{{ $lastMessageThirdComplyDate }}}.</li>
			@endif
		</ul>
	</li>

	<li>
		@include('extension_of_times.reminders.step_four_responses_first_text')

		@if ( ! $eot->fourthLevelMessages->isEmpty() )
			<ul>
				@foreach ( $eot->fourthLevelMessages as $message )
					<?php $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					<li>
						@include('extension_of_times.reminders.fourth_level_responses')
					</li>
				@endforeach

				@if ( ! $lastFourthMessageLockedStatus and $lastFourthMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
					<li>Contractor to may appeal {{ link_to_route('eotFourthLevelMessage.create', 'here', array($eot->project_id, $eot->id)) }}.</li>
				@endif
			</ul>
		@endif
	</li>
@endif