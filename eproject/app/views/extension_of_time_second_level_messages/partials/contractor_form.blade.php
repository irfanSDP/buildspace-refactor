<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>
		@if ( ! $eotLastMessage )
			{{{ PCK\Forms\EOTMessageSecondLevelContractorForm::formTitleOne }}}
		@else
			{{{ PCK\Forms\EOTMessageSecondLevelContractorForm::formTitleTwo }}}
		@endif
	</h2>
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
					<label class="label">The Original Deadline:</label>
					{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
				</section>

				<section>
					<label class="label">Requested New Deadline for Submission<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('requested_new_deadline') ? 'state-error' : null }}}">
						@if ( ! $eotLastMessage )
							{{ Form::text('requested_new_deadline', Input::old('requested_new_deadline'), array('class' => 'finishdate', 'required')) }}
						@else
							{{{ $eot->project->getProjectTimeZoneTime($eotLastMessage->requested_new_deadline) }}}
							{{ Form::hidden('requested_new_deadline', $eot->project->getProjectTimeZoneTime($eotLastMessage->requested_new_deadline)) }}
						@endif
					</label>
					{{ $errors->first('requested_new_deadline', '<em class="invalid">:message</em>') }}
				</section>

				@if ( $eotLastMessage )
					<section>
						<label class="label">Decision of the Architect:</label>
						@if ( $eotLastMessage->decision == PCK\ExtensionOfTimeSecondLevelMessages\ExtensionOfTimeSecondLevelMessage::REJECT_DEADLINE )
							Application Rejected
						@else
							Deadline extended to {{{ $eot->project->getProjectTimeZoneTime($eotLastMessage->grant_different_deadline) }}}
						@endif
					</section>
				@endif

				<section>
					<label class="label">Subject/Reference<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
						{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
					</label>
					{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Letter to the Architect<span class="required">*</span>:</label>
					<label class="textarea {{{ $errors->has('message') ? 'state-error' : null }}}">
						{{ Form::textarea('message', Input::old('message'), array('required' => 'required', 'rows' => 3)) }}
					</label>
					{{ $errors->first('message', '<em class="invalid">:message</em>') }}
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