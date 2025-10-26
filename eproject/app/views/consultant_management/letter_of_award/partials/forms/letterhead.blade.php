<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('letterOfAward.letterHead') }}} :</label>
        <label class="textarea {{{ $errors->has('letterhead') ? 'state-error' : null }}}">
            {{ Form::textarea('letterhead', Input::old('letterhead', isset($loa) ? $loa->letterhead : null), ['id'=>'letterhead-txt', 'autofocus' => 'autofocus', 'class'=>'summernote']) }}
        </label>
        {{ $errors->first('letterhead', '<em class="invalid">:message</em>') }}
    </section>
</div>