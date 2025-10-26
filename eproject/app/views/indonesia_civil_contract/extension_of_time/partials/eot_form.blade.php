<fieldset>
	<section>
		<label class="label">{{ trans('projects.project') }}:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">{{ trans('extensionOfTime.extensionOfTimeReference') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('reference') ? 'state-error' : null }}}">
			{{ Form::text('reference', Input::old('reference'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('reference', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('extensionOfTime.subject') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
			{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
	</section>

	@if($clause && (!$clause->items->isEmpty()))
		<section>
			<label class="label">{{ trans('extensionOfTime.clausesThatEmpower') }}:</label>
			<div style="height: 480px; overflow-y: scroll;">
				@foreach ( $clause->items as $item )
					<label>
						@if ( empty($selectedClauseIds) )
							{{ Form::checkbox('selected_clauses[]', $item->id) }}
						@else
							{{ Form::checkbox('selected_clauses[]', $item->id, in_array($item->id, $selectedClauseIds)) }}
						@endif

						@include('clause_items.partials.clause_item_description_formatter', ['item' => $item])
					</label>
					<br/>
					<br/>
				@endforeach
			</div>
			{{ $errors->first('selected_clauses', '<em class="invalid">:message</em>') }}
		</section>
	@endif

	<section>
		<label class="label">{{ trans('extensionOfTime.details') }}<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('details') ? 'state-error' : null }}}">
			{{ Form::textarea('details', Input::old('details'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('details', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('extensionOfTime.totalDays') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('days') ? 'state-error' : null }}}">
			{{ Form::number('days', Input::old('days'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('days', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('extensionOfTime.earlyWarnings') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('early_warnings') ? 'state-error' : null }}}">
			{{ Form::select('early_warnings[]', $warnings, Input::old('early_warnings') ?? $preSelectedWarnings ?? null, array('class' => 'select2', 'multiple' => true)) }}
		</label>
		{{ $errors->first('early_warnings', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('extensionOfTime.architectInstruction') }}:</label>
		<label class="input {{{ $errors->has('indonesia_civil_contract_ai_id') ? 'state-error' : null }}}">
			{{ Form::select('indonesia_civil_contract_ai_id', $ais, Input::old('indonesia_civil_contract_ai_id') ?? $preSelectedAI ?? null, array('class' => 'select2')) }}
		</label>
		{{ $errors->first('indonesia_civil_contract_ai_id', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('extensionOfTime.attachments') }}:</label>

		@include('file_uploads.partials.upload_file_modal')
	</section>
</fieldset>