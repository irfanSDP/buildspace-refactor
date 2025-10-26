@if ( $loeLastQSMessage->decision == PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessage::REJECT )
	Application Rejected
@else
	QS granted ({{{ $loe->project->modified_currency_code }}}) {{{ number_format($loeLastQSMessage->grant_different_amount, 2) }}}
@endif