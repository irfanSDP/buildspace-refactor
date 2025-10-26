<ol class="reminderContainer">
	@if ($user->hasCompanyProjectRole($ai->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER))
		@include('architect_instructions.reminders.architect_reminder')
	@elseif ($user->hasCompanyProjectRole($ai->project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
		@include('architect_instructions.reminders.contractor_reminder')
	@else
		@include('architect_instructions.reminders.viewer_reminder')
	@endif
</ol>