@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.templateForms', trans('forms.templateForms'), array()) }}</li>
        <li>{{{ $templateForm->weightedNode->name }}}</li>
        <li>{{{ trans('forms.edit') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-address-card"></i> {{{ trans('forms.edit') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('forms.edit') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::model($templateForm, array('route' => array('vendorPerformanceEvaluation.templateForms.update', $templateForm->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.vendorGroup') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('contract_group_category_id') ? 'state-error' : null }}}">
                                    {{ Form::select('contract_group_category_id', $vendorGroupOptions, Input::old('contract_group_category_id'), ['class' => 'select2 fill-horizontal'])}}
                                </label>
                                {{ $errors->first('contract_group_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.projectStage') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('project_status_id') ? 'state-error' : null }}}">
                                    {{ Form::select('project_status_id', $projectStatuses, Input::old('project_status_id'), ['class' => 'select2 fill-horizontal'])}}
                                </label>
                                {{ $errors->first('project_status_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.templateForms', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection