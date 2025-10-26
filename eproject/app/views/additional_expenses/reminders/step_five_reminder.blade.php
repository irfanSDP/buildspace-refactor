@if ( $ae->status == PCK\LossOrAndExpenses\LossOrAndExpense::GRANTED_TEXT )

	<li>
		@if ( $ae->additionalExpenseInterimClaim )
			On {{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseInterimClaim->created_at) }}}, the Architect {{ HTML::link('#', 'mentioned', array('data-toggle' => 'modal', 'data-target' => '#aeInterimClaim-'.$ae->additionalExpenseInterimClaim->id)) }} the Interim Certificate in which the set-off is made.
		@else
			@if ($user->hasCompanyProjectRole($ae->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) and $isEditor)
				The Architect to mention in which {{ link_to_route('aeInterimClaim.create', 'Interim Certificate', array($ae->project_id, $ae->id)) }} the Contractor's claim for Additional Expense is paid. The Contract Sum will be adjusted accordingly.
			@else
				The Architect to mention in which Interim Certificate the Contractor's claim for Additional Expense is paid. The Contract Sum will be adjusted accordingly.
			@endif
		@endif
	</li>

@endif