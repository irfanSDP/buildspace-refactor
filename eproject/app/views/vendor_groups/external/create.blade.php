@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorGroups.external.index', trans('contractGroupCategories.externalVendorGroups')) }}</li>
        <li>@if(isset($contractGroupCategory)) {{{ trans('forms.edit') }}} {{{ $contractGroupCategory->name }}} @else {{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.externalVendorGroups') }}} @endif</li>
    </ol>
@endsection
<?php use PCK\ContractGroupCategory\ContractGroupCategory; ?>
@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.externalVendorGroups') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($contractGroupCategory)) {{{ trans('forms.edit') }}} {{{ $contractGroupCategory->name }}} @else {{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.externalVendorGroups') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorGroups.external.store'), 'class' => 'smart-form')) }}
                        <input type="hidden" name="vendor_type" value="{{ ContractGroupCategory::TYPE_EXTERNAL }}" />
                        <div class="row">
                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('contractGroupCategories.code') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('code') ? 'state-error' : null }}}">
                                    {{ Form::text('code', Input::old('code', (isset($contractGroupCategory)) ? $contractGroupCategory->code : null), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('code', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('contractGroupCategories.vendorType') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('code') ? 'state-error' : null }}}">
                                    {{ Form::select('vendor_type', $vendorTypes, (isset($contractGroupCategory) ? $contractGroupCategory->vendor_type : null), array('class' => 'select2')); }}
                                </label>
                                {{ $errors->first('vendor_type', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-9 col-lg-6">
                                <label class="label">{{{ trans('contractGroupCategories.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name', (isset($contractGroupCategory)) ? $contractGroupCategory->name : null), array('required' => 'required')) }}
                                </label>
                                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($contractGroupCategory)) ? $contractGroupCategory->id : -1) }}
                            {{ link_to_route('vendorGroups.external.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection