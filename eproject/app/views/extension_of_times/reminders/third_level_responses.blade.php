@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('extension_of_times.reminders.architect.third_level_response')
@else
	@include('extension_of_times.reminders.contractor.third_level_response')
@endif