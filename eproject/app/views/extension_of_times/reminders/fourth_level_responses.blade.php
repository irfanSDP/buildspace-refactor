@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('extension_of_times.reminders.architect.fourth_level_response')
@else
	@include('extension_of_times.reminders.contractor.fourth_level_response')
@endif