<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\AEMessageSecondLevelArchitectForm::accordianId); ?>

@if ( $message->decision == PCK\LossOrAndExpenseSecondLevelMessages\LossOrAndExpenseSecondLevelMessage::REJECT_DEADLINE )
	Architect {{ HTML::link($hashTag, 'rejected') }} the application on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'extended') }} the deadline to {{{ $ae->project->getProjectTimeZoneTime($message->grant_different_deadline) }}} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif