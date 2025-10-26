<ol class="reminderContainer">
	@if ($user->hasCompanyProjectRole($eot->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER))
		@include('extension_of_times.reminders.architect_reminder')
	@elseif ($user->hasCompanyProjectRole($eot->project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
		@include('extension_of_times.reminders.contractor_reminder')
	@else
		@include('extension_of_times.reminders.viewer_reminder')
	@endif
</ol>