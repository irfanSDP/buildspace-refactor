<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageSecondLevelArchitectForm::accordianId); ?>

@if ( $message->decision == PCK\AdditionalExpenseSecondLevelMessages\AdditionalExpenseSecondLevelMessage::REJECT_DEADLINE )
	Architect {{ HTML::link($hashTag, 'rejected') }} the application on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'extended') }} the deadline to {{{ $loe->project->getProjectTimeZoneTime($message->grant_different_deadline) }}} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif