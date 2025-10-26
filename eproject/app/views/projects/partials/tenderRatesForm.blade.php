<section>
    <h1 class="txt-color-blueDark">{{ trans('tenders.tenderRatesFromBuildspace') }}</h1>
</section>

<section>
    <label class="label">{{ trans('tenders.tenderRates') }} <span class="required">*</span>:</label>
    <label class="input {{{ $errors->has('rates') ? 'state-error' : null }}}">
        {{ Form::file('rates', array('accept' => '.tr', 'style' => 'height:100%')) }}
    </label>
    {{ $errors->first('rates', '<em class="invalid">:message</em>') }}
</section>