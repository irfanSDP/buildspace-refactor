@if ( $ai->messages->count() > 0 )
	<li>
		The provision of the Conditions that empowers the issuance of the AI is not specified by the Architect. The Contractor may request the Architect to specify in writing the provision of Conditions.
		<ul>
			@foreach($ai->messages as $message)
				<?php $lastMessageType = $message->type; ?>

				@if ($message->type == PCK\ContractGroups\Types\Role::CONTRACTOR)
					@include('architect_instructions.reminders.contractor_response')
				@elseif ($message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER)
					@include('architect_instructions.reminders.architect_response')
				@endif
			@endforeach

			@if ( $lastMessageType != PCK\ContractGroups\Types\Role::CONTRACTOR and $isEditor )
				<li>If the Contractor is not satisfied, he may inform the Architect {{ link_to_route('aiMessage.create', 'here', array($ai->project_id, $ai->id)) }}.</li>
			@endif
		</ul>
	</li>
@else
	<li>
		The provision of the Conditions that empowers the issuance of the AI is not specified by the Architect. The Contractor may request the Architect to specify in writing the provision of Conditions{{ $isEditor ? link_to_route('aiMessage.create', ' here', array($ai->project_id, $ai->id)) : null }}.
	</li>
@endif