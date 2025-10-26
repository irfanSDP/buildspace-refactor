@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluationModuleParameter') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluationModuleParameter') }}
			</h1>
		</div>
	</div>
    <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluationModuleParameter') }}</h2>
        </header>
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body no-padding">
                <form action="{{ route('digital-star.module-parameter.update') }}" method="POST" class="smart-form">
                    <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                    <fieldset>
                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{ trans('digitalStar/vendorManagement.generalGradingSystem') }}:</label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_management_grade_id') ? 'state-error' : null }}}">
                                    <?php $gradingSystems = [null => trans('general.selectAnOption')] + $gradingSystems; ?>
                                    {{ Form::select('vendor_management_grade_id', $gradingSystems, null, array('class' => 'select2 input-sm fill-horizontal')) }}
                                </label>
                                {{ $errors->first('vendor_management_grade_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>

                        <div class="row">
                            <section class="col col-4">
                                <label class="label">{{ trans('digitalStar/vendorManagement.emailReminderXPeriodBeforeVpeCycleEndDate') }}:</label>
                                <label class="fill-horizontal">
                                    <label class="checkbox"><input type="checkbox" name="email_reminder_before_cycle_end_date" {{ $record->email_reminder_before_cycle_end_date ? "checked" : "" }}><i></i>{{ trans('general.enable') }}</label>
                                </label>
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('digitalStar/vendorManagement.period') }} <span class="required">*</span></label>
                                <label class="input {{ $errors->has('email_reminder_before_cycle_end_date_value') ? 'state-error' : null }}">
                                    <input type="text" name="email_reminder_before_cycle_end_date_value" value="{{ Input::old('email_reminder_before_cycle_end_date_value') ?? floatval($record->email_reminder_before_cycle_end_date_value) }}">
                                </label>
                                {{ $errors->first('email_reminder_before_cycle_end_date_value', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-4">
                                <label class="label">{{ trans('digitalStar/vendorManagement.unit') }}<span class="required">*</span></label>
                                <label class="select">
                                    <select name="email_reminder_before_cycle_end_date_unit" class="select2">
                                        <?php $selectedValue = Input::old('email_reminder_before_cycle_end_date_unit') ?? $record->email_reminder_before_cycle_end_date_unit; ?>
                                        @foreach($unitDescriptions as $value => $description)
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
       $('[name=vendor_management_grade_id]').val("{{ Input::old('vendor_management_grade_id') ?? $record->vendor_management_grade_id }}").trigger("change.select2");
    </script>
@endsection