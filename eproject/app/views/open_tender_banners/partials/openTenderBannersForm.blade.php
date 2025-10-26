<header>
	{{ trans('openTenderBanners.banner_detail') }}
</header>
<?php
    $imageAttributes  = array(
        'path' => '',
        'title' => 'image',
        'border' => 'dashed',
        'width' => '200px',
        'height' => '200px'
    );

    if (!empty($image) && file_exists(public_path('upload/banner/'.$id.'/'.$image))) {
		$imageAttributes['path'] = asset('upload/banner/'.$id.'/'.$image);
		$imageAttributes['border'] = 'dotted';
    }

?>
<fieldset>
	<div class="row">
		<section class="col col-4">
			<label class="label">{{ trans('openTenderBanners.display_order') }} <span class="required">*</span>:</label>
			{{ Form::select('display_order', PCK\OpenTenderBanners\OpenTenderBanners::display_order(),  isset($display_order) ? $display_order : Input::old('display_order'), array('class' => 'form-control')) }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('openTenderBanners.start_time') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('start_time') ? 'state-error' : null }}}">
				<input type="datetime-local" name="start_time" value="{{ (isset($start_time)) ? $start_time : Input::old('start_time') }}">
			</label>
			{{ $errors->first('start_time', '<em class="invalid">:message</em>') }}
		</section>
		<section class="col col-4">
			<label class="label">{{ trans('openTenderBanners.end_time') }} <span class="required">*</span>:</label>
			<label class="input {{{ $errors->has('end_time') ? 'state-error' : null }}}">
				<input type="datetime-local" name="end_time" value="{{ (isset($end_time)) ? $end_time : Input::old('end_time') }}">
			</label>
			{{ $errors->first('end_time', '<em class="invalid">:message</em>') }}
		</section>
	</div>
	<section>
		<label class="label">{{ trans('openTenderBanners.image') }} <span class="required">*</span>:</label>
		@if  ($imageAttributes['path'] != null )
			<div style="display: flex; justify-content: center; width: 400px; height: 200px; margin-bottom: 15px; border: 1px {{{ $imageAttributes['border'] }}} black;">
				<img style="max-width: 100%; height: auto; object-fit: contain;" src="{{ $imageAttributes['path'] != null ? $imageAttributes['path'].'?v='.time() : '' }}" class="logo">
			</div>
		@endif
		<label class="input {{{ $errors->has('image') ? 'state-error' : null }}}">
			<input type="file" name="image"  accept="image/png">
		</label>
		<small class="text-muted">Recommended size: 1200x400px</small>
		{{ $errors->first('image', '<em class="invalid">:message</em>') }}
	</section>
</fieldset>