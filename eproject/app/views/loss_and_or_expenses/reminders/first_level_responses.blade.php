@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('loss_and_or_expenses.reminders.architect.first_level_response')
@else
	@include('loss_and_or_expenses.reminders.contractor.first_level_response')
@endif