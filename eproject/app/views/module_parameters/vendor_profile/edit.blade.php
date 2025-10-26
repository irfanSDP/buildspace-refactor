@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('vendorManagement.vendorProfileModuleParameter') }}</li>
	</ol>
@endsection
<?php use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter; ?>
@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('vendorManagement.vendorProfileModuleParameter') }}
			</h1>
		</div>
	</div>
    <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('vendorManagement.vendorProfileModuleParameter') }}</h2>
        </header>
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body no-padding">
                <form action="{{ route('vendor.profile.module.parameter.update') }}" method="POST" class="smart-form">
                    <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                    <fieldset>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.registrationPrice') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('registration_price') ? 'state-error' : null }}">
                                    <input type="number" name="registration_price" value="{{ Input::old('registration_price') ?? $record->registration_price }}" step="0.01" min="0">
                                </label>
                                {{ $errors->first('registration_price', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.renewalPrice') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('renewal_price') ? 'state-error' : null }}">
                                    <input type="number" name="renewal_price" value="{{ Input::old('renewal_price') ?? $record->renewal_price }}" step="0.01" min="0">
                                </label>
                                {{ $errors->first('renewal_price', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.validityPeriodOfActiveVendorInAVL') }}<span class="required">*</span></label>
                                <label class="input {{ $errors->has('validity_period_of_active_vendor_in_avl_value') ? 'state-error' : null }}">
                                    <input type="text" name="validity_period_of_active_vendor_in_avl_value" value="{{ Input::old('validity_period_of_active_vendor_in_avl_value') ?? floatval($record->validity_period_of_active_vendor_in_avl_value) }}">
                                </label>
                                {{ $errors->first('validity_period_of_active_vendor_in_avl_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.unit') }}<span class="required">*</span></label>
                                <label class="select">
                                    <select name="validity_period_of_active_vendor_in_avl_unit" class="select2">
                                        <?php $selectedValue = Input::old('validity_period_of_active_vendor_in_avl_unit') ?? $record->validity_period_of_active_vendor_in_avl_unit; ?>
                                        @foreach(VendorProfileModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.gracePeriodExpiredVendorMoveToDVL') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('grace_period_of_expired_vendor_before_moving_to_dvl_value') ? 'state-error' : null }}">
                                    <input type="text" name="grace_period_of_expired_vendor_before_moving_to_dvl_value" value="{{ Input::old('grace_period_of_expired_vendor_before_moving_to_dvl_value') ?? floatval($record->grace_period_of_expired_vendor_before_moving_to_dvl_value) }}">
                                </label>
                                {{ $errors->first('grace_period_of_expired_vendor_before_moving_to_dvl_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('grace_period_of_expired_vendor_before_moving_to_dvl_unit') ?? $record->grace_period_of_expired_vendor_before_moving_to_dvl_unit; ?>
                                    <select name="grace_period_of_expired_vendor_before_moving_to_dvl_unit" class="select2">
                                        @foreach(VendorProfileModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.periodToRetrainVendorInWL') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('vendor_retain_period_in_wl_value') ? 'state-error' : null }}">
                                    <input type="text" name="vendor_retain_period_in_wl_value" value="{{ Input::old('vendor_retain_period_in_wl_value') ?? floatval($record->vendor_retain_period_in_wl_value) }}">
                                </label>
                                {{ $errors->first('vendor_retain_period_in_wl_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('vendor_retain_period_in_wl_unit') ?? $record->vendor_retain_period_in_wl_unit; ?>
                                    <select name="vendor_retain_period_in_wl_unit" class="select2">
                                        @foreach(VendorProfileModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.renewalPeriodBeforeExpiryInDays') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('renewal_period_before_expiry_in_days') ? 'state-error' : null }}">
                                    <input type="number" name="renewal_period_before_expiry_in_days" value="{{ Input::old('renewal_period_before_expiry_in_days') ?? $record->renewal_period_before_expiry_in_days }}">
                                </label>
                                {{ $errors->first('renewal_period_before_expiry_in_days', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.minScoreToMoveFromNWLToAVL') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('watch_list_nomineee_to_active_vendor_list_threshold_score') ? 'state-error' : null }}">
                                    <input type="number" name="watch_list_nomineee_to_active_vendor_list_threshold_score" value="{{ Input::old('watch_list_nomineee_to_active_vendor_list_threshold_score') ?? $record->watch_list_nomineee_to_active_vendor_list_threshold_score }}">
                                </label>
                                {{ $errors->first('watch_list_nomineee_to_active_vendor_list_threshold_score', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.scoreThresholdToMoveFromNWLToWL') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('watch_list_nomineee_to_watch_list_threshold_score') ? 'state-error' : null }}">
                                    <input type="number" name="watch_list_nomineee_to_watch_list_threshold_score" value="{{ Input::old('watch_list_nomineee_to_watch_list_threshold_score') ?? $record->watch_list_nomineee_to_watch_list_threshold_score }}">
                                </label>
                                {{ $errors->first('watch_list_nomineee_to_watch_list_threshold_score', '<em class="invalid">:message</em>') }}
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