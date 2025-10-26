<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>{{{ PCK\Forms\EOTMessageFourthLevelArchitectForm::formTitle }}}</h2>
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

				<section>
					<label class="label">Subject/Reference<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
						{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
					</label>
					{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Letter to the Contractor<span class="required">*</span>:</label>
					<label class="textarea {{{ $errors->has('message') ? 'state-error' : null }}}">
						{{ Form::textarea('message', Input::old('message'), array('required' => 'required', 'rows' => 3)) }}
					</label>
					{{ $errors->first('message', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Decision<span class="required">*</span>:</label>
					<label class="{{{ ($errors->has('decision') or $errors->has('grant_different_days')) ? 'state-error' : null }}}" style="display:block;">
						{{ Form::radio('decision', PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage::EXTEND_DEADLINE) }}
						Grant the EOT of days as applied by the Contractor
					</label>
					<label class="{{{ ($errors->has('decision') or $errors->has('grant_different_days')) ? 'state-error' : null }}}" style="display:block;">
						{{ Form::radio('decision', PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage::REJECT_DEADLINE) }}
						Reject the Contractor's application for EOT
					</label>
					<label class="{{{ ($errors->has('decision') or $errors->has('grant_different_days')) ? 'state-error' : null }}}" style="display: inline;">
						{{ Form::radio('decision', PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage::GRANT_DIFF_DEADLINE) }} Grant
					</label>

					{{ Form::text('grant_different_days', Input::old('grant_different_days')) }} day(s)

					{{ $errors->first('decision', '<br><em class="invalid">:message</em>') }}
					{{ $errors->first('grant_different_days', '<br><em class="invalid">:message</em>') }}
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