<fieldset>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('general.name') }}} <span class="required">*</span>:</label>
            <label class="input textarea {{{ $errors->has('name') ? 'state-error' : null }}}">
                {{ Form::text('name', Input::old('name'), array('required' => 'required', 'class' => 'form-control padded-less-left', 'autofocus' => 'autofocus')) }}
            </label>
            {{ $errors->first('name', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-6 col-md-6 col-lg-6">
            <label class="label">{{{ trans('costData.masterCostData') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal {{{ $errors->has('master_cost_data_id') ? 'state-error' : null }}}">
                {{ Form::select('master_cost_data_id', $masterCostDataRecords, Input::old('master_cost_data_id'), ['class' => 'select2 fill-horizontal'])}}
            </label>
            {{ $errors->first('master_cost_data_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('costData.type') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal {{{ $errors->has('cost_data_type_id') ? 'state-error' : null }}}">
                {{ Form::select('cost_data_type_id', $types, Input::old('cost_data_type_id'), ['class' => 'select2 fill-horizontal'])}}
            </label>
            {{ $errors->first('cost_data_type_id', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-4 col-lg-4">
            <label class="label">{{{ trans('projects.country') }}}:</label>
            <label class="input {{{ $errors->has('region_id') ? 'state-error' : null }}}">
                <select class="select2 fill-horizontal" name="region_id" data-form-id="region_input"></select>
            </label>
            {{ $errors->first('region_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-4 col-lg-4">
            <label class="label">{{{ trans('projects.state') }}}:</label>
            <label class="input {{{ $errors->has('subregion_id') ? 'state-error' : null }}}">
                <select class="select2 fill-horizontal" name="subregion_id" data-form-id="subregion_input" data-type="dependentSelection" data-dependent-id="second"></select>
            </label>
            {{ $errors->first('subregion_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-4 col-lg-4">
            <label class="label">{{{ trans('currencies.currency') }}}:</label>
            <label class="input {{{ $errors->has('currency_id') ? 'state-error' : null }}}">
                {{ Form::select('currency_id', $currencies, null, ['class' => 'select2'])}}
            </label>
            {{ $errors->first('currency_id', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-4 col-lg-4">
            <label class="label">{{{ trans('costData.tenderYear') }}}:</label>
            <label class="input {{{ $errors->has('tender_date') ? 'state-error' : null }}}">
                {{ Form::number('tender_date', Input::old('tender_date', (isset($costData) && !is_null($costData->tender_date)) ? \Carbon\Carbon::parse($costData->tender_date)->year : null), ['placeholder' => 'yyyy', 'min' => 1900, 'max' => 2099, 'step' => 1]) }}
            </label>
            {{ $errors->first('tender_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-4 col-lg-4">
            <label class="label">{{{ trans('costData.awardYear') }}}:</label>
            <label class="input {{{ $errors->has('award_date') ? 'state-error' : null }}}">
                {{ Form::number('award_date', Input::old('award_date', (isset($costData) && !is_null($costData->award_date)) ? \Carbon\Carbon::parse($costData->award_date)->year : null), ['placeholder' => 'yyyy', 'min' => 1900, 'max' => 2099, 'step' => 1]) }}
            </label>
            {{ $errors->first('award_date', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('subsidiaries.subsidiary') }}} <span class="required">*</span>:</label>
            <label class="fill-horizontal {{{ $errors->has('subsidiary_id') ? 'state-error' : null }}}">
                <select name="subsidiary_id" id="subsidiary_id" class ="fill-horizontal select2" required>
                    <?php
                        $selectedSubsidiaryId = Input::old('subsidiary_id', isset($costData) ? $costData->subsidiary_id : null);
                    ?>
                    <option value="" @if($selectedSubsidiaryId === null) selected @endif>{{ trans('forms.none') }}</option>
                    @foreach ($subsidiaries as $subsidiaryId => $subsidiaryName)
                        <option value="{{{ $subsidiaryId }}}" @if($subsidiaryId == $selectedSubsidiaryId) selected @endif>{{{ $subsidiaryName }}}</option>
                    @endforeach
                </select>
            </label>
            {{ $errors->first('subsidiary_id', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('projects.projects') }}}:</label>
            <label class="fill-horizontal {{{ $errors->has('project_id') ? 'state-error' : null }}}">
                <div id="project-list-table"></div>
            </label>
            {{ $errors->first('project_id', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <button type="button" class="btn btn-warning pull-right" data-action="list-project-options">{{ trans('costData.addProjects') }}</section>
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('costData.notes') }}}:</label>
            <label class="fill-horizontal {{{ $errors->has('notes') ? 'state-error' : null }}}">
                <div class="well">
                    <div class="summernote" data-input="notes">
                        <?php $oldInput = Input::old('notes') ?? null; ?>
                        @if($oldInput)
                            {{ $oldInput }}
                        @elseif(isset($costData))
                            {{ $costData->getEProjectCostData()->notes }}
                        @else
                            <div style="text-align: justify;"><br></div>
                        @endif
                    </div>
                </div>
            </label>
            {{ $errors->first('notes', '<em class="invalid">:message</em>') }}
        </section>
    </div>
</fieldset>