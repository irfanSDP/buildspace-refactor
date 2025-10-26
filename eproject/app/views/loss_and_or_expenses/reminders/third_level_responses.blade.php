<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageThirdLevelArchitectQsForm::accordianId); ?>

@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
	Architect {{ HTML::link($hashTag, 'requested') }} Contractor to provide futher particulars on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}
@elseif ( $message->type == PCK\ContractGroups\Types\Role::CLAIM_VERIFIER )
	QS {{ HTML::link($hashTag, 'requested') }} Contractor to provide futher particulars on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}
@else
	<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\LOEMessageThirdLevelContractorForm::accordianId); ?>

	Contractor provided {{ HTML::link($hashTag, 'further details') }} on {{{ $loe->project->getProjectTimeZoneTime($message->created_at) }}}
@endif