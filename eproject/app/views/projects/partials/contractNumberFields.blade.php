<?php ?>
<div class="row">
    <section class="col col-xs-3 col-md-3 col-lg-3">
        <label class="label">{{{ trans('projects.contractType') }}} <span class="required">*</span>:</label>
        @if(isset($labelsOnly['contract']))
            {{{ $labelsOnly['contract'] }}}
        @else
            <label class="fill-horizontal">
                {{ Form::select('contract_id', $contractTypes, Input::old('contract_id'), array('class' => 'input-sm select2 fill-horizontal')) }}
                <i></i>
            </label>
            {{ $errors->first('contract_id', '<em class="invalid">:message</em>') }}
        @endif
    </section>
    <section class="col col-xs-5 col-md-5 col-lg-5">
        <label class="label">
            {{{ trans('projects.contractNumber') }}} <span class="required">*</span>:
            <span class="reference-view" style="font-family: monospace">
                @{{ reference }}
            </span>
            <span class="text-danger" id="referenceUsageLabel" style="display: none;">
                ({{ trans('projects.referenceInUse') }})
            </span>
        </label>
        <label class="fill horizontal input {{{ $errors->has('reference') ? 'state-error' : null }}}" data-for="reference">
            {{ Form::text('reference', null, array('placeholder' => trans('projects.contractNumber'), 'v-model' => 'reference', 'data-type' => 'reference')) }}
        </label>
        {{ $errors->first('reference', '<em class="color-bootstrap-danger">:message</em>') }}
    </section>
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <label class="label">{{{ trans('projects.workCategory') }}} <span class="required">*</span>:</label>
        <label class="fill-horizontal {{{ $errors->has('work_category_id') ? 'state-error' : null }}}">
            {{ Form::select('work_category_id', $workCategories, Input::old('work_category_id'), ['id' => 'work_category_select', 'class' => 'select2 fill-horizontal', 'data-on-change' => 'generateReference'])}}
        </label>
        {{ $errors->first('work_category_id', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-5">
        @if(isset($labelsOnly['subsidiary']))
            <label class="label">{{{ trans('projects.subsidiary') }}}:</label>
            {{{ $labelsOnly['subsidiary'] }}}
        @else
            <label class="label">{{{ trans('projects.subsidiary') }}} <span class="required">*</span>:
                @if(isset($subsidiaries) && empty($subsidiaries))
                    <a href="{{ route('subsidiaries.index') }}" class="pull-right">{{{ trans('subsidiaries.createNew') }}}</a>
                @endif
            </label>
            @if(isset($subsidiaries) && (!empty($subsidiaries)))
                <label class="fill-horizontal {{{ $errors->has('subsidiary_id') ? 'state-error' : null }}}">
                    <select id="subsidiary_select" class="select2 fill-horizontal" data-type="uniqueIdentifier" data-on-change="generateReference" name="subsidiary_id">
                        <option value="-1">{{ trans('forms.select') }}</option>
                        @foreach ($subsidiaries as $subsidiaryId => $subsidiaryName)
                            <option value="{{{ $subsidiaryId }}}">
                                {{{ $subsidiaryName }}}
                            </option>
                        @endforeach
                    </select>
                </label>
                {{ $errors->first('subsidiary_id', '<em class="invalid">:message</em>') }}
            @endif
        @endif
    </section>

    <section class="col col-2">
        <label class="label">{{{ trans('projects.year') }}} <span class="required">*</span>:</label>
        <label class="fill-horizontal input {{{ $errors->has('reference') ? 'state-error' : null }}}">
            {{ Form::text('reference_suffix', null, array('id' => 'reference_suffix', 'placeholder' => trans('projects.contractNumberSuffix'), 'v-model' => 'referenceSuffix', 'data-type' => 'uniqueIdentifier', 'data-on-change' => 'generateReference')) }}
        </label>
    </section>

    <section class="col col-2">
        <label class="label">{{{ trans('projects.runningNumber') }}} <span class="required">*</span>:</label>
        <label class="fill-horizontal input {{{ $errors->has('reference') || $errors->has('running_number') ? 'state-error' : null }}}">
            {{ Form::number('running_number', null, array('placeholder' => trans('projects.runningNumber'), 'v-model' => 'runningNumber', 'data-type' => 'uniqueIdentifier', 'data-on-change' => 'generateReference', 'min' => 1)) }}
            {{ $errors->first('running_number', '<em class="color-bootstrap-danger">:message</em>') }}
            <em data-error="runningNumberExists" class="color-bootstrap-danger" hidden>{{ trans('projects.runningNumberExists') }}</em>
            <em data-valid="runningNumberValid" class="color-bootstrap-success" hidden>{{ trans('projects.runningNumberValid') }}</em>
        </label>
    </section>

    <section class="col col-2">
        <label class="label">&nbsp;</label>
        <button type="button" class="form-control btn btn-warning" v-on="click:generateRunningNumber">
            <i class="fa fa-magic"></i> {{ trans('projects.generate') }}
        </button>
    </section>

    <section class="col col-1">
        <label class="label">&nbsp;</label>
        <button type="button" class="form-control btn btn-info" v-on="click:validateRunningNumber">
            {{ trans('projects.check') }}
        </button>
    </section>

</div>