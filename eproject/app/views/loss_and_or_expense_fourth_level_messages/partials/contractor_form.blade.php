<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>{{{ PCK\Forms\LOEMessageFourthLevelContractorForm::formTitle }}}</h2>
</header>

<!-- widget div-->
<div>
	<!-- widget content -->
	<div class="widget-body no-padding">
		{{ Form::open(array('class' => 'smart-form')) }}
			<fieldset>
				<section>
					<label class="label">Final Loss And/Or Expense Claim's Reference:</label>
					{{{ $loe->lossOrAndExpenseClaim->subject }}}
				</section>

				<section>
					<label class="label">Notice of Intention To Claim Loss And/Or Expense's Reference:</label>
					{{ link_to_route('loe.show', $loe->subject, array($loe->project_id, $loe->id)) }}
				</section>

				<section>
					<label class="label">Deadline for Submission:</label>
					{{{ $loe->project->getProjectTimeZoneTime($loe->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
				</section>

				<section>
					<label class="label">Date of Submission:</label>
					<span class="dateSubmitted">{{{ $loe->project->getProjectTimeZoneTime($loe->lossOrAndExpenseClaim->created_at) }}}</span>
				</section>

				<section>
					<label class="label">Final Claim Amount ({{{ $loe->project->modified_currency_code }}}):</label>
					{{{ number_format($loe->lossOrAndExpenseClaim->final_claim_amount, 2) }}}
				</section>

				@if ( $loeLastArchitectMessage )
					<section>
						<label class="label">Decision of the Architect:</label>
						@include('loss_and_or_expense_fourth_level_messages.partials.architect_decision')
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