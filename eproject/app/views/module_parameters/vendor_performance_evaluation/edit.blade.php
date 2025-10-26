@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('vendorManagement.vendorPerformanceEvaluationModuleParameter') }}</li>
	</ol>
@endsection
<?php use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter; ?>
@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('vendorManagement.vendorPerformanceEvaluationModuleParameter') }}
			</h1>
		</div>
	</div>
    <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('vendorManagement.vendorPerformanceEvaluationModuleParameter') }}</h2>
        </header>
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body no-padding">
                <form action="{{ route('vendor.performance.evaluation.module.parameter.update') }}" method="POST" class="smart-form">
                    <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                    <fieldset>					
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.defaultTimeFrameForVPECycle') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('default_time_frame_for_vpe_cycle_value') ? 'state-error' : null }}">
                                    <input type="text" name="default_time_frame_for_vpe_cycle_value" value="{{ Input::old('default_time_frame_for_vpe_cycle_value') ?? floatval($record->default_time_frame_for_vpe_cycle_value) }}">
                                </label>
                                {{ $errors->first('default_time_frame_for_vpe_cycle_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.unit') }}<span class="required">*</span></label>
                                <label class="select">
                                    <select name="default_time_frame_for_vpe_cycle_unit" class="select2">
                                        <?php $selectedValue = Input::old('default_time_frame_for_vpe_cycle_unit') ?? $record->default_time_frame_for_vpe_cycle_unit; ?>
                                        @foreach(VendorPerformanceEvaluationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.defaultTimeFrameForVPESubmission') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('default_time_frame_for_vpe_submission_value') ? 'state-error' : null }}">
                                    <input type="text" name="default_time_frame_for_vpe_submission_value" value="{{ Input::old('default_time_frame_for_vpe_submission_value') ?? floatval($record->default_time_frame_for_vpe_submission_value) }}">
                                </label>
                                {{ $errors->first('default_time_frame_for_vpe_submission_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.unit') }} <span class="required">*</span></label>
                                <label class="select">
                                    <?php $selectedValue = Input::old('default_time_frame_for_vpe_submission_unit') ?? $record->default_time_frame_for_vpe_submission_unit; ?>
                                    <select name="default_time_frame_for_vpe_submission_unit" class="select2">
                                        @foreach(VendorPerformanceEvaluationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.sendReminderToEvaluatorDaysBeforeVPESubmit') }}:</label>
                                <label class="fill-horizontal {{{ $errors->has('number_of_days_ahead_of_submission') ? 'state-error' : null }}}">
                                    {{ Form::select('number_of_days_ahead_of_submission[]', $submissionReminders, Input::old('number_of_days_ahead_of_submission[]') ?? $submissionReminders, array('class' => 'input-sm fill-horizontal', 'multiple' => 'multiple')) }}
                                </label>
                                {{ $errors->first('number_of_days_ahead_of_submission', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.generalGradingSystem') }}:</label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_management_grade_id') ? 'state-error' : null }}}">
                                    <?php $gradingSystems = [null => trans('general.selectAnOption')] + $gradingSystems; ?>
                                    {{ Form::select('vendor_management_grade_id', $gradingSystems, null, array('class' => 'select2 input-sm fill-horizontal')) }}
                                </label>
                                {{ $errors->first('vendor_management_grade_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-3">
                                <label class="label">{{ trans('vendorManagement.requireAttachmentsForSubmission') }}:</label>
                                <label class="fill-horizontal">
                                    <label class="checkbox"><input type="checkbox" name="attachments_required" {{ $record->attachments_required ? "checked" : "" }}><i></i>{{ trans('forms.required') }}</label>
                                </label>
                            </section>
                            <section class="col col-3" data-id="attachments_required_score_threshold">
                                <label class="label">{{ trans('vendorManagement.requireAttachmentsForSubmissionScoreThreshold') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('attachments_required_score_threshold') ? 'state-error' : null }}">
                                    <input type="number" name="attachments_required_score_threshold" value="{{ Input::old('attachments_required_score_threshold') ?? floatval($record->attachments_required_score_threshold) }}">
                                </label>
                                {{ $errors->first('attachments_required_score_threshold', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('vendorManagement.passingScore') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('passing_score') ? 'state-error' : null }}">
                                    <input type="number" name="passing_score" value="{{ Input::old('passing_score') ?? $record->passing_score }}">
                                </label>
                                {{ $errors->first('passing_score', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.emailReminderXPeriodBeforeVpeCycleEndDate') }}:</label>
                                <label class="fill-horizontal">
                                    <label class="checkbox"><input type="checkbox" name="email_reminder_before_cycle_end_date" {{ $record->email_reminder_before_cycle_end_date ? "checked" : "" }}><i></i>{{ trans('general.enable') }}</label>
                                </label>
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.period') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('email_reminder_before_cycle_end_date_value') ? 'state-error' : null }}">
                                    <input type="text" name="email_reminder_before_cycle_end_date_value" value="{{ Input::old('email_reminder_before_cycle_end_date_value') ?? floatval($record->email_reminder_before_cycle_end_date_value) }}">
                                </label>
                                {{ $errors->first('email_reminder_before_cycle_end_date_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('vendorManagement.unit') }}<span class="required">*</span></label>
                                <label class="select">
                                    <select name="email_reminder_before_cycle_end_date_unit" class="select2">
                                        <?php $selectedValue = Input::old('email_reminder_before_cycle_end_date_unit') ?? $record->email_reminder_before_cycle_end_date_unit; ?>
                                        @foreach(VendorPerformanceEvaluationModuleParameter::getUnitDescription() as $value => $description)
                                        <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </label>
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
    <script type="text/javascript">
       $('[name="number_of_days_ahead_of_submission[]"]').select2({
           theme: 'bootstrap',
           tags: true,
       });
       $('[name=vendor_management_grade_id]').val("{{ Input::old('vendor_management_grade_id') ?? $record->vendor_management_grade_id }}").trigger("change.select2");
       $('[name=attachments_required]').on('change', function(){
            if($(this).prop('checked')){
                $('[data-id=attachments_required_score_threshold]').show();
            }
            else{
                $('[data-id=attachments_required_score_threshold]').hide();
            }
       });

       if($('[name=attachments_required]').prop('checked')){
           $('[data-id=attachments_required_score_threshold]').show();
       }
       else{
           $('[data-id=attachments_required_score_threshold]').hide();
       }
    </script>
@endsection