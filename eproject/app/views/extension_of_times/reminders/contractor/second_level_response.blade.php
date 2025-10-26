<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageSecondLevelContractorForm::accordianId); ?>

@if ( $messageCount == 0 )
	The Contractor requested the Architect to {{ HTML::link($hashTag, 'extend the deadline') }} to submit EOT claim to {{{ $eot->project->getProjectTimeZoneTime($message->requested_new_deadline) }}} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif