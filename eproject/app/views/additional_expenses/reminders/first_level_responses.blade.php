@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	@include('additional_expenses.reminders.architect.first_level_response')
@else
	@include('additional_expenses.reminders.contractor.first_level_response')
@endif