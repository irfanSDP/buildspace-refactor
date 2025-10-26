<ul>
	@if ( $ai->latestExtensionOfTime )
		<li>
			<?php $prepared = $ai->latestExtensionOfTime->status == PCK\ArchitectInstructions\ArchitectInstruction::DRAFT_TEXT ? false : true; ?>

			@if ( $prepared )
				{{ link_to_route('eot.show', 'EOT Claim', array($ai->project_id, $ai->latestExtensionOfTime->id)) }} relevant to the AI.
			@else
				EOT Claim under preparation.
			@endif
		</li>
	@endif

	@if ( $ai->latestLossOrAndExpense )
		<li>
			<?php $prepared = $ai->latestLossOrAndExpense->status == PCK\LossOrAndExpenses\LossOrAndExpense::DRAFT_TEXT ? false : true; ?>

			@if ( $prepared )
				{{ link_to_route('loe.show', 'Loss and/or Expense Claim', array($ai->project_id, $ai->latestLossOrAndExpense->id)) }} relevant to the AI.
			@else
				Loss and/or Expense Claim under preparation.
			@endif
		</li>
	@endif

	@if ( $ai->latestAdditionalExpense )
		<li>
			<?php $prepared = $ai->latestAdditionalExpense->status == PCK\AdditionalExpenses\AdditionalExpense::DRAFT_TEXT ? false : true; ?>

			@if ( $prepared )
				{{ link_to_route('ae.show', 'Additional Expense', array($ai->project_id, $ai->latestAdditionalExpense->id)) }} relevant to the AI.
			@else
				Additional Expense under preparation.
			@endif
		</li>
	@endif
</ul>