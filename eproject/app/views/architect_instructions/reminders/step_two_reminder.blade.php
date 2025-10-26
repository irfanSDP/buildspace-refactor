<li>
	The Contractor to check whether the AI will lead to EOT, Loss and/or Expense, or Variation / Additional Expense claim.

	@if ( $user->hasCompanyProjectRole($ai->project, PCK\ContractGroups\Types\Role::CONTRACTOR) )
		@include('architect_instructions.reminders.contractor.step_two_indepth_information')
	@elseif( $ai->latestExtensionOfTime or $ai->latestLossOrAndExpense or $ai->latestAdditionalExpense )
		@include('architect_instructions.reminders.viewer.step_two_indepth_information')
	@endif
</li>