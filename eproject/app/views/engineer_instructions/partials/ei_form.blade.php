<fieldset>
	<section>
		<label class="label">Project Title:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">Subject/EI Reference<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
			{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Details/Cover Letter<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('detailed_elaborations') ? 'state-error' : null }}}">
			{{ Form::textarea('detailed_elaborations', Input::old('detailed_elaborations'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('detailed_elaborations', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Deadline to Comply With<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('deadline_to_comply_with') ? 'state-error' : null }}}">
			{{ Form::text('deadline_to_comply_with', Input::old('deadline_to_comply_with'), array('class' => 'finishdate')) }}
		</label>
		{{ $errors->first('deadline_to_comply_with', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Attachment(s):</label>

		@include('file_uploads.partials.upload_file_modal')
	</section>
</fieldset>