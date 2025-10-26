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

			@if ( ! $lastFirstMessageStatus and $lastFirstMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
				<li>Architect to reply {{ link_to_route('loeFirstLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
			@endif
		</ul>
	@else
		@if ( $isEditor )
			&nbsp;Architect may {{ link_to_route('loeFirstLevelMessage.create', 'reject/accept', array($loe->project_id, $loe->id)) }} the notice of intention to claim Loss And/Or Expense.
		@endif
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

				@if ( $lastSecondMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
					<li>Architect to reply {{ link_to_route('loeSecondLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
				@endif
			</ul>

			@if ( $loe->lossOrAndExpenseClaim )
				<?php $hashTagClaim = '#' . str_replace('%id%', $loe->lossOrAndExpenseClaim->id, PCK\Forms\LOEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTagClaim, 'submitted') }} the final claim on {{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}.
			@endif
		@else
			<br>
			<br>

			The Contractor may request the Architect to Extend the Deadline to submit Loss And/Or Expense claim.

			<br>
			<br>

			@if ( ! $loe->lossOrAndExpenseClaim )
				Otherwise, the Contractor to submit the Loss And/Or Expense claim.
			@else
				<?php $hashTagClaim = '#' . str_replace('%id%', $loe->lossOrAndExpenseClaim->id, PCK\Forms\LOEClaimForm::accordianId); ?>

				The Contractor {{ HTML::link($hashTagClaim, 'submitted') }} the final claim on {{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}.
			@endif
		@endif
	</li>
@endif

@if ( $loe->lossOrAndExpenseClaim )
	<li>
		The Architect and QS may request Contractor to provide further particulars for the Loss And/Or Expense Claim.

		<ul>
			<?php $lastThirdMessageType = null; ?>

			@foreach ( $loe->thirdLevelMessages as $message )
				<li>
					<?php $lastThirdMessageType = $message->type; ?>

					@include('loss_and_or_expenses.reminders.third_level_responses')
				</li>
			@endforeach

			@if ( $lastThirdMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
				<li>Architect to request further particulars {{ link_to_route('loeThirdLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
			@endif
		</ul>
	</li>

	@if ( $loe->fourthLevelMessages->isEmpty() )
		<li>
			@if ( $isEditor )
				The Architect to decide to {{ link_to_route('loeFourthLevelMessage.create', 'accept/reject', array($loe->project_id, $loe->id)) }} the Contractor's application for Loss And/Or Expense.
			@else
				The Architect to decide to accept/reject the Contractor's application for Loss And/Or Expense.
			@endif
		</li>
	@else
		<li>
			The Architect to decide to accept/reject the Contractor's application for Loss And/Or Expense.

			<ul>
				@foreach ( $loe->fourthLevelMessages as $message )
					<?php $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					<li>
						@include('loss_and_or_expenses.reminders.fourth_level_responses')
					</li>
				@endforeach

				@if ( ! $loe->lossOrAndExpenseInterimClaim and ! $lastFourthMessageLockedStatus and $lastFourthMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
					<li>Architect to respond {{ link_to_route('loeFourthLevelMessage.create', 'here', array($loe->project_id, $loe->id)) }}.</li>
				@endif
			</ul>
		</li>
	@endif

	@include('loss_and_or_expenses.reminders.step_five_reminder')
@endif