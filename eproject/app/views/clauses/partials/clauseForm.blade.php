<header>
	{{ trans('clauses.clauseInformation') }}
</header>

<fieldset>
	<div class="row">
		<section class="col col-xs-12 col-md-6 col-lg-6">
			<label class="label">{{ trans('clauses.contractType') }} <span class="required">*</span>:</label>
			<label class="select">
				{{ Form::select('contract_id', $contractTypes, Input::old('contract_id'), array('class' => 'input-sm')) }}
				<i></i>
			</label>
		</section>
		<section class="col col-xs-12 col-md-6 col-lg-6">
			<label class="label">{{ trans('clauses.clauseName') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
				{{ Form::text('name', Input::old('name'), array('required' => 'required', 'placeholder' => trans('clauses.clauseName'))) }}
			</label>
			{{ $errors->first('name', '<em class="invalid">:message</em>') }}
		</section>
	</div>
</fieldset>