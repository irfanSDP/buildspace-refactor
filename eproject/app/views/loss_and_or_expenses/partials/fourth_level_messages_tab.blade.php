<div id="s4" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		@foreach ( $loe->fourthLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				@include('loss_and_or_expenses.partials.architect_fourth_level_info_tab', array('message' => $message))
			@elseif ( $message->type == PCK\ContractGroups\Types\Role::CONTRACTOR )
				@include('loss_and_or_expenses.partials.contractor_fourth_level_info_tab', array('message' => $message))
			@endif
		@endforeach
	</div>
</div>