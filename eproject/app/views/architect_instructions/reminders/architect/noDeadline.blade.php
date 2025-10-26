<li>
	@if ( $ai->thirdLevelMessages->count() > 0)
		The deadline to comply with the AI is not specified by the Architect. However, if the Contractor does not comply with the AI the Employer may employ and pay other Person to execute any work necessary to give effect to the AI. The Architect to verify that the AI had been complied with or otherwise.
	@else
		The deadline to comply with the AI is not specified by the Architect. However, if the Contractor does not comply with the AI the Employer may employ and pay other Person to execute any work necessary to give effect to the AI. The Architect to verify that the AI had been complied with or otherwise{{ $isEditor ? link_to_route('aiThirdLevelMessage.create', ' here', array($ai->project_id, $ai->id)) : null }}.
	@endif

	@include('architect_instructions.reminders.step_three_reminder')
</li>