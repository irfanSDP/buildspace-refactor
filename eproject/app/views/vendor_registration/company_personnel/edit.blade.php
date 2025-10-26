@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.companyPersonnel', trans('vendorManagement.companyPersonnel'), array()) }}</li>
        <li>{{{ trans('vendorPreQualification.updateItem') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorPreQualification.updateItem') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorPreQualification.updateItem') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::model($companyPersonnel, array('route' => array('vendors.vendorRegistration.companyPersonnel.update', $companyPersonnel->id), 'class' => 'smart-form')) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.name') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                        {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.identificationNumber') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('identification_number') ? 'state-error' : null }}}">
                                        {{ Form::text('identification_number', Input::old('identification_number'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    {{ $errors->first('identification_number', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.type') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('type') ? 'state-error' : null }}}">
                                        {{ Form::select('type', $typeOptions, Input::old('type'), array('class' => 'form-control')) }}
                                    </label>
                                    {{ $errors->first('type', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <div class="row" id="default-section">
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.email') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('email_address') ? 'state-error' : null }}}">
                                        {{ Form::text('email_address', Input::old('email_address'), array()) }}
                                    </label>
                                    {{ $errors->first('email_address', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.contactNumber') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('contact_number') ? 'state-error' : null }}}">
                                        {{ Form::text('contact_number', Input::old('contact_number'), array()) }}
                                    </label>
                                    {{ $errors->first('contact_number', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.yearsOfExperience') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('years_of_experience') ? 'state-error' : null }}}">
                                        {{ Form::text('years_of_experience', Input::old('years_of_experience'), array()) }}
                                    </label>
                                    {{ $errors->first('years_of_experience', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <div class="row" id="shareholder-section" hidden>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.designation') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('designation') ? 'state-error' : null }}}">
                                        {{ Form::text('designation', Input::old('designation'), array()) }}
                                    </label>
                                    {{ $errors->first('designation', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.amountOfShare') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('amount_of_share') ? 'state-error' : null }}}">
                                        {{ Form::text('amount_of_share', Input::old('amount_of_share'), array()) }}
                                    </label>
                                    {{ $errors->first('amount_of_share', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.holdingPercentage') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('holding_percentage') ? 'state-error' : null }}}">
                                        {{ Form::text('holding_percentage', Input::old('holding_percentage'), array()) }}
                                    </label>
                                    {{ $errors->first('holding_percentage', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <footer>
                                {{ link_to_route('vendors.vendorRegistration.companyPersonnel', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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

@section('js')
    <script>
        $('select[name=type]').on('change', function(){
            if($(this).val() == {{ \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_SHAREHOLDERS }}){
                $('#default-section').hide();
                $('#shareholder-section').show();
            }
            else{
                $('#default-section').show();
                $('#shareholder-section').hide();
            }
        });

        if($('select[name=type]').val() == {{ \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_SHAREHOLDERS }}){
            $('#default-section').hide();
            $('#shareholder-section').show();
        }
    </script>
@endsection