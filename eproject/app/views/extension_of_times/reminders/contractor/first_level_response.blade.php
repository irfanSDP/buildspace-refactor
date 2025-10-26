<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageFirstLevelArchitectForm::accordianId); ?>

Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}