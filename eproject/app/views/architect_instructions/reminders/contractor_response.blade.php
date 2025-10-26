<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\ArchitectInstructionMessageForm::accordianId); ?>

<li>On {{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}, the Contractor informed the Architect that he is {{ HTML::link($hashTag, 'not satisfied') }} with the provision of the Conditions specified.</li>