<ol class="reminderContainer">
	@if ($user->hasCompanyProjectRole($loe->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER))
		@include('loss_and_or_expenses.reminders.architect_reminder')
	@elseif ($user->hasCompanyProjectRole($loe->project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
		@include('loss_and_or_expenses.reminders.contractor_reminder')
	@elseif ($user->hasCompanyProjectRole($loe->project, \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER))
		@include('loss_and_or_expenses.reminders.qs_reminder')
	@else
		@include('loss_and_or_expenses.reminders.viewer_reminder')
	@endif
</ol>