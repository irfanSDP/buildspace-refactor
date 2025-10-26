@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.templateForms', trans('forms.templateForms'), array()) }}</li>
        <li>{{{ $templateForm->weightedNode->name }}}</li>
        <li>{{ trans('vendorManagement.gradeDefinitions') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-sm fa-address-book"></i> {{ trans('vendorManagement.gradeDefinitions') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('vendorManagement.gradeDefinitions') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <form action="{{ route('vendorPerformanceEvaluation.templateForms.updateGrade', [$templateForm->id]) }}" method="POST" class="smart-form">
                        <input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
                        <fieldset>					
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{ trans('vendorManagement.vendorManagementGrades') }}</label>
                                    <label class="select">
                                        <select name="grade_template">
                                            <option value="">{{ trans('general.selectAnOption') }}</option>
                                            @foreach($gradeTemplates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select> <i></i> </label>
                                        {{ $errors->first('grade_template', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-2">
                                    <label class="label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary"><i class="far fa-save fa-lg"></i> {{ trans('forms.save') }}</button>
                                </section>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection