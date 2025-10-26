<header>
	{{ trans('states.stateInformation') }}
</header>

<fieldset>
	<div class="row">
		<section class="col col-8">
			<label class="label">{{ trans('states.name') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
				{{ Form::text('name', Input::old('name'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('name', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('states.timezone') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('timezone') ? 'state-error' : null }}}">
				{{ Form::text('timezone', Input::old('timezone'), array('required' => 'required')) }}
			</label>
				{{ $errors->first('timezone', '<em class="invalid">:message</em>') }}
		</section>
	</div>
</fieldset>