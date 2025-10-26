<li>
	The Contractor to give written notice to the Architect his intention to claim for EOT by <strong>{{{ $eot->deadline_to_submit_notice_to_claim }}}</strong>. Written notice was given by Contractor to Architect on {{{ $eot->project->getProjectTimeZoneTime($eot->created_at) }}}.

	@if ( ! $eot->firstLevelMessages->isEmpty() )
		<ul>
			@foreach ( $eot->firstLevelMessages as $message )
				<li>
					@include('extension_of_times.reminders.first_level_responses')
				</li>
			@endforeach
		</ul>
	@endif
</li>

@if ( ! $eot->eotContractorConfirmDelay )
	<li>The Contractor to confirm that "The Cause of the Delay is Over". The deadline for the Contractor to submit the final claim for EOT is {{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay.</li>
@else
	<?php $hashTagDelayIsOver = '#' . str_replace('%id%', $eot->eotContractorConfirmDelay->id, PCK\Forms\EOTContractorConfirmDelayForm::accordianId); ?>

	<li>
		The Contractor {{ HTML::link($hashTagDelayIsOver, 'confirmed') }} that the date of the end of the cause of delay is {{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->date_on_which_delay_is_over) }}}. The deadline for the Contractor to submit the final claim for EOT is <strong>{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}</strong> ({{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay).

		@if ( ! $eot->secondLevelMessages->isEmpty() )
			<?php $messageCount = 0; ?>

			<ul>
				@foreach($eot->secondLevelMessages as $message)
				<li>
					@include('extension_of_times.reminders.second_level_responses')
				</li>
				<?php $messageCount++; ?>
				@endforeach
			</ul>

			@if ( ! $eot->extensionOfTimeClaim )
				Anyway, the Contractor may still submit the EOT claim.
			@else
				<?php $hashTag = '#' . str_replace('%id%', $eot->extensionOfTimeClaim->id, PCK\Forms\EOTClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTag, 'submitted') }} the final claim on {{{ $eot->project->getProjectTimeZoneTime($eot->extensionOfTimeClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			The Contractor may request the Architect to Extend the Deadline to submit EOT claim.

			<br>
			<br>

			@if ( ! $eot->extensionOfTimeClaim )
				Otherwise, the Contractor to submit the EOT claim.
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
					@include('extension_of_times.reminders.third_level_responses')
				</li>
			@endforeach
		</ul>
	</li>

	<li>
		@include('extension_of_times.reminders.step_four_responses_first_text')

		@if ( ! $eot->fourthLevelMessages->isEmpty() )
			<ul>
				@foreach ( $eot->fourthLevelMessages as $message )
					<li>
						@include('extension_of_times.reminders.fourth_level_responses')
					</li>
				@endforeach
			</ul>
		@endif
	</li>
@endif