<div id="s4" class="tab-pane padding-10">
	<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		@foreach ( $eot->fourthLevelMessages as $message )
			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				@include('extension_of_times.partials.architect_fourth_level_info_tab', array('message' => $message))
			@else
				@include('extension_of_times.partials.contractor_fourth_level_info_tab', array('message' => $message))
			@endif
		@endforeach
	</div>
</div>