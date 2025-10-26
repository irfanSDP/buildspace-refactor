<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>{{{ PCK\Forms\AEMessageFourthLevelContractorForm::formTitle }}}</h2>
</header>

<!-- widget div-->
<div>
	<!-- widget content -->
	<div class="widget-body no-padding">
		{{ Form::open(array('class' => 'smart-form')) }}
			<fieldset>
				<section>
					<label class="label">Final Additional Expense Claim's Reference:</label>
					{{{ $ae->additionalExpenseClaim->subject }}}
				</section>

				<section>
					<label class="label">Notice of Intention To Claim Additional Expense's Reference:</label>
					{{ link_to_route('ae.show', $ae->subject, array($ae->project_id, $ae->id)) }}
				</section>

				<section>
					<label class="label">Deadline for Submission:</label>
					{{{ $ae->project->getProjectTimeZoneTime($ae->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</section>

				<section>
					<label class="label">Date of Submission:</label>
					<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($ae->additionalExpenseClaim->created_at) }}}</span>
				</section>

				<section>
					<label class="label">Final Claim Amount ({{{ $ae->project->modified_currency_code }}}):</label>
					{{{ number_format($ae->additionalExpenseClaim->final_claim_amount, 2) }}}
				</section>

				@if ( $aeLastArchitectMessage )
					<section>
						<label class="label">Decision of the Architect:</label>

						@include('additional_expense_fourth_level_messages.partials.architect_decision')
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