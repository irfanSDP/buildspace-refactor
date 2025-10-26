@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorGroups.external.index', trans('contractGroupCategories.externalVendorGroups'), array()) }}</li>
        <li>{{ link_to_route('vendorCategories.index', $contractGroupCategory->name, array($contractGroupCategory->id)) }}</li>
        <li>{{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.vendorCategories') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorCategories') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('forms.add') }}} {{{ trans('contractGroupCategories.vendorCategories') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorCategories.store', $contractGroupCategory->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('contractGroupCategories.code') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('code') ? 'state-error' : null }}}">
                                    {{ Form::text('code', Input::old('code'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('code', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-9 col-lg-9">
                                <label class="label">{{{ trans('contractGroupCategories.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name'), array('required' => 'required')) }}
                                </label>
                                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('contractGroupCategories.target') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('target') ? 'state-error' : null }}}">
                                    {{ Form::number('target', Input::old('target', 0), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('target', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', -1) }}
                            {{ Form::hidden('contract_group_category_id', $contractGroupCategory->id) }}
                            {{ link_to_route('vendorCategories.index', trans('forms.back'), array($contractGroupCategory->id), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection