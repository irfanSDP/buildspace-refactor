<li>
	The Contractor to give written notice to the Architect his intention to claim for Loss And/Or Expense by <strong>{{{ $loe->deadline_to_submit_notice_to_claim }}}</strong>. Written notice was given by Contractor to Architect on {{{ $loe->project->getProjectTimeZoneTime($loe->created_at) }}}.

	@if ( ! $loe->firstLevelMessages->isEmpty() )
		<ul>
			@foreach ( $loe->firstLevelMessages as $message )
				<li>
					<?php $lastFirstMessageType = $message->type; $lastFirstMessageStatus = $message->decision; ?>

					@include('loss_and_or_expenses.reminders.first_level_responses')
				</li>
			@endforeach

			@if ( ! $lastFirstMessageStatus and $lastFirstMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>Contractor to appeal {{ link_to_route('loeFirstLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
			@endif
		</ul>
	@endif
</li>

@if ( ! $loe->contractorConfirmDelay )
	<li>
		@if ( $isEditor )
			The Contractor to confirm that {{ link_to_route('loeContractorConfirmDelay.create', '"The matters referred to in the claim [that have led to the Loss And/Or Expense]" have ended', array($loe->project_id, $loe->id)) }}. The deadline for the Contractor to submit the claim for the Loss And/Or Expense is {{{ $loe->project->pam2006Detail->deadline_submitting_final_claim_l_and_e }}} days from date the matters referred have ended.
		@else
			@include('loss_and_or_expenses.reminders.step_two_text_response')
		@endif
	</li>
@else
	<li>
		@include('loss_and_or_expenses.reminders.step_two_text_response_confirmed')

		@if ( ! $loe->secondLevelMessages->isEmpty() )
			<?php $messageCount = 0; ?>

			<ul>
				@foreach($loe->secondLevelMessages as $message)
				<?php $lastSecondMessageType = $message->type; ?>
				<li>
					@include('loss_and_or_expenses.reminders.second_level_responses')
				</li>
				<?php $messageCount++; ?>
				@endforeach

				@if ( $lastSecondMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
					<li>Contractor to appeal {{ link_to_route('loeSecondLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
				@endif
			</ul>

			@if ( ! $loe->lossOrAndExpenseClaim )
				Anyway, the Contractor may still submit the Loss and/or Expense claim{{ $isEditor ? link_to_route('loeClaim.create', ' here', array($loe->project_id, $loe->id)) : null }}.
			@else
				<?php $hashTagClaim = '#' . str_replace('%id%', $loe->lossOrAndExpenseClaim->id, PCK\Forms\LOEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTagClaim, 'submitted') }} the final claim on {{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			@if ( $isEditor )
				The Contractor may request the Architect to {{ link_to_route('loeSecondLevelMessage.create', 'Extend the Deadline', array($loe->project_id, $loe->id)) }} to submit Loss and/or Expense claim.
			@else
				The Contractor may request the Architect to Extend the Deadline to submit Loss and/or Expense claim.
			@endif

			<br>
			<br>

			@if ( ! $loe->lossOrAndExpenseClaim )
				Otherwise, the Contractor to submit the Loss and/or Expense claim{{ $isEditor ? link_to_route('loeClaim.create', ' here', array($loe->project_id, $loe->id)) : null }}.
			@else
				<?php $hashTagClaim = '#' . str_replace('%id%', $loe->lossOrAndExpenseClaim->id, PCK\Forms\LOEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTagClaim, 'submitted') }} the final claim on {{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}.
			@endif
		@endif
	</li>
@endif

@if ( $loe->lossOrAndExpenseClaim )
	<li>
		The Architect may request Contractor to provide further particulars for the Loss And/Or Expense Claim.

		<ul>
			@foreach ( $loe->thirdLevelMessages as $message )
				<li>
					<?php $lastThirdMessageType = $message->type; $lastMessageThirdComplyDate = $loe->project->getProjectTimeZoneTime($message->deadline_to_comply_with); ?>

					@include('loss_and_or_expenses.reminders.third_level_responses')
				</li>
			@endforeach

			@if ( ! $loe->thirdLevelMessages->isEmpty() and $lastThirdMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>Contractor to comply with the Architect or QS's request {{ link_to_route('loeThirdLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }} by {{{ $lastMessageThirdComplyDate }}}.</li>
			@endif
		</ul>
	</li>

	<li>
		The Architect to decide to accept/reject the Contractor's application for Loss And/Or Expense.

		@if ( ! $loe->fourthLevelMessages->isEmpty() )
			<ul>
				@foreach ( $loe->fourthLevelMessages as $message )
					<?php $isArchitectLastMessage = $message->type; $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
						<li>@include('loss_and_or_expenses.reminders.architect.fourth_level_response')</li>
					@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
						<li>@include('loss_and_or_expenses.reminders.contractor.fourth_level_response')</li>
					@endif
				@endforeach

				@if ( ! $loe->lossOrAndExpenseInterimClaim and $lastArchitectFourthMessage and ! $lastFourthMessageLockedStatus and $lastFourthMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor and $isArchitectLastMessage == \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
					<li>Contractor to may appeal {{ link_to_route('loeFourthLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
				@endif
			</ul>
		@endif
	</li>

	@include('loss_and_or_expenses.reminders.step_five_reminder')
@endif