<header>
    {{ trans('projects.postContractInformation') }}
</header>

<fieldset>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <div class="well">
                <label class="label">{{ trans('projects.selectedContractor') }}:</label>
                <label class="input" id="contractor-name">
                    {{{ isset($contractor) ? mb_strtoupper($contractor->name) : null }}}
                </label>
            </div>
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-6 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.commencementDate') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('commencement_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('commencement_date', Input::old('commencement_date'), array('required' => 'required', 'class' => 'commencement_date')) }}
            </label>
            {{ $errors->first('commencement_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-6 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.completionDate') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('completion_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('completion_date', Input::old('completion_date'), array('required' => 'required', 'class' => 'completion_date')) }}
            </label>
            {{ $errors->first('completion_date', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.contractSum') }} :</label>
            <label class="input {{{ $errors->has('contract_sum') ? 'state-error' : null }}}">
                {{ Form::text('contract_sum', Input::old('contract_sum')) }}
            </label>
            {{ $errors->first('contract_sum', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">{{ trans('dailyLabourReports.trade') }} <span class="required">*</span>:</label>
            <select name="trade" id="trade" class="form-control" required>
                <option selected disabled="">Select</option>
                @foreach($trades as $trade)
                    @if(Input::old('trade') == $trade->id)
                        <option selected value="{{{ $trade->id }}}">
                            {{{ $trade->name }}}
                        </option>
                    @else
                        <option value="{{{ $trade->id }}}">
                            {{{ $trade->name }}}
                        </option>
                    @endif
                @endforeach
            </select>
            {{ $errors->first('trade', '<em class="invalid">:message</em>') }}
        </section>
    </div>

</fieldset>