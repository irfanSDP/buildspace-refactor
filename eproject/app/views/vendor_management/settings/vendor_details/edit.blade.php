@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ trans('vendorManagement.vendorDetailsSettings') }}</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-building"></i> {{ trans('vendorManagement.vendorDetailsSettings') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{{ trans('vendorManagement.vendorDetailsSettings') }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    {{ Form::model($settings, array('route' => array('vendorRegistration.vendorDetails.instructions.settings.update'), 'class' => 'smart-form')) }}
                        <fieldset>
                            <legend>{{ trans('vendorManagement.instructions') }}</legend>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.name') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('name_instructions', Input::old('name_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.address') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('address_instructions', Input::old('address_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.vendorGroup') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('contract_group_category_instructions', Input::old('contract_group_category_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.vendorCategory') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('vendor_category_instructions', Input::old('vendor_category_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.mainContact') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('contact_person_instructions', Input::old('contact_person_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.referenceNumber') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('reference_number_instructions', Input::old('reference_number_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.taxRegistrationNumber') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('tax_registration_number_instructions', Input::old('tax_registration_number_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.email') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('email_instructions', Input::old('email_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.telephone') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('telephone_instructions', Input::old('telephone_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.fax') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('fax_instructions', Input::old('fax_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.country') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('country_instructions', Input::old('country_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.state') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('state_instructions', Input::old('state_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.companyStatus') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('company_status_instructions', Input::old('company_status_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.bumiputeraEquity') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('bumiputera_equity_instructions', Input::old('bumiputera_equity_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.nonBumiputeraEquity') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('non_bumiputera_equity_instructions', Input::old('non_bumiputera_equity_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.foreignerEquity') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('foreigner_equity_instructions', Input::old('foreigner_equity_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.cidbGrade') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('cidb_grade_instructions', Input::old('cidb_grade_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('companies.bimLevel') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('bim_level_instructions', Input::old('bim_level_instructions'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                        </fieldset>

                        <footer>
                            {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'save', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
            <header>
                <h2>{{{ trans('vendorManagement.vendorDetailsSettings') }}}</h2>				
            </header>
            <div>
                <div class="widget-body">
                    <form class="smart-form" action="{{ route('vendorRegistration.vendorDetails.attachment.settings.update') }}" method="POST">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <fieldset>
                            <legend>{{ trans('general.attachments') }}</legend>
                            <div class="row">
                                <section>
                                    <div class="row">
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="name_attachments" @if($attachmentSettings->name_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.name') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="address_attachments" @if($attachmentSettings->address_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.address') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="contract_group_category_attachments" @if($attachmentSettings->contract_group_category_attachments) checked="true" @endif>
                                                <i></i>{{ trans('vendorManagement.vendorGroup') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="vendor_category_attachments" @if($attachmentSettings->vendor_category_attachments) checked="true" @endif>
                                                <i></i>{{ trans('vendorManagement.vendorCategory') }}</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="main_contact_attachments" @if($attachmentSettings->main_contact_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('companies.mainContact') }}}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="reference_number_attachments" @if($attachmentSettings->reference_number_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('companies.referenceNumber') }}}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="tax_registration_number_attachments" @if($attachmentSettings->tax_registration_number_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('companies.taxRegistrationNumber') }}}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="email_attachments" @if($attachmentSettings->email_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.email') }}</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="telephone_attachments" @if($attachmentSettings->telephone_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.telephone') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="fax_attachments" @if($attachmentSettings->fax_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.fax') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="country_attachments" @if($attachmentSettings->country_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.country') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="state_attachments" @if($attachmentSettings->state_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.state') }}</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="company_status_attachments" @if($attachmentSettings->company_status_attachments) checked="true" @endif>
                                                <i></i>{{ trans('vendorManagement.companyStatus') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="bumiputera_equity_attachments" @if($attachmentSettings->bumiputera_equity_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('vendorManagement.bumiputeraEquity') }}}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="non_bumiputera_equity_attachments" @if($attachmentSettings->non_bumiputera_equity_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('vendorManagement.nonBumiputeraEquity') }}}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="foreigner_equity_attachments" @if($attachmentSettings->foreigner_equity_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('vendorManagement.foreignerEquity') }}}</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="cidb_grade_attachments" @if($attachmentSettings->cidb_grade_attachments) checked="true" @endif>
                                                <i></i>{{ trans('companies.cidbGrade') }}</label>
                                        </div>
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="bim_level_attachments" @if($attachmentSettings->bim_level_attachments) checked="true" @endif>
                                                <i></i>{{{ trans('companies.bimLevel') }}}</label>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </fieldset>
                        <footer>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                        </footer>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{{ trans('vendorManagement.sectionInstructions') }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    {{ Form::model($instructions, array('route' => array('vendorRegistration.settings.sectionInstructions.update'), 'class' => 'smart-form')) }}
                        <fieldset>
                            <legend>{{ trans('vendorManagement.instructions') }}</legend>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.companyPersonnel') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('company_personnel', Input::old('company_personnel'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.projectTrackRecord') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('project_track_record', Input::old('project_track_record'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.supplierCreditFacilities') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('supplier_credit_facilities', Input::old('supplier_credit_facilities'), array('rows' => 3)) }}
                                    </label>
                                </section>
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorManagement.vendorRegistrationPayment') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('payment', Input::old('payment'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{{ trans('vendorPreQualification.vendorPreQualification') }}}:</label>
                                    <label class="textarea">
                                        {{ Form::textarea('vendor_pre_qualifications', Input::old('vendor_pre_qualifications'), array('rows' => 3)) }}
                                    </label>
                                </section>
                            </div>     
                        </fieldset>

                        <footer>
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'save', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
