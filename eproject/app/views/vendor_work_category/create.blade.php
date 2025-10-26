@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorWorkCategories.index', trans('contractGroupCategories.vendorWorkCategories')) }}</li>
        <li>@if(isset($vendorWorkCategory)) {{{ trans('forms.edit') }}} {{{ $vendorWorkCategory->name }}} @else {{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.vendorWorkCategories') }}} @endif</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorWorkCategories') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($vendorWorkCategory)) {{{ trans('forms.edit') }}} {{{ $vendorWorkCategory->name }}} @else {{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.vendorWorkCategories') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorWorkCategories.store'), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('contractGroupCategories.code') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('code') ? 'state-error' : null }}}">
                                    {{ Form::text('code', Input::old('code', (isset($vendorWorkCategory)) ? $vendorWorkCategory->code : ""), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('code', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-9 col-lg-9">
                                <label class="label">{{{ trans('contractGroupCategories.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name', (isset($vendorWorkCategory)) ? $vendorWorkCategory->name : ""), array('required' => 'required')) }}
                                </label>
                                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('contractGroupCategories.projectWorkCategories') }}} :</label>
                                <label class="input fill-horizontal {{{ $errors->has('work_category_id') ? 'state-error' : null }}}">
                                    {{ Form::select('work_category_id[]', $workCategories, Input::old('work_category_id') ?? $selectedWorkCategories, array('class' => 'select2 fill-horizontal', 'multiple' => 'multiple')) }}
                                </label>
                                {{ $errors->first('work_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($vendorWorkCategory)) ? $vendorWorkCategory->id : -1) }}
                            {{ link_to_route('vendorWorkCategories.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection