<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageThirdLevelArchitectForm::accordianId); ?>

Architect {{ HTML::link($hashTag, 'requested') }} Contractor to provide futher particulars on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}