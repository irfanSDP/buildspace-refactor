<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageFourthLevelContractorForm::accordianId); ?>

Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}.