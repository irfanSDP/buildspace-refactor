@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        @if(!isset($attachmentSetting))
        <li>{{ link_to_route('consultant.management.attachment.settings.index', trans('general.attachmentSettings'), [$consultantManagementContract->id]) }}</li>
        <li>{{{ trans('forms.add') }}} {{{ trans('general.attachmentSettings') }}}</li>
        @else
        <li>{{ link_to_route('consultant.management.attachment.settings.show', $attachmentSetting->title, [$consultantManagementContract->id, $attachmentSetting->id]) }}</li>
        <li>{{{ trans('forms.edit') }}} {{{ trans('general.attachmentSettings') }}}</li>
        @endif
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-cogs"></i> {{{ trans('general.attachmentSettings') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($attachmentSetting)) {{{ trans('forms.edit') }}} {{{ $attachmentSetting->title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.attachmentSettings') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.attachment.settings.store', $consultantManagementContract->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-8 col-lg-8">
                            <label class="label">{{{ trans('general.title') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                {{ Form::text('title', Input::old('title', isset($attachmentSetting) ? $attachmentSetting->title : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-4 col-lg-4">
                            <?php $defaultMandatory = (isset($attachmentSetting) && !$attachmentSetting->mandatory) ? 0 : 1; ?>
                            <label class="label">Mandatory <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('mandatory') ? 'state-error' : null }}}">
                                <select class="select2 fill-horizontal" name="mandatory" id="mandatory-select">
                                    <option value="1" @if(1 == Input::old('mandatory', $defaultMandatory)) selected @endif>{{{ trans('general.yes') }}}</option>
                                    <option value="0" @if(0 == Input::old('mandatory', $defaultMandatory)) selected @endif>{{{ trans('general.no') }}}</option>

                                </select>
                            </label>
                            {{ $errors->first('mandatory', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', (isset($attachmentSetting)) ? $attachmentSetting->id : -1) }}
                        {{ Form::hidden('consultant_management_contract_id', $consultantManagementContract->id) }}
                        @if(!isset($attachmentSetting))
                        {{ link_to_route('consultant.management.attachment.settings.index', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
                        @else
                        {{ link_to_route('consultant.management.attachment.settings.show', trans('forms.back'), [$consultantManagementContract->id, $attachmentSetting->id], ['class' => 'btn btn-default']) }}
                        @endif
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection