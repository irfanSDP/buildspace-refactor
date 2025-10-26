<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>
		@if ( $aeLastMessage )
			{{{ PCK\Forms\AEMessageSecondLevelContractorForm::formTitleTwo }}}
		@else
			{{{ PCK\Forms\AEMessageSecondLevelContractorForm::formTitleOne }}}
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
					<label class="label">Notice of Intention To Claim Additional Expense Reference:</label>
					{{ link_to_route('ae.show', $ae->subject, array($ae->project_id, $ae->id)) }}
				</section>

				<section>
					<label class="label">The Original Deadline:</label>
					{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</section>

				<section>
					<label class="label">Requested New Deadline for Submission<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('requested_new_deadline') ? 'state-error' : null }}}">
						@if ( ! $aeLastMessage )
							{{ Form::text('requested_new_deadline', Input::old('requested_new_deadline'), array('class' => 'finishdate', 'required')) }}
						@else
							{{{ $ae->project->getProjectTimeZoneTime($aeLastMessage->requested_new_deadline) }}}
							{{ Form::hidden('requested_new_deadline', $ae->project->getProjectTimeZoneTime($aeLastMessage->requested_new_deadline)) }}
						@endif
					</label>
					{{ $errors->first('requested_new_deadline', '<em class="invalid">:message</em>') }}
				</section>

				@if ( $aeLastMessage )
					<section>
						<label class="label">Decision of the Architect:</label>
						@if ( $aeLastMessage->decision == PCK\AdditionalExpenseSecondLevelMessages\AdditionalExpenseSecondLevelMessage::REJECT_DEADLINE )
							Application Rejected
						@else
							Deadline extended to {{{ $ae->project->getProjectTimeZoneTime($aeLastMessage->grant_different_deadline) }}}
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

					@include('file_uploads.partials.upload_file_modal', ['project' => $ae->project])
				</section>
			</fieldset>

			<footer>
				{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

				{{ link_to_route('ae.show', 'Cancel', [$ae->project->id, $ae->id], ['class' => 'btn btn-default']) }}
			</footer>
		{{ Form::close() }}
	</div>
	<!-- end widget content -->
</div>
<!-- end widget div -->