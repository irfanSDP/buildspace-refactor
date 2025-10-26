@if ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
	@include('extension_of_times.reminders.contractor.second_level_response')
@else
	@include('extension_of_times.reminders.architect.second_level_response')
@endif