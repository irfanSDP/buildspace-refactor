<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\AEMessageFourthLevelArchitectQsForm::accordianId); ?>

@if ( $message->decision == PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage::REJECT )
	Architect {{ HTML::link($hashTag, 'rejected') }} the Contractor's claim on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'granted') }} {{{ $ae->project->modified_currency_code }}} {{{ number_format($message->grant_different_amount, 2) }}} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif