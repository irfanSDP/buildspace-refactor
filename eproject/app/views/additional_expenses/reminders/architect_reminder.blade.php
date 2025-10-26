<li>
	The Contractor to give written notice to the Architect his intention to claim for Additional Expense by <strong>{{{ $ae->project->getProjectTimeZoneTime($ae->deadline_to_submit_notice_to_claim) }}}</strong>. Written notice was given by Contractor to Architect on {{{ $ae->created_at }}}.

	@if ( ! $ae->firstLevelMessages->isEmpty() )
		<ul>
			@foreach ( $ae->firstLevelMessages as $message )
				<li>
					<?php $lastFirstMessageType = $message->type; $lastFirstMessageStatus = $message->decision; ?>

					@include('additional_expenses.reminders.first_level_responses')
				</li>
			@endforeach

			@if ( ! $lastFirstMessageStatus and $lastFirstMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
				<li>Architect to reply {{ link_to_route('aeFirstLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
			@endif
		</ul>
	@else
		@if ( $isEditor )
			&nbsp;Architect may {{ link_to_route('aeFirstLevelMessage.create', 'reject/accept', array($ae->project_id, $ae->id)) }} the notice of intention to claim Additional Expense.
		@endif
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

				@if ( $lastSecondMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
					<li>Architect to reply {{ link_to_route('aeSecondLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
				@endif
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

			@if ( ! $ae->additionalExpenseClaim )
				Otherwise, the Contractor to submit the Additional Expense claim.
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
			<?php $lastThirdMessageType = null; ?>

			@foreach ( $ae->thirdLevelMessages as $message )
				<li>
					<?php $lastThirdMessageType = $message->type; ?>

					@include('additional_expenses.reminders.third_level_responses')
				</li>
			@endforeach

			@if ( $lastThirdMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
				<li>Architect to request further particulars {{ link_to_route('aeThirdLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
			@endif
		</ul>
	</li>

	@if ( $ae->fourthLevelMessages->isEmpty() )
		<li>
			@if ( $isEditor )
				The Architect to decide to {{ link_to_route('aeFourthLevelMessage.create', 'accept/reject', array($ae->project_id, $ae->id)) }} the Contractor's application for Additional Expense.
			@else
				The Architect to decide to accept/reject the Contractor's application for Additional Expense.
			@endif
		</li>
	@else
		<li>
			The Architect to decide to accept/reject the Contractor's application for Additional Expense.

			<ul>
				@foreach ( $ae->fourthLevelMessages as $message )
					<?php $lastFourthMessageType = $message->type; $lastFourthMessageLockedStatus = $message->locked; ?>
					<li>
						@include('additional_expenses.reminders.fourth_level_responses')
					</li>
				@endforeach

				@if ( ! $ae->additionalExpenseInterimClaim and ! $lastFourthMessageLockedStatus and $lastFourthMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
					<li>Architect to respond {{ link_to_route('aeFourthLevelMessage.create', 'here', array($ae->project_id, $ae->id)) }}.</li>
				@endif
			</ul>
		</li>
	@endif

	@include('additional_expenses.reminders.step_five_reminder')
@endif