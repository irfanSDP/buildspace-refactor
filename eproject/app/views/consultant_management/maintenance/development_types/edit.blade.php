@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('consultant.management.maintenance.development.type.index', trans('general.developmentTypes')) }}</li>
        <li>@if(isset($developmentType)) {{{ trans('forms.edit') }}} {{{ $developmentType->title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.developmentType') }}} @endif</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-city"></i> {{{ trans('general.developmentTypes') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($developmentType)) {{{ trans('forms.edit') }}} {{{ $developmentType->title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.developmentType') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.maintenance.development.type.store'], 'class' => 'smart-form']) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-6">
                                <label class="label">{{{ trans('projects.title') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                    {{ Form::text('title', Input::old('title', (isset($developmentType)) ? $developmentType->title : ""), ['required' => 'required']) }}
                                </label>
                                {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-6">
                                <label class="label">{{{ trans('general.productTypes') }}} <span class="required">*</span>:</label>
                                <label class="input fill-horizontal {{{ $errors->has('product_type_id') ? 'state-error' : null }}}">
                                    {{ Form::select('product_type_id[]', $productTypes, Input::old('product_type_id') ?? $selectedProductTypes, ['class' => 'select2 fill-horizontal', 'multiple' => 'multiple']) }}
                                </label>
                                {{ $errors->first('product_type_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($developmentType)) ? $developmentType->id : -1) }}
                            {{ link_to_route('consultant.management.maintenance.development.type.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection