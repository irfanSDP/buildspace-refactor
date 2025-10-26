<fieldset>
	<section>
		<label class="label">Project Title:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">Reference to AI/CAI (if relevant)<span class="required">*</span>:</label>

		@if ( is_object($ai) )
			{{ link_to_route('ai.show', $ai->reference, [$ai->project_id, $ai->id]) }}

			{{ Form::hidden('architect_instruction_id', $ai->id) }}
		@else
			<label class="select {{{ $errors->has('architect_instruction_id') ? 'state-error' : null }}}">
				{{ Form::select('architect_instruction_id', $ai, Input::old('architect_instruction_id'), ['data-ai-selector' => true]) }}
				<i></i>
			</label>
			{{ $errors->first('architect_instruction_id', '<em class="invalid">:message</em>') }}
		@endif
	</section>

	<section>
		<label class="label">Cause(s) of EOT<span class="required">*</span>: {{ $errors->first('selected_clauses', '<em class="invalid" style="color: red;">:message</em>') }}</label>
		<div style="height: 480px; overflow-y: scroll;">
			@foreach ( $clause->items as $item )
				<label>
					@if ( empty($selectedClauseIds) )
						{{ Form::checkbox('selected_clauses[]', $item->id) }}
					@else
						{{ Form::checkbox('selected_clauses[]', $item->id, in_array($item->id, $selectedClauseIds)) }}
					@endif

					@include('clause_items.partials.clause_item_description_formatter', ['item' => $item])
				</label>
				<br/>
				<br/>
			@endforeach
		</div>
	</section>

	<section>
		<label class="label">Date of AI/CAI/Commencement of Event<span class="required">*</span>:</label>

		@if ( is_object($ai) )
			<?php $aiDate = Carbon\Carbon::parse($project->getProjectTimeZoneTime($ai->created_at))->format(\Config::get('dates.submission_date_formatting')); ?>

			{{{ $aiDate }}}

			{{ Form::hidden('commencement_date_of_event', $aiDate) }}
		@else
			<label class="input {{{ $errors->has('commencement_date_of_event') ? 'state-error' : null }}}">
				{{ Form::text('commencement_date_of_event', Input::old('commencement_date_of_event'), array('id' => 'aiCommencementDate', 'class' => 'finishdate')) }}
			</label>
			{{ $errors->first('commencement_date_of_event', '<em class="invalid">:message</em>') }}
		@endif
	</section>

	<section>
		<label class="label">Deadline to submit notice to claim ({{{ $project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot }}} days from date above):</label>
		<label class="input">
			<span id="new_deadline">
				@if ( is_object($ai) )
					{{{ $project->getProjectTimeZoneTime(PCK\ExtensionOfTimes\ExtensionOfTime::calculateDeadlineToSubmitNoticeToClaim($project, $ai->created_at, $project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot)) }}}
				@else
					@if ( Input::old('commencement_date_of_event') and strtotime(Input::old('commencement_date_of_event')) )
						{{{ PCK\ExtensionOfTimes\ExtensionOfTime::calculateDeadlineToSubmitNoticeToClaim($project, Input::old('commencement_date_of_event'), $project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot) }}}
					@elseif ( isset($eot) )
						{{{ $project->getProjectTimeZoneTime(PCK\ExtensionOfTimes\ExtensionOfTime::calculateDeadlineToSubmitNoticeToClaim($project, $eot->commencement_date_of_event, $project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot)) }}}
					@else
						Please select a date from above
					@endif
				@endif
			</span>
		</label>
	</section>

	<section>
		<label class="label">Subject/Reference<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
			{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Detailed Elaborations to Substantiate Claim/Cover Letter<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('detailed_elaborations') ? 'state-error' : null }}}">
			{{ Form::textarea('detailed_elaborations', Input::old('detailed_elaborations'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('detailed_elaborations', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Initial Estimate of EOT (Days)<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('initial_estimate_of_eot') ? 'state-error' : null }}}">
			{{ Form::text('initial_estimate_of_eot', Input::old('initial_estimate_of_eot'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('initial_estimate_of_eot', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Attachment(s):</label>

		@include('file_uploads.partials.upload_file_modal')
	</section>

	{{ Form::hidden('deadline_days', $project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot, ['id' => 'deadline_days']) }}
</fieldset>