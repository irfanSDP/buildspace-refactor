<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('letterOfAward.signatory') }}} :</label>
        <label class="textarea {{{ $errors->has('signatory') ? 'state-error' : null }}}">
            {{ Form::textarea('signatory', Input::old('signatory', isset($loa) ? $loa->signatory : null), ['id'=>'signatory-txt', 'autofocus' => 'autofocus', 'class'=>'summernote']) }}
        </label>
        {{ $errors->first('signatory', '<em class="invalid">:message</em>') }}
    </section>
</div>