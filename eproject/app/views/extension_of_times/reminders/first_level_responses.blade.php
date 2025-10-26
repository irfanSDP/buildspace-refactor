@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('extension_of_times.reminders.architect.first_level_response')
@else
	@include('extension_of_times.reminders.contractor.first_level_response')
@endif