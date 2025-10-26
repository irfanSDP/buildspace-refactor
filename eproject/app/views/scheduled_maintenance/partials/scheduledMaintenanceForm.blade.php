<header>
	{{ trans('scheduledMaintenance.scheduled_maintenance_info') }}
</header>
<?php
    $imageAttributes  = array(
        'path' => '',
        'title' => 'image',
        'border' => 'dashed',
        'width' => '200px',
        'height' => '200px'
    );

    if (!empty($image) && file_exists(public_path('upload/maintenance/'.$id.'/'.$image))) {
		$imageAttributes['path'] = asset('upload/maintenance/'.$id.'/'.$image);
		$imageAttributes['border'] = 'dotted';
    }
	else if (empty($image) && file_exists(public_path('upload/maintenance/0/maintenance.png')))
	{
		$imageAttributes['path'] = asset('upload/maintenance/0/maintenance.png');
		$imageAttributes['border'] = 'dotted';	
	}

    ?>
<fieldset>
	<div class="row">
		<section class="col col-4">
			<label class="label">{{ trans('scheduledMaintenance.start_time') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('start_time') ? 'state-error' : null }}}">
				<input type="datetime-local" name="start_time" value="{{ (isset($start_time)) ? $start_time : Input::old('start_time') }}">
			</label>
			{{ $errors->first('start_time', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('scheduledMaintenance.end_time') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('end_time') ? 'state-error' : null }}}">
			<input type="datetime-local" name="end_time" value="{{ (isset($end_time)) ? $end_time : Input::old('end_time') }}">
			</label>
			{{ $errors->first('end_time', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('scheduledMaintenance.status') }} <span class="required">*</span>:</label>
			<label class="checkbox">
				<input type="checkbox" name="status" value="{{ (isset($status)) ? '1' : '0' }}" {{ isset($status) && $status ? 'checked' : '' }}>
				<i></i>{{ trans('scheduledMaintenance.active') }}
			</label>
		</section>
	</div>
	<section>
		<label class="label">{{ trans('scheduledMaintenance.message') }} <span class="required">*</span>:</label>
		<label for="country" class="input {{{ $errors->has('message') ? 'state-error' : null }}}">
			<input type="text" name="message" value="{{ (isset($message)) ? $message : Input::old('message') }}">
		</label>
		{{ $errors->first('message', '<em class="invalid">:message</em>') }}
	</section>
	<section>
		<label class="label">{{ trans('scheduledMaintenance.image') }} <span class="required">*</span>:</label>
		<div style="display: flex; justify-content: center; width: 400px; height: 200px; margin-bottom: 15px; border: 1px {{{ $imageAttributes['border'] }}} black;">
			<img style="max-width: 100%; height: auto; object-fit: contain;" src="{{ $imageAttributes['path'] != null ? $imageAttributes['path'].'?v='.time() : '' }}" class="logo">
		</div>
		<label class="input {{{ $errors->has('image') ? 'state-error' : null }}}">
			<input type="file" name="image"  accept="image/png">
		</label>
		{{ $errors->first('image', '<em class="invalid">:message</em>') }}
	</section>
</fieldset>