@if ( ! $ai->haveClause() )
	@include('architect_instructions.reminders.architect.withoutClause')
@else
	@include('architect_instructions.reminders.architect.withClause')
@endif

@include('architect_instructions.reminders.step_two_reminder')

@if ( ! $ai->haveDeadline() )
	@include('architect_instructions.reminders.architect.noDeadline')
@else
	@include('architect_instructions.reminders.architect.hasDeadline')
@endif

@include('architect_instructions.reminders.step_four_reminder')