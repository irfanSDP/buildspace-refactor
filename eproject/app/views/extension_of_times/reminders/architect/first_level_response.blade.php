<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageFirstLevelArchitectForm::accordianId); ?>

@if ( $message->decision )
	Architect {{ HTML::link($hashTag, 'accepted') }} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'rejected') }} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif