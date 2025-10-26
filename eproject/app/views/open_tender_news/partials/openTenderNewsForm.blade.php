<header>
	{{ trans('openTenderNews.news_detail') }}
</header>
<fieldset>
	<div class="row">
		<section class="col col-6">
			<label class="label">{{ trans('openTenderNews.department') }} <span class="required">*</span>:</label>
			{{ Form::select('department', PCK\OpenTenderNews\OpenTenderNews::subsidiariesList(),  isset($department) ? $department : Input::old('department'), array('class' => 'form-control padded-less-left')) }}
		</section>
	</div>
	<div class="row">
		<section class="col col-6">
			<label class="label">{{ trans('openTenderNews.start_time') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('start_time') ? 'state-error' : null }}}">
				<input type="datetime-local" name="start_time" value="{{ (isset($start_time)) ? $start_time : Input::old('start_time') }}">
			</label>
			{{ $errors->first('start_time', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-6">
			<label class="label">{{ trans('openTenderNews.end_time') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('end_time') ? 'state-error' : null }}}">
			<input type="datetime-local" name="end_time" value="{{ (isset($end_time)) ? $end_time : Input::old('end_time') }}">
			</label>
			{{ $errors->first('end_time', '<em class="invalid">:message</em>') }}
		</section>
	</div>
	<section>
		<label class="label">{{ trans('openTenderNews.description') }} <span class="required">*</span>:</label>
		{{ Form::textArea('description', isset($description) ? $description : Input::old('description'), array('class' => 'form-control padded-less-left')) }}
		{{ $errors->first('description', '<em class="invalid">:message</em>') }}
	</section>
	<section></section>
	<div class="row">
		<section class="col col-6">
			<label class="label">{{ trans('openTenderNews.status') }} <span class="required">*</span>:</label>
			{{ Form::select('status', PCK\OpenTenderNews\OpenTenderNews::status(),  isset($status) ? $status : Input::old('status'), array('class' => 'form-control')) }}
		</section>
	</div>
</fieldset>