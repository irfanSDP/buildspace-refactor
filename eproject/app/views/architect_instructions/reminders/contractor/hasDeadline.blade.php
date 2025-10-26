<li>
	@if ( $ai->thirdLevelMessages->count() > 0)
		The AI must be complied with by <strong><i>{{{ $ai->project->getProjectTimeZoneTime($ai->deadline_to_comply) }}}</i></strong>. Otherwise, the Employer may employ and pay other Person to execute any work necessary to give effect to the AI. The Contractor to confirm that the AI has been executed and completed.
	@else
		The AI must be complied with by <strong><i>{{{ $ai->project->getProjectTimeZoneTime($ai->deadline_to_comply) }}}</i></strong>. Otherwise, the Employer may employ and pay other Person to execute any work necessary to give effect to the AI. The Contractor to confirm that the AI has been executed and completed{{ $isEditor ? link_to_route('aiThirdLevelMessage.create', ' here', array($ai->project_id, $ai->id)) : null }}.
	@endif

	@include('architect_instructions.reminders.step_three_reminder')
</li>