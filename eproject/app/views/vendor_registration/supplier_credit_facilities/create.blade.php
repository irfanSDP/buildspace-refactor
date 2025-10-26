@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.supplierCreditFacilities', trans('vendorManagement.supplierCreditFacilities'), array()) }}</li>
        <li>{{{ trans('vendorPreQualification.addItem') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorPreQualification.addItem') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorPreQualification.addItem') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::open(array('route' => array('vendors.vendorRegistration.supplierCreditFacilities.store'), 'class' => 'smart-form')) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label class="label">{{{ trans('vendorManagement.supplierName') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('supplier_name') ? 'state-error' : null }}}">
                                        {{ Form::text('supplier_name', Input::old('supplier_name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    {{ $errors->first('supplier_name', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label class="label">{{{ trans('vendorManagement.creditFacilities') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('credit_facilities') ? 'state-error' : null }}}">
                                        {{ Form::text('credit_facilities', Input::old('credit_facilities'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    {{ $errors->first('credit_facilities', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            @if($setting->has_attachments)
                            <section>
                                <label class="label">{{{ trans('forms.attachments') }}}:</label>

                                @include('file_uploads.partials.upload_file_modal')
                            </section>
                            @endif
                            <footer>
                                {{ link_to_route('vendors.vendorRegistration.supplierCreditFacilities', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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