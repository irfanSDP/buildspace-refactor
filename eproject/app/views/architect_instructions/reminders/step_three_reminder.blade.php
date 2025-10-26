@if ( $ai->thirdLevelMessages->count() > 0)

	<ul>
		@foreach ( $ai->thirdLevelMessages as $message )
			<?php $lastMessageType = $message->type; $lastMessageComplianceStatus = $message->compliance_status; ?>

			<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\AIMessageThirdLevelArchitectForm::accordianId); ?>

			<li>
				@if ($message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER)
					@if ($message->compliance_status == PCK\ArchitectInstructions\StatusType::COMPLIED)
						On {{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}, the Architect {{ HTML::link($hashTag, 'confirmed') }} that the Contractor has complied with the AI.
					@elseif ($message->compliance_status == PCK\ArchitectInstructions\StatusType::NOT_COMPLIED)
						On {{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}, the Architect {{ HTML::link($hashTag, 'confirmed') }} that the AI had not been complied with. The Employer may employ and pay other Person to execute any work necessary to give effect to the AI. The Cost in connection shall be set-off by the Employer.
					@else
						On {{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}, the Architect {{ HTML::link($hashTag, 'confirmed') }} that there are still outstanding works to be carried out.
					@endif
				@elseif ($message->type == PCK\ContractGroups\Types\Role::CONTRACTOR)
					On {{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}, the Contractor {{ HTML::link($hashTag, 'responded') }}.
				@endif
			</li>
		@endforeach

		@if ( ! $ai->architectInstructionInterimClaim )
			@if ( $lastMessageComplianceStatus != PCK\ArchitectInstructions\StatusType::COMPLIED and $isEditor )
				@if ($user->hasCompanyProjectRole($ai->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) and $lastMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER)
					<li>Architect to {{ link_to_route('aiThirdLevelMessage.create', 'verify', array($ai->project_id, $ai->id)) }}.</li>
				@elseif ($user->hasCompanyProjectRole($ai->project, \PCK\ContractGroups\Types\Role::CONTRACTOR) and $lastMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR)
					<li>Contractor to {{ link_to_route('aiThirdLevelMessage.create', 'respond', array($ai->project_id, $ai->id)) }}.</li>
				@endif
			@endif
		@endif
	</ul>

@endif