<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\EOTMessageFourthLevelArchitectForm::accordianId); ?>

@if ( $message->decision == PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage::REJECT_DEADLINE )
	Architect {{ HTML::link($hashTag, 'rejected') }} the Contractor's application for EOT on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@else
	Architect {{ HTML::link($hashTag, 'accepted') }} the Contractor's application and issued a Certificate of EOT. {{{ $message->grant_different_days }}} day(s) of EOT was granted on {{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}.
@endif