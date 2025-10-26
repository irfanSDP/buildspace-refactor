@if ( $loeLastArchitectMessage->decision == PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessage::REJECT )
	Application Rejected
@else
	Architect granted ({{{ $loe->project->modified_currency_code }}}) {{{ number_format($loeLastArchitectMessage->grant_different_amount, 2) }}}
@endif