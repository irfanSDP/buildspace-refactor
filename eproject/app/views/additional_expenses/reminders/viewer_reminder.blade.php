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
		</ul>
	@endif
</li>

@if ( ! $ae->contractorConfirmDelay )
	<li>
		@include('additional_expenses.reminders.step_two_text_response')
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
			</ul>

			@if ( $ae->additionalExpenseClaim )
				<?php $hashTag = '#' . str_replace('%id%', $ae->additionalExpenseClaim->id, PCK\Forms\AEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTag, 'submitted') }} the final claim on {{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			The Contractor may request the Architect to Extend the Deadline to submit Additional Expense claim.

			<br>
			<br>

			@if ( $ae->additionalExpenseClaim )
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
		</ul>
	</li>

	<li>
		The Architect to decide to accept/reject the Contractor's application for Additional Expense.

		@if ( ! $ae->fourthLevelMessages->isEmpty() )
			<ul>
				@foreach ( $ae->fourthLevelMessages as $message )
					<?php $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
						<li>@include('additional_expenses.reminders.architect.fourth_level_response')</li>
					@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
						<li>@include('additional_expenses.reminders.contractor.fourth_level_response')</li>
					@endif
				@endforeach
			</ul>
		@endif
	</li>

	@include('additional_expenses.reminders.step_five_reminder')
@endif