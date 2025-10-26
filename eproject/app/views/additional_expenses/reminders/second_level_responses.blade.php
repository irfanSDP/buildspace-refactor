@if ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
	@include('additional_expenses.reminders.contractor.second_level_response')
@else
	@include('additional_expenses.reminders.architect.second_level_response')
@endif