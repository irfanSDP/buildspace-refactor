<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>{{{ PCK\Forms\EOTMessageFirstLevelArchitectForm::formTitle }}}</h2>
</header>

<!-- widget div-->
<div>
	<!-- widget content -->
	<div class="widget-body no-padding">
		{{ Form::open(array('class' => 'smart-form')) }}
			<fieldset>
				<section>
					<label class="label">Notice of Intention To Claim EOT's Reference:</label>
					{{ link_to_route('eot.show', $eot->subject, array($eot->project_id, $eot->id)) }}
				</section>

				<section>
					<label class="label">Date of Submission:</label>
					<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($eot->created_at) }}}</span>
				</section>

				<section>
					<label class="label">Subject/Reference<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
						{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
					</label>
					{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Details/Cover Letter<span class="required">*</span>:</label>
					<label class="textarea {{{ $errors->has('details') ? 'state-error' : null }}}">
						{{ Form::textarea('details', Input::old('details'), array('required' => 'required', 'rows' => 3)) }}
					</label>
					{{ $errors->first('details', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Decision for Contractor's Notice of Intention to Claim EOT<span class="required">*</span>:</label>
					<label class="radio {{{ $errors->has('decision') ? 'state-error' : null }}}">
						{{ Form::radio('decision', 1) }}
						<i></i>Accept
					</label>
					<label class="radio {{{ $errors->has('decision') ? 'state-error' : null }}}">
						{{ Form::radio('decision', 0) }}
						<i></i>Reject
					</label>
					{{ $errors->first('decision', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Attachment(s):</label>

					@include('file_uploads.partials.upload_file_modal', ['project' => $eot->project])
				</section>
			</fieldset>

			<footer>
				{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

				{{ link_to_route('eot.show', 'Cancel', [$eot->project->id, $eot->id], ['class' => 'btn btn-default']) }}
			</footer>
		{{ Form::close() }}
	</div>
	<!-- end widget content -->
</div>
<!-- end widget div -->