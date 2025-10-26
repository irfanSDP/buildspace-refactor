@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('consultant.management.maintenance.product.type.index', trans('general.productTypes')) }}</li>
        <li>@if(isset($productType)) {{{ trans('forms.edit') }}} {{{ $productType->title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.productType') }}} @endif</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-city"></i> {{{ trans('general.productTypes') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($productType)) {{{ trans('forms.edit') }}} {{{ $productType->title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.productType') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.maintenance.product.type.store'], 'class' => 'smart-form']) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-6">
                                <label class="label">{{{ trans('projects.title') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                    {{ Form::text('title', Input::old('title', (isset($productType)) ? $productType->title : ""), ['required' => 'required']) }}
                                </label>
                                {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-6">
                                <label class="label">{{{ trans('general.developmentTypes') }}} :</label>
                                <label class="input fill-horizontal {{{ $errors->has('development_type_id') ? 'state-error' : null }}}">
                                    {{ Form::select('development_type_id[]', $developmentTypes, Input::old('development_type_id') ?? $selectedDevelopmentTypes, ['class' => 'select2 fill-horizontal', 'multiple' => 'multiple']) }}
                                </label>
                                {{ $errors->first('development_type_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($productType)) ? $productType->id : -1) }}
                            {{ link_to_route('consultant.management.maintenance.product.type.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection