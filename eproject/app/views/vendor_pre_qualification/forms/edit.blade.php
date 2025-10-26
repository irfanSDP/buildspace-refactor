@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorPreQualification.vendorPreQualification') }}}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.index', trans('vendorPreQualification.formLibrary'), array()) }}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.vendorWorkCategories.index', $vendorGroup->name, array($vendorGroup->id)) }}</li>
        <li>{{{ $form->weightedNode->name }}}</li>
        <li>{{{ trans('forms.update') }}}</li>
    </ol>
@endsection
weightedNode
@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-address-card"></i> {{{ trans('forms.update') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ $form->weightedNode->name }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::model($form->weightedNode, array('route' => array('vendorPreQualification.formLibrary.form.update', $vendorGroup->id, $vendorWorkCategory->id, $form->id), 'class' => 'smart-form')) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="label">{{{ trans('vendorPreQualification.name') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                        {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <footer>
                                {{ link_to_route('vendorPreQualification.formLibrary.vendorWorkCategories.index', trans('forms.back'), array($vendorGroup->id), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection