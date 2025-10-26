<header>
	{{ trans('countries.countryInfo') }}
</header>

<fieldset>
	<section>
		<label class="label">{{ trans('countries.countryName') }} <span class="required">*</span>:</label>
		<label for="country" class="input {{{ $errors->has('country') ? 'state-error' : null }}}">
			{{ Form::text('country', Input::old('country'), array('required' => 'required')) }}
		</label>
		{{ $errors->first('country', '<em class="invalid">:message</em>') }}
	</section>
	<div class="row">
		<section class="col col-4">
			<label class="label">{{ trans('countries.iso') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('iso') ? 'state-error' : null }}}">
				{{ Form::text('iso', Input::old('iso'), array('required' => 'required', 'maxlength' => 3)) }}
			</label>
			{{ $errors->first('iso', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('countries.iso3') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('iso3') ? 'state-error' : null }}}">
				{{ Form::text('iso3', Input::old('iso3'), array('required' => 'required', 'maxlength' => 3)) }}
			</label>
			{{ $errors->first('iso3', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('countries.fips') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('fips') ? 'state-error' : null }}}">
				{{ Form::text('fips', Input::old('fips'), array('required' => 'required', 'maxlength' => 3)) }}
			</label>
			{{ $errors->first('fips', '<em class="invalid">:message</em>') }}
		</section>
	</div>
	<div class="row">
		<section class="col col-3">
			<label class="label">{{ trans('countries.phonePrefix') }}<span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('phone_prefix') ? 'state-error' : null }}}">
				{{ Form::text('phone_prefix', Input::old('phone_prefix'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('phone_prefix', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-6">
			<label class="label">{{ trans('countries.postalCode') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('postal_code') ? 'state-error' : null }}}">
				{{ Form::text('postal_code', Input::old('postal_code'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('postal_code', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-3">
			<label class="label">{{ trans('countries.continent') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('continent') ? 'state-error' : null }}}">
				{{ Form::text('continent', Input::old('continent'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('continent', '<em class="invalid">:message</em>') }}
		</section>
	</div>
	<div class="row">
		<section class="col col-6">
			<label class="label">{{ trans('countries.languages') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('languages') ? 'state-error' : null }}}">
				{{ Form::text('languages', Input::old('languages'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('languages', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-6">
			<label class="label">{{ trans('countries.geonameId') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('geonameid') ? 'state-error' : null }}}">
				{{ Form::text('geonameid', Input::old('geonameid'), array('required' => 'required', 'maxlength' => 10)) }}
			</label>
			{{ $errors->first('geonameid', '<em class="invalid">:message</em>') }}
		</section>
	</div>
</fieldset>

<header>
	{{ trans('currencies.currency') }}
</header>

<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">{{ trans('currencies.currencyCode') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('currency_code') ? 'state-error' : null }}}">
				{{ Form::text('currency_code', Input::old('currency_code'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('currency_code', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-6">
			<label class="label">{{ trans('currencies.currencyName') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('currency_name') ? 'state-error' : null }}}">
				{{ Form::text('currency_name', Input::old('currency_name'), array('required' => 'required')) }}
			</label>
			{{ $errors->first('currency_name', '<em class="invalid">:message</em>') }}
		</section>
	</div>
	<div class="row">
		<section class="col col-6">
			<label class="label">{{ trans('currencies.roundingOptions') }}</label>
			<label class="select">
				<select name="rounding_type">
					@foreach (PCK\Countries\CurrencySetting::getRoundingTypeText() as $typeId => $typeDescription)
						@if (isset($country))
							<?php $selected = ($typeId == $country->currencySetting->rounding_type) ? 'selected' : ''; ?>
						@else
							<?php $selected = ($typeId == PCK\Countries\CurrencySetting::ROUNDING_TYPE_DISABLED) ? 'selected' : ''; ?>
						@endif
						<option value="{{{ $typeId }}}" {{{ $selected }}}>{{{ $typeDescription }}}</option>
					@endforeach
				</select> <i></i> 
			</label>
		</section>
	</div>
</fieldset>