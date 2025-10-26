<header>
	<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
	<h2>
		@if ($user->hasCompanyProjectRole($ae->project, PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER))
			{{{ PCK\Forms\AEMessageFourthLevelArchitectQsForm::formTitleOne }}}
		@else
			{{{ PCK\Forms\AEMessageFourthLevelArchitectQsForm::formTitleTwo }}}
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

				<section>
					<label class="label">Subject/Reference<span class="required">*</span>:</label>
					<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
						{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
					</label>
					{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">
						@if ($user->hasCompanyProjectRole($ae->project, PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER))
							Letter to the Contractor<span class="required">*</span>:
						@else
							Letter to the Architect<span class="required">*</span>:
						@endif
					</label>
					<label class="textarea {{{ $errors->has('message') ? 'state-error' : null }}}">
						{{ Form::textarea('message', Input::old('message'), array('required' => 'required', 'rows' => 3)) }}
					</label>
					{{ $errors->first('message', '<em class="invalid">:message</em>') }}
				</section>

				<section>
					<label class="label">Decision<span class="required">*</span>:</label>
					<label class="{{{ ($errors->has('decision') or $errors->has('grant_different_amount')) ? 'state-error' : null }}}" style="display:block;">
						{{ Form::radio('decision', PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage::GRANT) }}
						Grant the amount claimed by the Contractor
					</label>
					<label class="{{{ ($errors->has('decision') or $errors->has('grant_different_amount')) ? 'state-error' : null }}}" style="display:block;">
						{{ Form::radio('decision', PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage::REJECT) }}
						Reject the Claim
					</label>
					<label class="{{{ ($errors->has('decision') or $errors->has('grant_different_amount')) ? 'state-error' : null }}}" style="display:inline;">
						{{ Form::radio('decision', PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage::GRANT_DIFF_AMOUNT) }}
						Grant different amount to the Contractor.
					</label>

					({{{ $ae->project->modified_currency_code }}}) {{ Form::text('grant_different_amount', Input::old('grant_different_amount')) }}

					{{ $errors->first('decision', '<br><em class="invalid">:message</em>') }}
					{{ $errors->first('grant_different_amount', '<br><em class="invalid">:message</em>') }}
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