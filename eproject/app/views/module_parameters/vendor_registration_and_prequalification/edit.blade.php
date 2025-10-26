@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('vendorManagement.vendorRegistrationAndPrequalificationModuleParameter') }}</li>
	</ol>
@endsection
<?php use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter; ?>
@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('vendorManagement.vendorRegistrationAndPrequalificationModuleParameter') }}
			</h1>
		</div>
	</div>
    <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('vendorManagement.vendorRegistrationAndPrequalificationModuleParameter') }}</h2>
        </header>
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body no-padding">
                <form action="{{ route('vendor.registration.and.prequalification.module.parameter.update') }}" method="POST" class="smart-form">
                    <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                    <fieldset>					
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.validityPeriodOfTempLoginAccToUnregistedVendor') }}<span class="required">*</span></label>
                                <label class="input {{ $errors->has('valid_period_of_temp_login_acc_to_unreg_vendor_value') ? 'state-error' : null }}">
                                    <input type="text" name="valid_period_of_temp_login_acc_to_unreg_vendor_value" value="{{ Input::old('valid_period_of_temp_login_acc_to_unreg_vendor_value') ?? $record->valid_period_of_temp_login_acc_to_unreg_vendor_value }}">
                                </label>
                                {{ $errors->first('valid_period_of_temp_login_acc_to_unreg_vendor_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.unit') }}<span class="required">*</span></label>
                                <label class="select">
                                    <select name="valid_period_of_temp_login_acc_to_unreg_vendor_unit" class="select2">
                                        <?php $selectedValue = Input::old('valid_period_of_temp_login_acc_to_unreg_vendor_unit') ?? $record->valid_period_of_temp_login_acc_to_unreg_vendor_unit; ?>
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.allowOneCompanyToRegisterUnderMultiVendorCategory') }} <span class="required">*</span></label>
                                <label class="select">
                                    <select name="allow_only_one_comp_to_reg_under_multi_vendor_category" class="select2">
                                        <?php 
                                            $value         = Input::old('allow_only_one_comp_to_reg_under_multi_vendor_category') ?? $record->allow_only_one_comp_to_reg_under_multi_vendor_category;
                                            $selectedValue = $value ? VendorRegistrationAndPrequalificationModuleParameter::OPTION_YES : VendorRegistrationAndPrequalificationModuleParameter::OPTION_NO;
                                        ?>
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getYesNoOptions() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.vendorRegCertToBeGeneratedAndSentToSuccessfulRegVendor') }} <span class="required">*</span></label>
                                <label class="select">
                                    <select name="vendor_reg_cert_generated_sent_to_successful_reg_vendor" class="select2">
                                        <?php 
                                            $value         = Input::old('vendor_reg_cert_generated_sent_to_successful_reg_vendor') ?? $record->vendor_reg_cert_generated_sent_to_successful_reg_vendor;
                                            $selectedValue = $value ? VendorRegistrationAndPrequalificationModuleParameter::OPTION_YES : VendorRegistrationAndPrequalificationModuleParameter::OPTION_NO;
                                        ?>
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getYesNoOptions() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.numberOfDaysForValidSubmission') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('valid_submission_days') ? 'state-error' : null }}">
                                    <input type="number" name="valid_submission_days" value="{{ Input::old('valid_submission_days') ?? $record->valid_submission_days }}">
                                </label>
                                {{ $errors->first('valid_submission_days', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.sendNotificationOfPurgingDataBeforeEndOfTempAccValidPeriod', ['value' => $record->notify_vendor_before_end_of_temp_acc_valid_period_value, 'unit' => VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription($record->notify_vendor_before_end_of_temp_acc_valid_period_unit)]) }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('notify_vendor_before_end_of_temp_acc_valid_period_value') ? 'state-error' : null }}">
                                    <input type="text" name="notify_vendor_before_end_of_temp_acc_valid_period_value" value="{{ Input::old('notify_vendor_before_end_of_temp_acc_valid_period_value') ?? $record->notify_vendor_before_end_of_temp_acc_valid_period_value }}">
                                </label>
                                {{ $errors->first('notify_vendor_before_end_of_temp_acc_valid_period_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php 
                                        $selectedValue = Input::old('notify_vendor_before_end_of_temp_acc_valid_period_unit') ?? $record->notify_vendor_before_end_of_temp_acc_valid_period_unit;
                                    ?>
                                    <select name="notify_vendor_before_end_of_temp_acc_valid_period_unit" class="select2">
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.periodToRetainUnsuccessfulRegandPreqSubmission', ['unit' => VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription($record->period_retain_unsuccessful_reg_and_preq_submission_unit)]) }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('period_retain_unsuccessful_reg_and_preq_submission_value') ? 'state-error' : null }}">
                                    <input type="text" name="period_retain_unsuccessful_reg_and_preq_submission_value" value="{{ Input::old('period_retain_unsuccessful_reg_and_preq_submission_value') ?? $record->period_retain_unsuccessful_reg_and_preq_submission_value }}">
                                </label>
                                {{ $errors->first('period_retain_unsuccessful_reg_and_preq_submission_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-3">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('period_retain_unsuccessful_reg_and_preq_submission_unit') ?? $record->period_retain_unsuccessful_reg_and_preq_submission_unit; ?>
                                    <select name="period_retain_unsuccessful_reg_and_preq_submission_unit" class="select2">
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                            <section class="col col-3">
                                <label class="label">{{ trans('vendorManagement.selectStartingPeriodToRetainInfo', ['unit' => VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription($record->period_retain_unsuccessful_reg_and_preq_submission_unit)]) }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('start_period_retain_unsuccessful_reg_and_preq_submission_value') ?? $record->start_period_retain_unsuccessful_reg_and_preq_submission_value; ?>
                                    <select name="start_period_retain_unsuccessful_reg_and_preq_submission_value" class="select2">
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getRetainInfoStartingPeriod() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.notifyDataPurgeToVendorBeforeEndOfPeriodUnsuccessfulSubmission', ['period' => $record->notify_purge_data_before_end_period_for_unsuccessful_sub_value, 'unit' => VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription($record->notify_purge_data_before_end_period_for_unsuccessful_sub_unit)]) }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('notify_purge_data_before_end_period_for_unsuccessful_sub_value') ? 'state-error' : null }}">
                                    <input type="text" name="notify_purge_data_before_end_period_for_unsuccessful_sub_value" value="{{ Input::old('notify_purge_data_before_end_period_for_unsuccessful_sub_value') ?? $record->notify_purge_data_before_end_period_for_unsuccessful_sub_value }}">
                                </label>
                                {{ $errors->first('notify_purge_data_before_end_period_for_unsuccessful_sub_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('notify_purge_data_before_end_period_for_unsuccessful_sub_unit') ?? $record->notify_purge_data_before_end_period_for_unsuccessful_sub_unit; ?>
                                    <select name="notify_purge_data_before_end_period_for_unsuccessful_sub_unit" class="select2">
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.retainBasicInfoOfUnsuccessfullyRegisteredVendor') }}<span class="required">*</span></label>
                                <label class="select">
                                    <select name="retain_info_of_unsuccessfully_reg_vendor_after_data_purge" class="select2">
                                        <?php 
                                            $value         = Input::old('retain_info_of_unsuccessfully_reg_vendor_after_data_purge') ?? $record->retain_info_of_unsuccessfully_reg_vendor_after_data_purge;
                                            $selectedValue = $value ? VendorRegistrationAndPrequalificationModuleParameter::OPTION_YES : VendorRegistrationAndPrequalificationModuleParameter::OPTION_NO;
                                        ?>
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getYesNoOptions() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                            <section class="col col-4" id="retainInfoListSection">
                                <label class="label">{{ trans('vendorManagement.selectInfoToDisplayIfYes') }}</label>
                                    <div class="col col-12">
                                        @foreach($record->getAvailableInformationToDisplay() as $name => $info)
                                            <?php $checked = $info['checked'] ? 'checked' : null; ?>
                                            <label class="checkbox"><input type="checkbox" name="{{ $name }}" {{ $checked }}><i></i>{{ $info['description'] }}</label>
                                        @endforeach
                                    </div>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-8">
                                <label class="label">{{ trans('vendorManagement.sendRenewalEmailToVendorsBeforeExpiryDate', ['value' => $record->notify_vendors_for_renewal_value, 'unit' => VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription($record->notify_vendors_for_renewal_unit)]) }}<span class="required">*</span></label>
                                <label class="input {{ $errors->has('notify_vendors_for_renewal_value') ? 'state-error' : null }}">
                                    <input type="text" name="notify_vendors_for_renewal_value" value="{{ Input::old('notify_vendors_for_renewal_value') ?? $record->notify_vendors_for_renewal_value }}">
                                </label>
                                {{ $errors->first('notify_vendors_for_renewal_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('notify_vendors_for_renewal_unit') ?? $record->notify_vendors_for_renewal_unit; ?>
                                    <select name="notify_vendors_for_renewal_unit" class="select2">
                                        @foreach(VendorRegistrationAndPrequalificationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <hr>
                        &nbsp;
                        @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.generalGradingSystem') }}:</label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_management_grade_id') ? 'state-error' : null }}}">
                                    {{ Form::select('vendor_management_grade_id', $gradingSystems, null, array('class' => 'select2 input-sm fill-horizontal')) }}
                                </label>
                                {{ $errors->first('vendor_management_grade_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        @endif
                        <div class="row">
                            <section class="col col-lg-12">
                                <label class="label fill-horizontal">{{ trans('vendorManagement.vendorDeclaration') }}<span class="required">*</span></label>
                                    <label class="textarea {{ $errors->has('vendor_declaration') ? 'state-error' : null }}">
                                    <textarea class="form-control" rows="3" name="vendor_declaration" placeholder="{{ trans('vendorManagement.declarationMessage') }}">{{{Input::old('vendor_declaration') ?? $record->vendor_declaration }}}</textarea>
                                </label>
                                {{ $errors->first('vendor_declaration', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
                    </footer>
                </form>						  
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function(e) {
            if($('[name="retain_info_of_unsuccessfully_reg_vendor_after_data_purge"]').val() == "{{ VendorRegistrationAndPrequalificationModuleParameter::OPTION_NO }}") {
                $('#retainInfoListSection').hide();
            }

            $('[name="retain_info_of_unsuccessfully_reg_vendor_after_data_purge"]').on('change', function(e) {
                if($(this).val() == "{{ VendorRegistrationAndPrequalificationModuleParameter::OPTION_YES}}") {
                    $('#retainInfoListSection').show();
                } else {
                    $('#retainInfoListSection').hide();
                }
            });

            $('[name=vendor_management_grade_id]').val("{{ Input::old('vendor_management_grade_id') ?? $record->vendor_management_grade_id }}").trigger("change.select2");
        });
    </script>
@endsection