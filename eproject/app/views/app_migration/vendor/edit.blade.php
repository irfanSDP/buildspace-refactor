<?php
$companyErrors = $errors->getBag('company');
?>

@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>Create Company Vendor</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-edit"></i> Create Company Vendor
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>Create Company Vendor</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    {{ Form::open(['route' => ['app.migration.vendor.store'], 'class' => 'smart-form', 'id' => 'company-form', 'method' => 'post']) }}
                    <fieldset>
                        <div class="row">
                            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('companies.name') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $companyErrors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name'), array('required')) }}
                                </label>
                                {{ $companyErrors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-xs-3 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('vendorManagement.activationDate') }}} <span class="required">*</span>:</label>
                                <?php
                                $activationDate = Input::old('activation_date', null);
                                $activationDate = ($activationDate) ? date('Y-m-d',strtotime($activationDate)) : null;
                                ?>
                                <label class="input {{{ $companyErrors->has('activation_date') ? 'state-error' : null }}}">
                                    <input min="2000-01-01" name="activation_date" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.activationDate') }}}" value="{{$activationDate}}" required/>
                                </label>
                            </section>
                            <section class="col col-xs-3 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('vendorManagement.expiryDate') }}} <span class="required">*</span>:</label>
                                <?php
                                $expiryDate = Input::old('expiry_date', null);
                                $expiryDate = ($expiryDate) ? date('Y-m-d',strtotime($expiryDate)) : null;
                                ?>
                                <label class="input {{{ $companyErrors->has('expiry_date') ? 'state-error' : null }}}">
                                    <input min="2000-01-01" name="expiry_date" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.expiryDate') }}}" value="{{$expiryDate}}" required/>
                                </label>
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('companies.address') }}}<span class="required">*</span>:</label>
                                <label class="textarea {{{ $companyErrors->has('address') ? 'state-error' : null }}}">
                                    {{ Form::textarea('address', Input::old('address'), array('required', 'rows' => 3, 'class' => 'rounded')) }}
                                </label>
                                {{ $companyErrors->first('address', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-3">
                                <label class="label">{{{ trans('companies.country') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal">
                                    <select class="form-control select2" name="country_id" id="country" data-action="filter" data-select-width="180px"></select>
                                </label>
                                {{ $companyErrors->first('country_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-3">
                                <label class="label">{{{ trans('companies.state') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal">
                                    <select class="form-control select2" name="state_id" id="state" data-action="filter" data-select-width="180px"></select>
                                </label>
                                {{ $companyErrors->first('state_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-3"></section>
                            <section class="col col-3"></section>
                        </div>
                    </fieldset>
                    
                    <hr class="simple"/>

                    <fieldset>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{{ trans('companies.contractGroupCategory') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $companyErrors->has('contract_group_category_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal" name="contract_group_category_id"></select>
                                </label>
                                {{ $companyErrors->first('contract_group_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{{ trans('vendorManagement.vendorCategory') }}}:</label>
                                <label class="fill-horizontal {{{ $companyErrors->has('vendor_category_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal" name="vendor_category_id[]" data-type="dependentSelection" data-dependent-id="second" @if($multipleVendorCategories) multiple @endif></select>
                                </label>
                                {{ $companyErrors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="form-group col col-xs-12 col-lg-6 col-md-6 col-sm-12">
                                <label class="label" for="contract_group_category_id">{{{ trans('businessEntityTypes.businessEntityTypes') }}}  <span class="required">*</span></label>
                                <label class="select fill-horizontal">
                                    <select name="business_entity_type_id" class="fill select2" style="width:100%;">
                                        <option value="" data-disabled="true" {{{ Input::old('business_entity_type_id') ? '' : 'selected' }}}>{{{ trans('businessEntityTypes.selectEntityType') }}}</option>
                                        @foreach($businessEntityTypes as $entityType)
                                            <option value="{{{ $entityType->id }}}" {{{ (Input::old('business_entity_type_id') == $entityType->id) ? 'selected' : '' }}}>{{{ $entityType->name }}}</option>
                                        @endforeach
                                        @if($allowOtherBusinessEntityTypes)
                                            <option value="other">{{ trans('forms.othersPleaseSpecify') }}</option>
                                        @endif
                                    </select>
                                </label>
                                {{ $companyErrors->first('company_contract_group_category_id', '<em class="invalid">:message</em>') }}
                            </section>

                            <section class="form-group col col-xs-12 col-lg-6 col-md-6 col-sm-12 {{{ $companyErrors->has('business_entity_type_other') ? 'has-error' : null }}}" style="display:none;" id="business_entity_type_other_section">
                                <label class="label">{{{ trans('forms.other') }}}  <span class="required">*</span></label>
                                <label class="input {{{ $companyErrors->has('business_entity_type_other') ? 'state-error' : null }}}">
                                {{ Form::text('business_entity_type_other', Input::old('business_entity_type_other'), array('placeholder' => trans('forms.other'))) }}
                                </label>
                                {{ $companyErrors->first('business_entity_type_other', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-4">
                                <label class="label">{{{ trans('companies.mainContact') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $companyErrors->has('main_contact') ? 'state-error' : null }}}">
                                    {{ Form::text('main_contact', Input::old('main_contact'), array('required')) }}
                                </label>
                                {{ $companyErrors->first('main_contact', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{{ trans('companies.referenceNumber') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ ($companyErrors->has('reference_no') || $companyErrors->has('registration_id')) ? 'state-error' : null }}}">
                                    {{ Form::text('reference_no', Input::old('reference_no'), array('required')) }}
                                </label>
                                {{ $companyErrors->first('reference_no', '<em class="invalid">:message</em>') }}
                                {{ $companyErrors->first('registration_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{{ trans('companies.taxRegistrationNumber') }}}:</label>
                                <label class="input {{{ ($companyErrors->has('tax_registration_no') || $companyErrors->has('tax_registration_id')) ? 'state-error' : null }}}">
                                    {{ Form::text('tax_registration_no', Input::old('tax_registration_no')) }}
                                </label>
                                {{ $companyErrors->first('tax_registration_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-4">
                                <label class="label">{{{ trans('companies.email') }}}:</label>
                                <label class="input {{{ $companyErrors->has('email') ? 'state-error' : null }}}">
                                    {{ Form::email('email', Input::old('email')) }}
                                </label>
                                {{ $companyErrors->first('email', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{{ trans('companies.telephone') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $companyErrors->has('telephone_number') ? 'state-error' : null }}}">
                                    {{ Form::text('telephone_number', Input::old('telephone_number'), array('required')) }}
                                </label>
                                {{ $companyErrors->first('telephone_number', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{{ trans('companies.fax') }}}:</label>
                                <label class="input {{{ $companyErrors->has('fax_number') ? 'state-error' : null }}}">
                                    {{ Form::text('fax_number', Input::old('fax_number')) }}
                                </label>
                                {{ $companyErrors->first('fax_number', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>

                    <hr class="simple"/>

                    <fieldset>
                        <div class="row">
                            <section class="col col-12">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_bumiputera', 1, Input::old('is_bumiputera', false), ['id'=>'is_bumiputera', 'class'=>'custom-control-input']) }}
                                    <label class="custom-control-label" for="is_bumiputera">{{{ trans('vendorManagement.bumiputera') }}}</label>
                                </div>
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.bumiputeraEquity') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $companyErrors->has('bumiputera_equity') ? 'state-error' : null }}}">
                                    {{ Form::text('bumiputera_equity', Input::old('bumiputera_equity', '0.00'), ['required' => 'required', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $companyErrors->first('bumiputera_equity', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.nonBumiputeraEquity') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $companyErrors->has('non_bumiputera_equity') ? 'state-error' : null }}}">
                                    {{ Form::text('non_bumiputera_equity', Input::old('non_bumiputera_equity', '0.00'), ['required' => 'required', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $companyErrors->first('non_bumiputera_equity', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.foreignerEquity') }}} :</label>
                                <label class="input {{{ $companyErrors->has('foreigner_equity') ? 'state-error' : null }}}">
                                    {{ Form::text('foreigner_equity', Input::old('foreigner_equity', '0.00'), ['autofocus' => 'autofocus']) }}
                                </label>
                                {{ $companyErrors->first('foreigner_equity', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>

                    <footer>
                        {{ link_to_route('companies', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                        {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.add'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::hidden('company_status', PCK\Companies\Company::COMPANY_STATUS_BUMIPUTERA) }}
                    {{ Form::hidden('id', -1) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
<script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
<script>
    $(document).ready(function () {

        var dependentSelection = $.extend({}, DependentSelection);
        dependentSelection.setUrls({first: webClaim.urlContractGroupCategories, second: webClaim.urlVendorCategories});
        dependentSelection.setForms({first: $('form [name=contract_group_category_id]'), second: $('form [name="vendor_category_id[]"]')});
        dependentSelection.setSelectedIds({first: webClaim.contractGroupCategoryId, second: webClaim.vendorCategoryId});
        dependentSelection.setPreSelectOnLoad({first: true, second: false});
        dependentSelection.init();

        $('select[name="business_entity_type_id"]').on('change', function(e) {
            e.preventDefault();

            var selectedValue = $(this).val();

            if(selectedValue == 'other') {
                $('#business_entity_type_other_section').show();
            } else {
                $('#business_entity_type_other_section').hide();
            }
        });

        if(webClaim.businessEntityTypeId == null) {
            $('select[name="business_entity_type_id"]').val('other').trigger('change');
            $('[name="business_entity_type_other"]').val(webClaim.businessEntityTypeName);
        } else {
            $('select[name="business_entity_type_id"]').val(webClaim.businessEntityTypeId).trigger('change');
        }
    });

</script>
@endsection