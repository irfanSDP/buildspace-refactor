<div class="widget-body no-padding">
	<div class="smart-form">
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				{{{ $eot->project->title }}}
			</section>

			<section>
				<strong>Reference to AI/CAI (if relevant):</strong><br>
				@if ( $eot->architectInstruction )
					{{ link_to_route('ai.show', $eot->architectInstruction->reference, array($eot->project->id, $eot->architectInstruction->id)) }}
				@else
					Not related to any AI
				@endif
			</section>

			@if ( ! $eot->attachedClauses->isEmpty() )
				<section>
					<strong>Cause(s) of EOT:</strong><br>
					<div>
						@foreach ( $eot->attachedClauses as $clause )
							@include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
							<br/>
							<br/>
						@endforeach
					</div>
				</section>
			@endif

			<section>
				<strong>Date Notice Submitted:</strong><br>
				<strong class="dateSubmitted"><i>{{{ $eot->project->getProjectTimeZoneTime($eot->created_at) }}}</i></strong> by {{{ $eot->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
			</section>

			<section>
				<strong>Date of AI/CAI/Commencement of Event:</strong><br>
				{{{ $eot->project->getProjectTimeZoneTime($eot->commencement_date_of_event) }}}
			</section>

			<section>
				<strong>Deadline to submit notice to claim ({{{ $eot->project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot }}} days from date above):</strong><br>
				{{{ $eot->deadline_to_submit_notice_to_claim }}}
			</section>

			<section>
				<strong>Subject/Reference:</strong><br>
				{{{ $eot->subject }}}
			</section>

			<section>
				<strong>Detailed Elaborations to Substantiate Claim/Cover Letter:</strong><br>
				{{{ $eot->detailed_elaborations }}}
			</section>

			<section>
				<strong>Initial Estimate of EOT (Days):</strong><br>
				{{{ $eot->initial_estimate_of_eot }}}
			</section>

			@if ( ! $eot->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $eot->attachments, 'projectId' => $eot->project_id])
				</section>
			@endif
		</fieldset>
	</div>
</div>

@if ( $eot->firstLevelMessages->count() > 0 )
	<h3>Additional Information</h3>

	@include('extension_of_times.partials.first_level_conversations', array('messages' => $eot->firstLevelMessages))
@endif