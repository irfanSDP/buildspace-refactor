<fieldset>
	<section>
		<label class="label">{{ trans('projects.project') }}:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">{{ trans('architectInstructions.architectInstructionReference') }}<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('reference') ? 'state-error' : null }}}">
			{{ Form::text('reference', Input::old('reference'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('reference', '<em class="invalid">:message</em>') }}
	</section>

	@if($clause && (!$clause->items->isEmpty()))
		<section>
			<label class="label">{{ trans('architectInstructions.clausesThatEmpower') }}:</label>
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
		</section>
	@endif

	<section>
		<label class="label">{{ trans('architectInstructions.architectsInstruction') }}<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('instruction') ? 'state-error' : null }}}">
			{{ Form::textarea('instruction', Input::old('instruction'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('instruction', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('architectInstructions.deadlineToComply') }}:</label>
		<label class="input {{{ $errors->has('deadline_to_comply') ? 'state-error' : null }}}">
			{{ Form::text('deadline_to_comply', Input::old('deadline_to_comply'), array('class' => 'finishdate')) }}
		</label>
		{{ $errors->first('deadline_to_comply', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('architectInstructions.requestsForInformation') }}:</label>
		<label class="input {{{ $errors->has('rfi') ? 'state-error' : null }}}">
			{{ Form::select('rfi[]', $requestsForInformation, Input::old('rfi') ?? $preSelectedRfi ?? null, array('class' => 'select2', 'multiple' => true)) }}
		</label>
		{{ $errors->first('rfi', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">{{ trans('architectInstructions.attachments') }} {{ trans('architectInstructions.attachmentsNote') }}:</label>

		@include('file_uploads.partials.upload_file_modal')
	</section>
	<section>
		@include('verifiers.select_verifiers')
	</section>
</fieldset>