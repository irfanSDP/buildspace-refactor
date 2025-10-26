<?php $clausesNo = []; ?>

@foreach ( $ai->attachedClauses as $clause )
	<?php $clausesNo[] = $clause->no; ?>
@endforeach

The Architect specified the provision of the Conditions that empowers him to issue the AI, i.e. Clause {{{ implode(' , Clause ', $clausesNo) }}}