<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>{{{ PCK\Forms\EOTMessageFourthLevelContractorForm::formTitle }}}</h2>
</header>

<!-- widget div-->
<div>
	<!-- widget content -->
	<div class="widget-body no-padding">
		{{ Form::open(array('class' => 'smart-form')) }}
			<fieldset>
				<section>
					<label class="label">Final EOT Claim's Reference:</label>
					{{{ $eot->extensionOfTimeClaim->subject }}}
				</section>

				<section>
					<label class="label">Notice of Intention To Claim EOT's Reference:</label>
					{{ link_to_route('eot.show', $eot->subject, array($eot->project_id, $eot->id)) }}
				</section>

				<section>
					<label class="label">Deadline for Submission:</label>
					{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
				</section>

				<section>
					<label class="label">Date of Submission:</label>
					<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($eot->extensionOfTimeClaim->created_at) }}}</span>
				</section>

				<section>
					<label class="label">EOT Claimed:</label>
					{{{ $eot->extensionOfTimeClaim->days_claimed }}} day(s)
				</section>

				@if ( $eotLastMessage )
					<section>
						<label class="label">Decision of the Architect:</label>
						@if ( $eotLastMessage->decision == PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage::REJECT_DEADLINE )
							Application Rejected
						@else
							{{{ $eotLastMessage->grant_different_days }}} day(s) of EOT granted
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