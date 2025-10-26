<li>
	The provision of the Conditions that empowers the issuance of the AI is not specified by the Architect. The Contractor may request the Architect to specify in writing the provision of Conditions.

	@if ( $ai->messages->count() > 0 )
	<ul>
		@foreach($ai->messages as $message)
			@if ($message->type == PCK\ContractGroups\Types\Role::CONTRACTOR)
				@include('architect_instructions.reminders.contractor_response')
			@elseif ($message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER)
				@include('architect_instructions.reminders.architect_response')
			@endif
		@endforeach
	</ul>
	@endif
</li>