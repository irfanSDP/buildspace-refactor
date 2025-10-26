@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.formTemplateMapping') }}}</li>
        <li>{{ link_to_route('vendorPreQualification.formMapping', trans('vendorPreQualification.vendorPreQualification'), array()) }}</li>
        <li>{{{ $vendorCategory->name."-".$vendorWorkCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorPreQualification.updateSetup') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $vendorCategory->name."-".$vendorWorkCategory->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => 'vendorPreQualification.formMapping.update', 'class' => 'smart-form')) }}
                    @if (isset($setup->id))
                        {{ Form::hidden('setupId', $setup->id) }}
                    @else
                        {{ Form::hidden('vendorCategoryId', $vendorCategory->id) }}
                        {{ Form::hidden('vendorWorkCategoryId', $vendorWorkCategory->id) }}
                    @endif
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.vendorCategory') }}}</label>
                                <label class="input ">
                                    {{{ $vendorCategory->name }}}
                                </label>
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.vendorWorkCategory') }}}</label>
                                <label class="input ">
                                    {{{ $vendorWorkCategory->name }}}
                                </label>
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{ trans('vendorManagement.form') }}:</label>
                                <label class="input">{{{ $templateFormName }}}</label>
                            </section>
                        </div>
                        <hr/>
                        <br/>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="checkbox">
                                    {{ Form::checkbox('pre_qualification_required', 1, Input::old('pre_qualification_required')) }}

                                    <i></i>{{ trans('vendorManagement.vendorPreQualificationRequired') }}
                                </label>
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPreQualification.formMapping', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection