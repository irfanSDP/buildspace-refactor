@if ( $aeLastArchitectMessage->decision == PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage::REJECT )
	Application Rejected
@else
	Architect granted ({{{ $ae->project->modified_currency_code }}}) {{{ number_format($aeLastArchitectMessage->grant_different_amount, 2) }}}
@endif