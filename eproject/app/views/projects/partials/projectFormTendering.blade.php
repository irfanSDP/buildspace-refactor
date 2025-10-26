<header>
	{{ trans('tenders.tenderingInformation') }}
</header>

<fieldset>
	<div class="row">
		<section class="col col-xs-12 col-md-6 col-lg-6">
			<label class="label">Close Date <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('close_date') ? 'state-error' : null }}}">
				<i class="icon-append fa fa-calendar"></i>
				{{ Form::text('close_date', Input::old('close_date'), array('required' => 'required', 'id' => 'close_date')) }}
			</label>
			{{ $errors->first('close_date', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-xs-12 col-md-6 col-lg-6">
			<label class="label">Close Time <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('close_time') ? 'state-error' : null }}}">
				<i class="icon-append fa fa-clock-o"></i>
				{{ Form::text('close_time', Input::old('close_time'), array('required' => 'required', 'id' => 'close_time')) }}
			</label>
			{{ $errors->first('close_time', '<em class="invalid">:message</em>') }}
		</section>
	</div>
</fieldset>