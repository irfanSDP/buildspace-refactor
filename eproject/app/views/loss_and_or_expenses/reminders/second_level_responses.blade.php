@if ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
	@include('loss_and_or_expenses.reminders.contractor.second_level_response')
@else
	@include('loss_and_or_expenses.reminders.architect.second_level_response')
@endif