<header>
	{{ trans('clauses.itemInformation') }}
</header>

<fieldset>
	<div class="row">
		<section class="col col-sm-12">
			<label class="label">{{ trans('clauses.no') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('no') ? 'state-error' : null }}}">
				{{ Form::text('no', Input::old('no'), array('required' => 'required', 'maxlength' => 25)) }}
			</label>
			{{ $errors->first('no', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-sm-12">
			<label class="label">{{ trans('clauses.description') }} <span class="required">*</span>:</label>
			<label class="textarea {{{ $errors->has('description') ? 'state-error' : null }}}">
				{{ Form::textarea('description', Input::old('description'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('description', '<em class="invalid">:message</em>') }}
		</section>
	</div>
</fieldset>