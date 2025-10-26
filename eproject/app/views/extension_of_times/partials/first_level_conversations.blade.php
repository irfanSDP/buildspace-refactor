<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
	@foreach ($messages as $message)
	<div>
		<h4 role="tab" aria-selected="false" tabindex="0" id="{{{ str_replace('%id%', $message->id, PCK\Forms\EOTMessageFirstLevelArchitectForm::accordianId) }}}">
			<span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				{{{ PCK\Forms\EOTMessageFirstLevelArchitectForm::formTitle }}}
			@else
				{{{ PCK\Forms\EOTMessageFirstLevelContractorForm::formTitle }}}
			@endif
		</h4>

		<div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
			<p>
				<strong>Subject/Reference:</strong><br>
				{{{ $message->subject }}}
			</p>

			<p>
				<strong>Date Submitted:</strong><br>
				<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($message->created_at) }}}</span> by {{{ $message->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
			</p>

			<p>
				<strong>Details/Cover Letter:</strong><br>
				{{{ $message->details }}}
			</p>

			@if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
				<p>
					<strong>Decision for Contractor's Notice of Intention to Claim EOT:</strong><br>
					@if ( $message->decision )
						Yes
					@else
						No
					@endif
				</p>
			@endif

			@if ( ! $message->attachments->isEmpty() )
				<p>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $eot->project_id])
				</p>
			@endif
		</div>
	</div>
	@endforeach
</div>