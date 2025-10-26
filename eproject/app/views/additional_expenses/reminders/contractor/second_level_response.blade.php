<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\AEMessageSecondLevelContractorForm::accordianId); ?>

@if ( $messageCount == 0 )
	The Contractor requested the Architect to {{ HTML::link($hashTag, 'extend the deadline') }} to submit Additional Expense claim to {{{ $ae->project->getProjectTimeZoneTime($message->requested_new_deadline) }}} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Contractor {{ HTML::link($hashTag, 'appealed') }} on {{{ $ae->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif