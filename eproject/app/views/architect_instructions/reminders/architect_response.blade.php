<?php $clausesNo = []; ?>

<?php $hashTag = '#' . str_replace('%id%', $message->id, PCK\Forms\ArchitectInstructionMessageForm::accordianId); ?>

@foreach ( $message->attachedClauses as $clause )
	<?php $clausesNo[] = $clause->no; ?>
@endforeach

<li>
	The Architect {{ HTML::link($hashTag, 'specified') }} the provision of the Conditions on {{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}} {{{ ! empty($clausesNo) ? '– i.e. Clause ' . implode(' , Clause ', $clausesNo) : null }}}
</li>