@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('loss_and_or_expenses.reminders.architect.fourth_level_response')
@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
	@include('loss_and_or_expenses.reminders.contractor.fourth_level_response')
@else
	@include('loss_and_or_expenses.reminders.qs.fourth_level_response')
@endif