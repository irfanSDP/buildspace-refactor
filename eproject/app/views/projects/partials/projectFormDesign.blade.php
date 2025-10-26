<header>
    {{{ trans('projects.businessUnitInfo') }}}
</header>

<fieldset>
    <div class="row">
        <section class="col col-xs-9 col-md-9 col-lg-9">
            <label class="label">{{{ trans('projects.businessUnitName') }}} :</label>
            {{{ $user->company->name }}}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('projects.projectCreatorName') }}} :</label>
            {{{ $user->name }}}
        </section>
    </div>
</fieldset>

<header>
    {{{ trans('projects.projectInfo') }}}
</header>

<fieldset>
    @include('projects.partials.contractNumberFields')
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('projects.projectTitle') }}} <span class="required">*</span>:</label>
            <label class="textarea {{{ $errors->has('title') ? 'state-error' : null }}}">
                {{ Form::textarea('title', Input::old('title'), array('required' => 'required', 'rows' => '1', 'autofocus' => 'autofocus')) }}
            </label>
            {{ $errors->first('title', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('projects.siteAddress') }}} <span class="required">*</span>:</label>
            <label class="textarea {{{ $errors->has('address') ? 'state-error' : null }}}">
                {{ Form::textarea('address', Input::old('address'), array('required' => 'required', 'rows' => 3)) }}
            </label>
            {{ $errors->first('address', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">{{{ trans('projects.country') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal {{{ $errors->has('country_id') ? 'state-error' : null }}}">
                <select class="select2 fill-horizontal" name="country_id" id="country"></select>
            </label>
            {{ $errors->first('country_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">{{{ trans('projects.state') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal {{{ $errors->has('state_id') ? 'state-error' : null }}}">
                <select class="select2 fill-horizontal" name="state_id" id="state">
                    <option value="" disabled>{{{ trans('companies.selectState') }}}</option>
                </select>
            </label>
            {{ $errors->first('state_id', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('projects.projectDescription') }}} <span class="required">*</span>:</label>
            <label class="textarea {{{ $errors->has('description') ? 'state-error' : null }}}">
                {{ Form::textarea('description', Input::old('description'), array('required' => 'required', 'rows' => 3)) }}
            </label>
            {{ $errors->first('description', '<em class="invalid">:message</em>') }}
        </section>
    </div>
</fieldset>

<header>
    {{{ trans('projects.defaultTemplates') }}}
</header>

<fieldset>
    <div class="row">
        <section class="col col-xs-11 col-md-5 col-lg-5">
            <label class="label">{{{ trans('letterOfAward.letterOfAward') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal">
                <select id="letterOfAwardTemplateSelect" class="select2 fill-horizontal" name="letter_of_award_template_id" id="letter_of_award_template_select">
                    @foreach ($letterOfAwardTemplates as $template)
                        <option value="{{{ $template['id'] }}}" data-print_route="{{{ $template['printRoute'] }}}">{{{ $template['name'] }}}</option>
                    @endforeach
                </select>
            </label>
            {{ $errors->first('letter_of_award_template_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-1 col-md-1 col-lg-1">
            <label class="label">&nbsp;</label>
            <button id="btnPreviewLetterOfAwardTemplatePDF" type="button" class="form-control btn btn-primary"><i class="fa fa-search"></i> {{ trans('general.preview') }}</button>
        </section>

        <section class="col col-xs-11 col-md-5 col-lg-5">
            <label class="label">{{{ trans('formOfTender.formOfTender') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal">
                <select id="formOfTenderTemplateSelect" class="select2 fill-horizontal" name="form_of_tender_template_id" id="letter_of_award_template_select">
                    @foreach ($formOfTenderTemplates as $template)
                        <option value="{{{ $template['id'] }}}" data-print_route="{{{ $template['printRoute'] }}}">{{{ $template['name'] }}}</option>
                    @endforeach
                </select>
            </label>
            {{ $errors->first('form_of_tender_template_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-1 col-md-1 col-lg-1">
            <label class="label">&nbsp;</label>
            <button id="btnPreviewFormOfTenderTemplatePDF" type="button" class="form-control btn btn-primary"><i class="fa fa-search"></i> {{ trans('general.preview') }}</button>
        </section>
    </div>
</fieldset>