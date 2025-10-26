<div class="widget-body no-padding">
	<div class="smart-form">
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				<label class="input">
					{{{ $ai->project->title }}}
				</label>
			</section>

			<section>
				<strong>AI Reference:</strong><br>
				{{{ $ai->reference }}}
			</section>

			@if ( $ai->haveClause() )
				<section>
					<strong>Clause(s) that empower the issuance of AI:</strong><br>
					<div>
						@foreach ( $ai->attachedClauses as $clause )
							@include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
							<br/>
							<br/>
						@endforeach
					</div>
				</section>
			@endif

			<section>
				<strong>Architect's Instruction:</strong><br>
				{{{ $ai->instruction }}}
			</section>

			<section>
				<strong>Date AI Issued:</strong><br>
				<label class="input">
					<strong class="dateSubmitted"><i>{{{ $ai->project->getProjectTimeZoneTime($ai->created_at) }}}</i></strong> by {{{ $ai->createdBy->present()->byWhoAndRole($ai->project, $ai->created_at) }}}
				</label>
			</section>

			<section>
				<strong>Deadline to Comply:</strong><br>
				{{{ $ai->project->getProjectTimeZoneTime($ai->deadline_to_comply) }}}
			</section>

			@if ( ! $ai->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ai->attachments, 'projectId' => $ai->project_id])
				</section>
			@endif
		</fieldset>
	</div>
</div>

@if ( $ai->messages->count() > 0 )
	<h3>Additional Information</h3>

	@include('architect_instructions.partials.first_level_conversations', array('messages' => $ai->messages))
@endif