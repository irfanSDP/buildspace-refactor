<fieldset>
	<section>
		<label class="label">{{ trans('projects.project') }}:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">{{ trans('earlyWarnings.reference') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('reference') ? 'state-error' : null }}}">
			{{ Form::text('reference', Input::old('reference'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('reference', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('earlyWarnings.details') }}<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('impact') ? 'state-error' : null }}}">
			{{ Form::textarea('impact', Input::old('impact'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('impact', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('earlyWarnings.commencementDate') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('commencement_date') ? 'state-error' : null }}}">
			{{ Form::text('commencement_date', Input::old('commencement_date'), array('required' => 'required', 'class' => 'anydate')) }}
		</label>
		{{ $errors->first('commencement_date', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('forms.attachments') }}:</label>

		@include('file_uploads.partials.upload_file_modal')
	</section>
</fieldset>