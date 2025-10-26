@if ( $ai->messages->count() > 0 )
	<li>
		@include('architect_instructions.reminders.base.withClause_first_message')

		<ul>
			@foreach($ai->messages as $message)
				<?php $lastMessageType = $message->type; ?>

				@if ($message->type == PCK\ContractGroups\Types\Role::CONTRACTOR)
					@include('architect_instructions.reminders.contractor_response')
				@elseif ($message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER)
					@include('architect_instructions.reminders.architect_response')
				@endif
			@endforeach

			@if ( $lastMessageType != PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER and $isEditor )
				<li>Architect to respond {{ link_to_route('aiMessage.create', 'here', array($ai->project_id, $ai->id)) }}.</li>
			@endif
		</ul>
	</li>
@else
	<li>
		The Architect specified the provision of the Conditions that empowers him to issue the AI. If the Contractor is not satisfied that the provision of the Conditions specified empowers the issuance of the AI, he may inform the Architect.
	</li>
@endif