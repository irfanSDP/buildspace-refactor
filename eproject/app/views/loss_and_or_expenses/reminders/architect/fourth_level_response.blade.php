<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageFourthLevelArchitectQsForm::accordianId); ?>

@if ( $message->decision == PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessage::REJECT )
	Architect {{ HTML::link($hashTag, 'rejected') }} the Contractor's claim on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'granted') }} {{{ $loe->project->modified_currency_code }}} {{{ number_format($message->grant_different_amount, 2) }}} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif