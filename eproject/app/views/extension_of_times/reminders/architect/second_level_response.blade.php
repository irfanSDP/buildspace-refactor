<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageSecondLevelArchitectForm::accordianId); ?>

@if ( $message->decision == PCK\ExtensionOfTimeSecondLevelMessages\ExtensionOfTimeSecondLevelMessage::REJECT_DEADLINE )
	Architect {{ HTML::link($hashTag, 'rejected') }} the application on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'extended') }} the deadline to {{{ $eot->project->getProjectTimeZoneTime($message->grant_different_deadline) }}} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif