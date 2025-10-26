<ol class="reminderContainer">
	@if ($user->hasCompanyProjectRole($ae->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER))
		@include('additional_expenses.reminders.architect_reminder')
    @elseif ($user->hasCompanyProjectRole($ae->project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
		@include('additional_expenses.reminders.contractor_reminder')
	@elseif ($user->hasCompanyProjectRole($ae->project, \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER))
		@include('additional_expenses.reminders.qs_reminder')
	@else
		@include('additional_expenses.reminders.viewer_reminder')
	@endif
</ol>