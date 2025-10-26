<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>{{{ PCK\Forms\AIMessageThirdLevelContractorForm::formTitle }}}</h2>
</header>

<!-- widget div-->
<div>
	<!-- widget content -->
	<div class="widget-body no-padding">
		{{ Form::open(array('class' => 'smart-form')) }}
			<fieldset>
				<section>
					<label class="label">AI Reference:</label>
					{{ link_to_route('ai.show', $ai->reference, array($ai->project_id, $ai->id)) }}
				</section>

				<section>
					<label class="label">Subject/Reference<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
						{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
					</label>
					{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Date of Compliance<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('compliance_date') ? 'state-error' : null }}}">
						{{ Form::text('compliance_date', Input::old('compliance_date'), array('class' => 'finishdate')) }}
					</label>
					{{ $errors->first('compliance_date', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Letter to Architect<span class="required">*</span>:</label>
					<label class="textarea {{{ $errors->has('reason') ? 'state-error' : null }}}">
						{{ Form::textarea('reason', Input::old('reason'), array('required' => 'required', 'rows' => 3)) }}
					</label>
					{{ $errors->first('reason', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Attachment(s):</label>

					@include('file_uploads.partials.upload_file_modal', ['project' => $ai->project])
				</section>
			</fieldset>

			<footer>
				{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

				{{ link_to_route('ai.show', 'Cancel', [$ai->project_id, $ai->id], ['class' => 'btn btn-default']) }}
			</footer>
		{{ Form::close() }}
	</div>
	<!-- end widget content -->
</div>
<!-- end widget div -->