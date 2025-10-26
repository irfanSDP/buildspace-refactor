@if ( $ai->status == PCK\ArchitectInstructions\ArchitectInstruction::NOT_COMPLIED_TEXT or $ai->status == PCK\ArchitectInstructions\ArchitectInstruction::WITH_OUTSTANDING_WORKS_TEXT )

	<li>
		@if ( $ai->architectInstructionInterimClaim )
			On {{{ $ai->project->getProjectTimeZoneTime($ai->architectInstructionInterimClaim->created_at) }}}, the Architect {{ HTML::link('#', 'mentioned', array('data-toggle' => 'modal', 'data-target' => '#aiInterimClaim-'.$ai->architectInstructionInterimClaim->id)) }} the Interim Certificate in which the set-off is made.
		@else
			@if ($user->hasCompanyProjectRole($ai->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) and $isEditor)
				The Architect {{ link_to_route('aiInterimClaim.create', 'to mention', array($ai->project_id, $ai->id)) }} in which Interim Certificate the set-off is made.
			@else
				The Architect to mention in which Interim Certificate the set-off is made.
			@endif
		@endif
	</li>

@endif