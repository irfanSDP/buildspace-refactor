<header>
    Completion Information
</header>

<fieldset>

    <div class="row">
        <section class="col col-xs-6 col-md-6 col-lg-6">
            <label class="label">Project Completion Date <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('completion_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('completion_date', Input::old('completion_date'), array('required' => 'required', 'class' => 'completion_date')) }}
            </label>
            {{ $errors->first('completion_date', '<em class="invalid">:message</em>') }}
        </section>
    </div>
</fieldset>