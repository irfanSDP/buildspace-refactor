<fieldset>
	<section>
		<label class="label">Project Title:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">AI Reference<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('reference') ? 'state-error' : null }}}">
			{{ Form::text('reference', Input::old('reference'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('reference', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Clause(s) that empower the issuance of AI:</label>
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

	<section>
		<label class="label">Architect's Instruction<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('instruction') ? 'state-error' : null }}}">
			{{ Form::textarea('instruction', Input::old('instruction'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('instruction', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Deadline to Comply: <i>(Minimum {{{ $project->pam2006Detail->min_days_to_comply_with_ai }}} days to comply with AI)</i></label>
		<label class="input {{{ $errors->has('deadline_to_comply') ? 'state-error' : null }}}">
			{{ Form::text('deadline_to_comply', Input::old('deadline_to_comply'), array('class' => 'finishdate')) }}
		</label>
		{{ $errors->first('deadline_to_comply', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Attachment(s) (note: You may want to scan your printed AI and attach it here):</label>

		@include('file_uploads.partials.upload_file_modal')
	</section>
</fieldset>