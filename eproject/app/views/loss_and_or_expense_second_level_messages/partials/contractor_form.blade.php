<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>
		@if ( $loeLastMessage )
			{{{ PCK\Forms\LOEMessageSecondLevelContractorForm::formTitleTwo }}}
		@else
			{{{ PCK\Forms\LOEMessageSecondLevelContractorForm::formTitleOne }}}
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
					<label class="label">Notice of Intention To Claim Loss And/Or Expense Reference:</label>
					{{ link_to_route('loe.show', $loe->subject, array($loe->project_id, $loe->id)) }}
				</section>

				<section>
					<label class="label">The Original Deadline:</label>
					{{{ $loe->project->getProjectTimeZoneTime($loe->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</section>

				<section>
					<label class="label">Requested New Deadline for Submission<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('requested_new_deadline') ? 'state-error' : null }}}">
						@if ( ! $loeLastMessage )
							{{ Form::text('requested_new_deadline', Input::old('requested_new_deadline'), array('class' => 'finishdate', 'required')) }}
						@else
							{{{ $loe->project->getProjectTimeZoneTime($loeLastMessage->requested_new_deadline) }}}
							{{ Form::hidden('requested_new_deadline', $loe->project->getProjectTimeZoneTime($loeLastMessage->requested_new_deadline)) }}
						@endif
					</label>
					{{ $errors->first('requested_new_deadline', '<em class="invalid">:message</em>') }}
				</section>

				@if ( $loeLastMessage )
					<section>
						<label class="label">Decision of the Architect:</label>
						@if ( $loeLastMessage->decision == PCK\LossOrAndExpenseSecondLevelMessages\LossOrAndExpenseSecondLevelMessage::REJECT_DEADLINE )
							Application Rejected
						@else
							Deadline extended to {{{ $loe->project->getProjectTimeZoneTime($loeLastMessage->grant_different_deadline) }}}
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

					@include('file_uploads.partials.upload_file_modal', ['project' => $loe->project])
				</section>
			</fieldset>

			<footer>
				{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

				{{ link_to_route('loe.show', 'Cancel', [$loe->project->id, $loe->id], ['class' => 'btn btn-default']) }}
			</footer>
		{{ Form::close() }}
	</div>
	<!-- end widget content -->
</div>
<!-- end widget div -->