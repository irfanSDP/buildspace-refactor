@if ( $loe->status == PCK\LossOrAndExpenses\LossOrAndExpense::GRANTED_TEXT )

	<li>
		@if ( $loe->lossOrAndExpenseInterimClaim )
			The Architect mentioned on {{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseInterimClaim->created_at) }}} that the Contractor's claim for Loss And/Or Expense is paid in this {{ HTML::link('#', 'Interim Certificate', array('data-toggle' => 'modal', 'data-target' => '#loeInterimClaim-'.$loe->lossOrAndExpenseInterimClaim->id)) }}. The Contract Sum has been adjusted accordingly.
		@else
			@if ($user->hasCompanyProjectRole($loe->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) and $isEditor)
				The Architect to mention in which {{ link_to_route('loeInterimClaim.create', 'Interim Certificate', array($loe->project_id, $loe->id)) }} the Contractor's claim for Loss And/Or Expense is paid. The Contract Sum will be adjusted accordingly.
			@else
				The Architect to mention in which Interim Certificate the Contractor's claim for Loss And/Or Expense is paid. The Contract Sum will be adjusted accordingly.
			@endif
		@endif
	</li>

@endif