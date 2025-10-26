<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageFirstLevelArchitectForm::accordianId); ?>

@if ( $message->decision )
	Architect {{ HTML::link($hashTag, 'accepted') }} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'rejected') }} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif