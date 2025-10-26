@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorWorkCategories.index', trans('contractGroupCategories.vendorWorkCategories'), array()) }}</li>
        <li>{{ link_to_route('vendorWorkSubcategories.index', $vendorWorkCategory->name, array($vendorWorkCategory->id)) }}</li>
        <li>@if(isset($vendorWorkSubcategory)) {{{ trans('forms.edit') }}} {{{ $vendorWorkSubcategory->name }}} @else {{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.vendorWorkSubcategories') }}} @endif</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorWorkSubcategories') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($vendorWorkSubcategory)) {{{ trans('forms.edit') }}} {{{ $vendorWorkSubcategory->name }}} @else {{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.vendorWorkSubcategories') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorWorkSubcategories.store', $vendorWorkCategory->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('contractGroupCategories.code') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('code') ? 'state-error' : null }}}">
                                    {{ Form::text('code', Input::old('code', (isset($vendorWorkSubcategory)) ? $vendorWorkSubcategory->code : ""), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('code', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-9 col-lg-9">
                                <label class="label">{{{ trans('contractGroupCategories.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name', (isset($vendorWorkSubcategory)) ? $vendorWorkSubcategory->name : ""), array('required' => 'required')) }}
                                </label>
                                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('vendor_work_category_id',  $vendorWorkCategory->id) }}
                            {{ Form::hidden('id', (isset($vendorWorkSubcategory)) ? $vendorWorkSubcategory->id : -1) }}
                            {{ link_to_route('vendorWorkSubcategories.index', trans('forms.back'), array($vendorWorkCategory->id), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection