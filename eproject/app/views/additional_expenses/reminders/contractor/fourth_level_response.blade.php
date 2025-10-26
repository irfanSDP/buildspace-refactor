<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\AEMessageFourthLevelContractorForm::accordianId); ?>

Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.