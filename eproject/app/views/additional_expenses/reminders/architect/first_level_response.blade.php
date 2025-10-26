<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\AEMessageFirstLevelArchitectForm::accordianId); ?>

@if ( $message->decision )
	Architect {{ HTML::link($hashTag, 'accepted') }} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'rejected') }} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif