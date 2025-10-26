<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
	@foreach ($messages as $message)
	<div>
		<h4 role="tab" aria-selected="false" tabindex="0" id="{{ str_replace('%id%', $message->id, PCK\Forms\AIMessageThirdLevelArchitectForm::accordianId) }}">
			<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				{{{ PCK\Forms\AIMessageThirdLevelArchitectForm::formTitle }}}
			@else
				{{{ PCK\Forms\AIMessageThirdLevelContractorForm::formTitle }}}
			@endif
		</h4>

		<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
			<p>
				<strong>Subject/Reference:</strong><br>
				{{{ $message->subject }}}
			</p>

			<p>
				<strong>Date Submitted:</strong><br>
				<span class="dateSubmitted">{{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($ai->project, $ai->created_at) }}}
			</p>

			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				<p>
					<strong>Has the Contractor complied with the AI:</strong><br>
					@if ( $message->compliance_status == PCK\ArchitectInstructions\StatusType::COMPLIED )
						{{{ PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessage::TYPE_YES }}}
					@elseif ( $message->compliance_status == PCK\ArchitectInstructions\StatusType::NOT_COMPLIED )
						{{{ PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessage::TYPE_NO_DID_NOT_COMPLY }}}
					@else
						{{{ PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessage::TYPE_NO_OUTSTANDING }}}
					@endif
				</p>

				<p>
					<strong>Letter to Contractor:</strong><br>
					{{{ $message->reason }}}
				</p>
			@else
				<p>
					<strong>Date of Compliance:</strong><br>
					{{{ $ai->project->getProjectTimeZoneTime($message->compliance_date) }}}
				</p>

				<p>
					<strong>Letter to Architect:</strong><br>
					{{{ $message->reason }}}
				</p>
			@endif

			@if ( ! $message->attachments->isEmpty() )
				<p>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $ai->project_id])
				</p>
			@endif
		</div>
	</div>
	@endforeach
</div>