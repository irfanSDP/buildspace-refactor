@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.companyDetails') }}}</li>
    </ol>
@endsection
<?php use PCK\ObjectField\ObjectField; ?>
<?php use PCK\Companies\Company; ?>
<?php use PCK\BuildingInformationModelling\BuildingInformationModellingLevel; ?>
@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-building"></i> {{ trans('vendorManagement.companyDetails') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{{ $company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    @if(!empty($section->amendment_remarks))
                    <div class="well @if($section->amendmentsRequired()) border-danger @elseif($section->amendmentsMade()) border-warning @endif">
                        {{ nl2br($section->amendment_remarks) }}
                    </div>
                    @endif
                    {{ Form::open(array('route' => array('vendor.registration.details.update'), 'class' => 'smart-form')) }}
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <fieldset>
                            <div class="row">
                                <section class="col col-xs-11">
                                    <label class="label">{{{ trans('companies.name') }}}<span class="required">*</span>:</label>
                                    @if(!empty($settings->name_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->name_instructions) }}</div>
                                    @endif
                                    <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="name" value="{{{ $company->name }}}"/>
                                    </label>
                                    {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->name_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments" 
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyName']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyName']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyName']) }}"
                                        data-field="vendorRegistrationDetailsCompanyName">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyName');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                        <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                    </button>
                                </section>
                                @endif
                            </div>
                            
                            <div class="row">
                                <section class="col col-xs-11">
                                    <label class="label">{{{ trans('companies.address') }}}<span class="required">*</span>:</label>
                                    @if(!empty($settings->address_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->address_instructions) }}</div>
                                    @endif
                                    <label class="textarea {{{ $errors->has('address') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="address" value="{{{ $company->address }}}"/>
                                    </label>
                                    {{ $errors->first('address', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->address_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyAddress']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyAddress']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyAddress']) }}"
                                        data-field="vendorRegistrationDetailsCompanyAddress">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyAddress');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>
                            
                            <div class="row">
                                <section class="col col-5">
                                    <label class="label">{{{ trans('vendorManagement.vendorGroup') }}} <span class="required">*</span>:</label>
                                    @if(!empty($settings->contract_group_category_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->contract_group_category_instructions) }}</div>
                                    @endif
                                    <label class="fill-horizontal {{{ $errors->has('contract_group_category_id') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" disabled value="{{{ $company->contractGroupCategory->name }}}"/>
                                    </label>
                                </section>
                                @if($attachmentSettings->contract_group_category_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsUserType']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsUserType']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsUserType']) }}"
                                        data-field="vendorRegistrationDetailsUserType">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsUserType');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-5">
                                    <label class="label">{{{ trans('vendorManagement.vendorCategory') }}}:</label>
                                    @if(!empty($settings->vendor_category_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->vendor_category_instructions) }}}</div>
                                    @endif
                                    <label class="fill-horizontal {{{ $errors->has('vendor_category_id') ? 'state-error' : null }}}">
                                        <select name="vendor_category_id[]" class="select2" @if($multipleVendorCategories) multiple @endif>
                                        @foreach($vendorCategories as $vendorCategory)
                                            <?php $selected = in_array($vendorCategory->id, $selectedVendorCategoryIds); ?>
                                            <option value="{{ $vendorCategory->id }}" @if($selected) selected @endif>{{ $vendorCategory->name }}</option>
                                        @endforeach
                                        </select>
                                    </label>
                                    {{ $errors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->vendor_category_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsVendorCategory']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsVendorCategory']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsVendorCategory']) }}"
                                        data-field="vendorRegistrationDetailsVendorCategory">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsVendorCategory');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>

                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.mainContact') }}}<span class="required">*</span>:</label>
                                    @if(!empty($settings->contact_person_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->contact_person_instructions) }}</div>
                                    @endif
                                    <label class="input {{{ $errors->has('main_contact') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="main_contact" value="{{{ $company->main_contact }}}"/>
                                    </label>
                                    {{ $errors->first('main_contact', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->main_contact_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyMainContact']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyMainContact']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyMainContact']) }}"
                                        data-field="vendorRegistrationDetailsCompanyMainContact">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyMainContact');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.referenceNumber') }}}<span class="required">*</span>:</label>
                                    @if(!empty($settings->reference_number_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->reference_number_instructions) }}</div>
                                    @endif
                                    <label class="input {{{ $errors->has('reference_no') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="reference_no" value="{{{ $company->reference_no }}}"/>
                                    </label>
                                    {{ $errors->first('reference_no', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->reference_number_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyRocNumber']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyRocNumber']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyRocNumber']) }}"
                                        data-field="vendorRegistrationDetailsCompanyRocNumber">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyRocNumber');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.taxRegistrationNumber') }}}:</label>
                                    @if(!empty($settings->tax_registration_number_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->tax_registration_number_instructions) }}}</div>
                                    @endif
                                    <label class="input {{{ ($errors->has('tax_registration_no') || $errors->has('tax_registration_id')) ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="tax_registration_number" value="{{{ $company->tax_registration_no }}}"/>
                                    </label>
                                    {{ $errors->first('tax_registration_no', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->tax_registration_number_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber']) }}"
                                        data-field="vendorRegistrationDetailsCompanyTaxRegistrationNumber">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>

                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.email') }}}:</label>
                                    @if(!empty($settings->email_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->email_instructions) }}</div>
                                    @endif
                                    <label class="input {{{ $errors->has('email') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="email" value="{{{ $company->email }}}"/>
                                    </label>
                                    {{ $errors->first('email', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->email_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyEmail']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyEmail']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyEmail']) }}"
                                        data-field="vendorRegistrationDetailsCompanyEmail">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyEmail');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.telephone') }}}<span class="required">*</span>:</label>
                                    @if(!empty($settings->telephone_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->telephone_instructions) }}</div>
                                    @endif
                                    <label class="input {{{ $errors->has('telephone_number') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="telephone_number" value="{{{ $company->telephone_number }}}"/>
                                    </label>
                                    {{ $errors->first('telephone_number', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->telephone_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyTelephone']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyTelephone']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyTelephone']) }}"
                                        data-field="vendorRegistrationDetailsCompanyTelephone">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyTelephone');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.fax') }}}:</label>
                                    @if(!empty($settings->fax_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->fax_instructions) }}</div>
                                    @endif
                                    <label class="input {{{ $errors->has('fax_number') ? 'state-error' : null }}}">
                                        <input type="text" class="form-control" name="fax_number" value="{{{ $company->fax_number }}}"/>
                                    </label>
                                </section>
                                @if($attachmentSettings->fax_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyFax']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyFax']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyFax']) }}"
                                        data-field="vendorRegistrationDetailsCompanyFax">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyFax');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>
                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.country') }}} <span class="required">*</span>:</label>
                                    @if(!empty($settings->country_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->country_instructions) }}</div>
                                    @endif
                                    <label class="fill-horizontal">
                                        <select class="form-control select2" name="country_id" id="country" data-action="filter" data-select-width="180px"></select>
                                    </label>
                                    {{ $errors->first('country_id', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->country_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyCountry']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyCountry']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyCountry']) }}"
                                        data-field="vendorRegistrationDetailsCompanyCountry">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyCountry');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.state') }}} <span class="required">*</span>:</label>
                                    @if(!empty($settings->state_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->state_instructions) }}</div>
                                    @endif
                                    <select class="form-control select2" name="state_id" id="state" data-action="filter" data-select-width="180px">
                                        <option value="" disabled>{{{ trans('companies.selectState') }}}</option>   
                                    </select>
                                    {{ $errors->first('state_id', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->state_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyState']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyState']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyState']) }}"
                                        data-field="vendorRegistrationDetailsCompanyState">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyState');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>
                            @if($company->isContractor())
                            <div class="row">
                                <section class="col col-xs-5">
                                    <label class="label">{{ trans('companies.cidbGrade') }} <span class="required">*</span></label>
                                    @if(!empty($settings->cidb_grade_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->cidb_grade_instructions) }}</div>
                                    @endif
                                    <select name="cidb_grade" class="select2">
                                        @foreach($cidb_grades as $cidb_grade)
                                            @if($company->cidb_grade == $cidb_grade->id)
                                                <option value="{{{$cidb_grade->id}}}" selected >{{{ $cidb_grade->grade }}}</option>
                                            @else
                                                <option value="{{{$cidb_grade->id}}}">{{{ $cidb_grade->grade }}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    {{ $errors->first('cidb_grade', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->cidb_grade_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCIDBGrade']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCIDBGrade']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCIDBGrade']) }}"
                                        data-field="vendorRegistrationDetailsCIDBGrade">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCIDBGrade');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-xs-5">
                                    <label class="label">{{ trans('companies.cidbCode') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a class="btn btn-link btn-sm" data-toggle="modal" data-target="#viewCidbCodesModal">
                                        <i class="fa fa-eye"></i>
                                        {{{ trans('companies.viewCidbCode') }}}
                                        </a>
                                    </label>
                                    @if(!empty($settings->cidb_code_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->cidb_code_instructions) }}</div>
                                    @endif
                                    <select name="cidb_code_id[]" id="cidb_code_id" class="form-control select2" multiple>
                                        @foreach($cidbCodes as $cidbCode)
                                        <?php $selected = in_array($cidbCode->id, $selectedCidbCodeIds); ?>
                                            @if($cidbCode->parent && !$cidbCode->child)
                                                <option @if($selected) selected @endif disabled value="{{{ $cidbCode->id }}}">
                                                    {{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @elseif($cidbCode->parent && $cidbCode->child)
                                                <option @if($selected) selected @endif disabled value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @elseif($cidbCode->child)
                                                <option @if($selected) selected @endif value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @else($cidbCode->subChild)
                                                <option @if($selected) selected @endif value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @endif
                                        @endforeach
                                </select> 
                                </section>
                            </div>
                            @endif
                            @if($company->isConsultant())
                            <div class="row">
                                <section class="col col-xs-5">
                                    <label class="label">{{ trans('companies.bimLevel') }} <span class="required">*</span></label>
                                    @if(!empty($settings->bim_level_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->bim_level_instructions) }}</div>
                                    @endif
                                    <select name="bim_level_id" class="select2">
                                        <option value="">{{ trans('general.selectAnOption') }}</option>
                                        @foreach(BuildingInformationModellingLevel::getBIMLevelSelections() as $id => $name)
                                        <?php $selected = ($company->bim_level_id == $id) ? 'selected' : null; ?>
                                        <option value="{{{ $id }}}" {{{ $selected }}}>{{{ $name }}}</option>
                                        @endforeach
                                    </select>
                                    {{ $errors->first('bim_level_id', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->bim_level_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsBIMLevel']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsBIMLevel']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsBIMLevel']) }}"
                                        data-field="vendorRegistrationDetailsBIMLevel">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsBIMLevel');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>
                            @endif
                            <div class="row">
                                <section class="col col-xs-5">
                                    <label class="label">{{ trans('vendorManagement.companyStatus') }} <span class="required">*</span></label>
                                    @if(!empty($settings->company_status_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->company_status_instructions) }}</div>
                                    @endif
                                    <select name="company_status" class="select2">
                                        <option value="">{{ trans('general.selectAnOption') }}</option>
                                        @foreach($companyStatusDescriptions as $identifier => $description)
                                        <?php $selected = ($company->company_status == $identifier) ? 'selected' : null; ?>
                                        <option value="{{{ $identifier }}}" {{{ $selected }}}>{{{ $description }}}</option>
                                        @endforeach
                                    </select>
                                    {{ $errors->first('company_status', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->company_status_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyStatus']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyStatus']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyStatus']) }}"
                                        data-field="vendorRegistrationDetailsCompanyStatus">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyStatus');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>
                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{{ trans('vendorManagement.bumiputeraEquity') }}} :</label>
                                    @if(!empty($settings->bumiputera_equity_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->bumiputera_equity_instructions) }}</div>
                                    @endif
                                    <label class="fill-horizontal">
                                        <input type="text" class="form-control" name="bumiputera_equity" value="{{{ $company->bumiputera_equity }}}"/>
                                    </label>
                                    {{ $errors->first('bumiputera_equity', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->bumiputera_equity_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyBumiputeraEquity']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyBumiputeraEquity']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyBumiputeraEquity']) }}"
                                        data-field="vendorRegistrationDetailsCompanyBumiputeraEquity">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyBumiputeraEquity');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('vendorManagement.nonBumiputeraEquity') }}} :</label>
                                    @if(!empty($settings->non_bumiputera_equity_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->non_bumiputera_equity_instructions) }}</div>
                                    @endif
                                    <label class="fill-horizontal">
                                        <input type="text" class="form-control" name="non_bumiputera_equity" value="{{{ $company->non_bumiputera_equity }}}"/>
                                    </label>
                                    {{ $errors->first('nonBumiputeraEquity', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->non_bumiputera_equity_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity']) }}"
                                        data-field="vendorRegistrationDetailsCompanyNonBumiputeraEquity">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                                <section class="col col-3">
                                    <label class="label">{{{ trans('vendorManagement.foreignerEquity') }}} :</label>
                                    @if(!empty($settings->foreigner_equity_instructions))
                                    <div class="label padded label-success text-white">{{ nl2br($settings->foreigner_equity_instructions) }}</div>
                                    @endif
                                    <label class="fill-horizontal">
                                        <input type="text" class="form-control" name="foreigner_equity" value="{{{ $company->foreigner_equity }}}"/>
                                    </label>
                                    {{ $errors->first('foreigner', '<em class="invalid">:message</em>') }}
                                </section>
                                @if($attachmentSettings->foreigner_equity_attachments)
                                <section class="col col-xs-1">
                                    <label class="label">&nbsp;</label>
                                    <button type="button" class="btn btn-xs btn-info pull-right" data-action="upload-item-attachments"
                                        data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyForeignerEquity']) }}"
                                        data-route-update-attachments="{{ route('vendor.registration.details.attachements.update', [$company->id, 'vendorRegistrationDetailsCompanyForeignerEquity']) }}"
                                        data-route-get-attachments-count="{{ route('vendor.registration.details.attachements.count.get', [$company->id, 'vendorRegistrationDetailsCompanyForeignerEquity']) }}"
                                        data-field="vendorRegistrationDetailsCompanyForeignerEquity">
                                        <?php 
                                            $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyForeignerEquity');
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                                </section>
                                @endif
                            </div>
                        </fieldset>

                        <footer>
                            <a href="{{ route('vendor.registration.form.show') }}" class="btn btn-info">{{ trans('forms.next') }}</a>
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(array('id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', array('id' => 'invoice-upload'))
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>

@include('vendor_registration.vendor_details.partials.view_cidb_codes_modal')

@endsection

@section('js')
    <script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
    <script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
    <script>
        $(document).ready(function() {
            function addRowToUploadModal(fileAttributes){
                var clone = $('[data-type=template] tr.template-download').clone();
                var target = $('#uploadFileTable tbody.files');

                $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
                $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
                $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
                $(clone).find("a[data-category=link]").html(fileAttributes['filename']);
                $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
                $(clone).find("[data-category=size]").html(fileAttributes['size']);
                $(clone).find("button[data-action=delete]").prop('data-route', fileAttributes['deleteRoute']);
                $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);

                target.append(clone);
            }

            $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
                e.preventDefault();

                var target = $('#uploadFileTable tbody.files').empty();
                var data   = $.get($(this).data('route-get-attachments-list'), function(data){
                    for(var i in data){
                        addRowToUploadModal({
                            download_url: data[i]['download_url'],
                            filename: data[i]['filename'],
                            imgSrc: data[i]['imgSrc'],
                            id: data[i]['id'],
                            size: data[i]['size'],
                            deleteRoute: data[i]['deleteRoute'],
                            createdAt: data[i]['createdAt'],
                        });
                    }
                });

                $('[data-action=submit-attachments]').data('updated-attachment-count-url', $(this).data('route-get-attachments-count'));
                $('#uploadAttachmentModal').modal('show');
                $('#attachment-upload-form').prop('action',$(this).data('route-update-attachments'));
            });

            $(document).on('click', '[data-action=submit-attachments]', function(){
                var updatedAttachmentCountUrl = $(this).data('updated-attachment-count-url');
                var uploadedFilesInput        = [];

                $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                    uploadedFilesInput.push($(this).val());
                });

                app_progressBar.show();

                $.post($('form#attachment-upload-form').prop('action'),{
                    _token: _csrf_token,
                    uploaded_files: uploadedFilesInput
                })
                .done(function(data){
                    if(data.success){
                        $('#uploadAttachmentModal').modal('hide');

                        $.get(updatedAttachmentCountUrl, {},function(resp) {
                            $(document).find('[data-field="' + resp.field + '"]').find('[data-component="attachment_upload_count"]').text(resp.attachmentCount);
                        });

                        app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            });
        });
    </script>
@endsection