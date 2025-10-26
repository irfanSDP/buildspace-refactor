<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageThirdLevelContractorForm::accordianId); ?>

Contractor provided {{ HTML::link($hashTag, 'further details') }} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}