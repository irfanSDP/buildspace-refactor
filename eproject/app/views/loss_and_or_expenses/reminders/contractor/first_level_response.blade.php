<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageFirstLevelContractorForm::accordianId); ?>

Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}