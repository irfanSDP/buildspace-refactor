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
		</ul>
	@endif
</li>

@if ( ! $loe->contractorConfirmDelay )
	<li>
		@include('loss_and_or_expenses.reminders.step_two_text_response')
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
			</ul>

			@if ( $loe->lossOrAndExpenseClaim )
				<?php $hashTagClaim = '#' . str_replace('%id%', $loe->lossOrAndExpenseClaim->id, PCK\Forms\LOEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTagClaim, 'submitted') }} the final claim on {{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			The Contractor may request the Architect to Extend the Deadline to submit Loss and/or Expense claim.

			<br>
			<br>

			@if ( $loe->lossOrAndExpenseClaim )
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
		</ul>
	</li>

	<li>
		The Architect to decide to accept/reject the Contractor's application for Loss And/Or Expense.

		@if ( ! $loe->fourthLevelMessages->isEmpty() )
			<ul>
				@foreach ( $loe->fourthLevelMessages as $message )
					<?php $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
						<li>@include('loss_and_or_expenses.reminders.architect.fourth_level_response')</li>
					@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
						<li>@include('loss_and_or_expenses.reminders.contractor.fourth_level_response')</li>
					@endif
				@endforeach
			</ul>
		@endif
	</li>

	@include('loss_and_or_expenses.reminders.step_five_reminder')
@endif