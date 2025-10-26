<div class="widget-body no-padding">
	<div class="smart-form">
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				{{{ $loe->project->title }}}
			</section>

			<section>
				<strong>Reference to AI/CAI (if relevant):</strong><br>
				@if ( $loe->architectInstruction )
					{{ link_to_route('ai.show', $loe->architectInstruction->reference, array($loe->project->id, $loe->architectInstruction->id)) }}
				@else
					Not related to any AI
				@endif
			</section>

			@if ( ! $loe->attachedClauses->isEmpty() )
				<section>
					<strong>Cause(s) of EOT:</strong><br>
					<div>
						@foreach ( $loe->attachedClauses as $clause )
							@include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
							<br/>
							<br/>
						@endforeach
					</div>
				</section>
			@endif

			<section>
				<strong>Date Notice Submitted:</strong><br>
				<strong class="dateSubmitted"><i>{{{ $loe->project->getProjectTimeZoneTime($loe->created_at) }}}</i></strong> by {{{ $loe->createdBy->present()->byWhoAndRole($loe->project, $loe->created_at) }}}
			</section>

			<section>
				<strong>Date of AI/CAI/Commencement of Event:</strong><br>
				{{{ $loe->project->getProjectTimeZoneTime($loe->commencement_date_of_event) }}}
			</section>

			<section>
				<strong>Deadline to submit notice to claim ({{{ $loe->project->pam2006Detail->deadline_submitting_note_of_intention_claim_l_and_e }}} days from date above):</strong><br>
				{{{ $loe->deadline_to_submit_notice_to_claim }}}
			</section>

			<section>
				<strong>Subject/Reference:</strong><br>
				{{{ $loe->subject }}}
			</section>

			<section>
				<strong>Detailed Elaborations to Substantiate Claim/Cover Letter:</strong><br>
				{{{ $loe->detailed_elaborations }}}
			</section>

			<section>
				<strong>Initial Estimate of the Claim ({{{ $loe->project->modified_currency_code }}}):</strong><br>
				{{{ number_format($loe->initial_estimate_of_claim, 2) }}}
			</section>

			@if ( ! $loe->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $loe->attachments, 'projectId' => $loe->project_id])
				</section>
			@endif
		</fieldset>
	</div>
</div>

@if ( $loe->firstLevelMessages->count() > 0 )
	<h3>Additional Information</h3>

	@include('loss_and_or_expenses.partials.first_level_conversations', array('messages' => $loe->firstLevelMessages))
@endif