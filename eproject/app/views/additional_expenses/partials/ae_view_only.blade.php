<div class="widget-body no-padding">
	<div class="smart-form">
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				{{{ $ae->project->title }}}
			</section>

			<section>
				<strong>Reference to AI/CAI (if relevant):</strong><br>
				@if ( $ae->architectInstruction )
					{{ link_to_route('ai.show', $ae->architectInstruction->reference, array($ae->project->id, $ae->architectInstruction->id)) }}
				@else
					Not related to any AI
				@endif
			</section>

			@if ( ! $ae->attachedClauses->isEmpty() )
				<section>
					<strong>Cause(s) of AE:</strong><br>
					<div>
						@foreach ( $ae->attachedClauses as $clause )
							@include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
							<br/>
							<br/>
						@endforeach
					</div>
				</section>
			@endif

			<section>
				<strong>Date Notice Submitted:</strong><br>
				<strong class="dateSubmitted"><i>{{{ $ae->project->getProjectTimeZoneTime($ae->created_at) }}}</i></strong> by {{{ $ae->createdBy->present()->byWhoAndRole($ae->project, $ae->created_at) }}}
			</section>

			<section>
				<strong>Date of AI/CAI/Commencement of Event:</strong><br>
				{{{ $ae->project->getProjectTimeZoneTime($ae->commencement_date_of_event) }}}
			</section>

			<section>
				<strong>Deadline to submit notice to claim ({{{ $ae->project->pam2006Detail->deadline_submitting_note_of_intention_claim_ae }}} days from date above):</strong><br>
				{{{ $ae->deadline_to_submit_notice_to_claim }}}
			</section>

			<section>
				<strong>Subject/Reference:</strong><br>
				{{{ $ae->subject }}}
			</section>

			<section>
				<strong>Detailed Elaborations to Substantiate Claim/Cover Letter:</strong><br>
				{{{ $ae->detailed_elaborations }}}
			</section>

			<section>
				<strong>Initial Estimate of the Claim ({{{ $ae->project->modified_currency_code }}}):</strong><br>
				{{{ number_format($ae->initial_estimate_of_claim, 2) }}}
			</section>

			@if ( ! $ae->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ae->attachments, 'projectId' => $ae->project_id])
				</section>
			@endif
		</fieldset>
	</div>
</div>

@if ( $ae->firstLevelMessages->count() > 0 )
	<h3>Additional Information</h3>

	@include('additional_expenses.partials.first_level_conversations', array('messages' => $ae->firstLevelMessages))
@endif