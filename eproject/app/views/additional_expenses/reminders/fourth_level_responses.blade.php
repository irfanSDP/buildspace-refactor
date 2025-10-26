@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('additional_expenses.reminders.architect.fourth_level_response')
@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
	@include('additional_expenses.reminders.contractor.fourth_level_response')
@else
	@include('additional_expenses.reminders.qs.fourth_level_response')
@endif