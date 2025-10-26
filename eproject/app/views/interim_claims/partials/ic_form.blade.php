<fieldset>
	<section>
		<label class="label">Project Title:</label>
		{{{ $project->title }}}
	</section>

	<section>
		<label class="label">Interim Claim No<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('claim_no') ? 'state-error' : null }}}">
			{{{ $claimCounter }}}

			{{ Form::hidden('claim_no', $claimCounter) }}
		</label>
		{{ $errors->first('claim_no', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Month/Year<span class="required">*</span>:</label>
		<div>
			{{ Form::selectMonth('month', Input::old('month', date('n'))) }}
			{{ Form::selectYear('year', date('Y'), date('Y') + 10, Input::old('year')) }}
		</div>
		{{ $errors->first('month', '<em class="invalid">:message</em>') }}
		{{ $errors->first('year', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Deadline to Issue Interim Certificate ({{{ $project->pam2006Detail->period_of_architect_issue_interim_certificate }}} days from submitted date):</label>
		{{{ ($calendarRepo->calculateFinalDate($project, Carbon\Carbon::now($project->timezone), $project->pam2006Detail->period_of_architect_issue_interim_certificate)) }}}
	</section>

	<section>
		<label class="label">Cover Letter<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('note') ? 'state-error' : null }}}">
			{{ Form::textarea('note', Input::old('note'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('note', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Attachment(s):</label>

		@include('file_uploads.partials.upload_file_modal', ['project' => $project])
	</section>
</fieldset>