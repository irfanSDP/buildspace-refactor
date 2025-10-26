<li>
	The Contractor to give written notice to the Architect his intention to claim for Additional Expense by <strong>{{{ $ae->deadline_to_submit_notice_to_claim }}}</strong>. Written notice was given by Contractor to Architect on {{{ $ae->project->getProjectTimeZoneTime($ae->created_at) }}}.

	@if ( ! $ae->firstLevelMessages->isEmpty() )
		<ul>
			@foreach ( $ae->firstLevelMessages as $message )
				<li>
					<?php $lastFirstMessageType = $message->type; $lastFirstMessageStatus = $message->decision; ?>

					@include('additional_expenses.reminders.first_level_responses')
				</li>
			@endforeach

			@if ( ! $lastFirstMessageStatus and $lastFirstMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>Contractor to appeal {{ link_to_route('aeFirstLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
			@endif
		</ul>
	@endif
</li>

@if ( ! $ae->contractorConfirmDelay )
	<li>
		@if ( $isEditor )
			The Contractor to {{ link_to_route('aeContractorConfirmDelay.create', 'confirm', array($ae->project_id, $ae->id)) }} that the Variation that leads to the Additional Expense has been completed. The deadline for the Contractor to submit the claim for Additional Expense is {{{ $ae->project->pam2006Detail->deadline_submitting_final_claim_ae }}} days from the date Variation is completed. The Contractor must provide all necessary calculations to substantiate his claims.
		@else
			@include('additional_expenses.reminders.step_two_text_response')
		@endif
	</li>
@else
	<li>
		@include('additional_expenses.reminders.step_two_text_response_confirmed')

		@if ( ! $ae->secondLevelMessages->isEmpty() )
			<?php $messageCount = 0; ?>

			<ul>
				@foreach($ae->secondLevelMessages as $message)
				<?php $lastSecondMessageType = $message->type; ?>
				<li>
					@include('additional_expenses.reminders.second_level_responses')
				</li>
				<?php $messageCount++; ?>
				@endforeach

				@if ( $lastSecondMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
					<li>Contractor to appeal {{ link_to_route('aeSecondLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
				@endif
			</ul>

			@if ( ! $ae->additionalExpenseClaim )
				Anyway, the Contractor may still submit the Additional Expense claim{{ $isEditor ? link_to_route('aeClaim.create', ' here', array($ae->project_id, $ae->id)) : null }}.
			@else
				<?php $hashTag = '#' . str_replace('%id%', $ae->additionalExpenseClaim->id, PCK\Forms\AEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTag, 'submitted') }} the final claim on {{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			@if ( $isEditor )
				The Contractor may request the Architect to {{ link_to_route('aeSecondLevelMessage.create', 'Extend the Deadline', array($ae->project_id, $ae->id)) }} to submit Additional Expense claim.
			@else
				The Contractor may request the Architect to Extend the Deadline to submit Additional Expense claim.
			@endif

			<br>
			<br>

			@if ( ! $ae->additionalExpenseClaim )
				Otherwise, the Contractor to submit the Additional Expense claim{{ $isEditor ? link_to_route('aeClaim.create', ' here', array($ae->project_id, $ae->id)) : null }}.
			@else
				<?php $hashTag = '#' . str_replace('%id%', $ae->additionalExpenseClaim->id, PCK\Forms\AEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTag, 'submitted') }} the final claim on {{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseClaim->created_at) }}}.
			@endif
		@endif
	</li>
@endif

@if ( $ae->additionalExpenseClaim )
	<li>
		The Architect and QS may request Contractor to provide further particulars for the Additional Expense Claim.

		<ul>
			@foreach ( $ae->thirdLevelMessages as $message )
				<li>
					<?php $lastThirdMessageType = $message->type; $lastMessageThirdComplyDate = $ae->project->getProjectTimeZoneTime($message->deadline_to_comply_with); ?>

					@include('additional_expenses.reminders.third_level_responses')
				</li>
			@endforeach

			@if ( ! $ae->thirdLevelMessages->isEmpty() and $lastThirdMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>Contractor to comply with the Architect or QS's request {{ link_to_route('aeThirdLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }} by {{{ $lastMessageThirdComplyDate }}}.</li>
			@endif
		</ul>
	</li>

	<li>
		The Architect to decide to accept/reject the Contractor's application for Additional Expense.

		@if ( ! $ae->fourthLevelMessages->isEmpty() )
			<ul>
				@foreach ( $ae->fourthLevelMessages as $message )
					<?php $isArchitectLastMessage = $message->type; $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
						<li>@include('additional_expenses.reminders.architect.fourth_level_response')</li>
					@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
						<li>@include('additional_expenses.reminders.contractor.fourth_level_response')</li>
					@endif
				@endforeach

				@if ( ! $ae->additionalExpenseInterimClaim and $lastArchitectFourthMessage and ! $lastFourthMessageLockedStatus and $lastFourthMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor and $isArchitectLastMessage == \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
					<li>Contractor to may appeal {{ link_to_route('aeFourthLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
				@endif
			</ul>
		@endif
	</li>

	@include('additional_expenses.reminders.step_five_reminder')
@endif