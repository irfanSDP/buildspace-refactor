@if ( ! $ai->haveClause() )
	@include('architect_instructions.reminders.contractor.withoutClause')
@else
	@include('architect_instructions.reminders.contractor.withClause')
@endif

@include('architect_instructions.reminders.step_two_reminder')

@if ( ! $ai->haveDeadline() )
	@include('architect_instructions.reminders.contractor.noDeadline')
@else
	@include('architect_instructions.reminders.contractor.hasDeadline')
@endif

@include('architect_instructions.reminders.step_four_reminder')