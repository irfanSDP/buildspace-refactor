<div class="row">
    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <label class="label">{{{trans('general.title')}}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
            {{ Form::text('title', Input::old('title', ($loa) ? $loa->title : null), ['required'=>'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('title', '<em class="invalid">:message</em>') }}
    </section>
</div>