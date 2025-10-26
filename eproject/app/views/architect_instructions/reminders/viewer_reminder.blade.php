@if ( ! $ai->haveClause() )
	@include('architect_instructions.reminders.viewer.withoutClause')
@else
	@include('architect_instructions.reminders.viewer.withClause')
@endif

@include('architect_instructions.reminders.step_two_reminder')

@if ( ! $ai->haveDeadline() )
	@include('architect_instructions.reminders.viewer.noDeadline')
@else
	@include('architect_instructions.reminders.viewer.hasDeadline')
@endif

@include('architect_instructions.reminders.step_four_reminder')