<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageFourthLevelContractorForm::accordianId); ?>

Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.