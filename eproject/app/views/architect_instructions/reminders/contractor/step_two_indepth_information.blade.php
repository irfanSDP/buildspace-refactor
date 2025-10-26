<ul>
	<li>
		@if ( $ai->latestExtensionOfTime )
			<?php $prepared = $ai->latestExtensionOfTime->status == PCK\ExtensionOfTimes\ExtensionOfTime::DRAFT_TEXT ? false : true; ?>

			{{ link_to_route('eot.show', 'EOT Claim', array($ai->project_id, $ai->latestExtensionOfTime->id)) }} {{ $prepared ? 'submitted' : '<i>under preparation</i>' }}.
		@else
			{{ link_to_route('eot.create', 'Apply New EOT Claim', array($ai->project_id, $ai->id)) }}.
		@endif
	</li>
	<li>
		@if ( $ai->latestLossOrAndExpense )
			<?php $prepared = $ai->latestLossOrAndExpense->status == PCK\LossOrAndExpenses\LossOrAndExpense::DRAFT_TEXT ? false : true; ?>

			{{ link_to_route('loe.show', 'Loss and/or Expense Claim', array($ai->project_id, $ai->latestLossOrAndExpense->id)) }} {{ $prepared ? 'submitted' : '<i>under preparation</i>' }}.
		@else
			{{ link_to_route('loe.create', 'Apply New Loss and/or Expense Claim', array($ai->project_id, $ai->id)) }}.
		@endif
	</li>
	<li>
		@if ( $ai->latestAdditionalExpense )
			<?php $prepared = $ai->latestAdditionalExpense->status == PCK\AdditionalExpenses\AdditionalExpense::DRAFT_TEXT ? false : true; ?>

			{{ link_to_route('ae.show', 'Additional Expense Claim', array($ai->project_id, $ai->latestAdditionalExpense->id)) }} {{ $prepared ? 'submitted' : '<i>under preparation</i>' }}.
		@else
			{{ link_to_route('ae.create', 'Apply New Additional Expense Claim', array($ai->project_id, $ai->id)) }}.
		@endif
	</li>
</ul>