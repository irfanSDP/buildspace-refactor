@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}</li>
        <li>{{ link_to_route('digital-star.cycle.index', trans('digitalStar/vendorManagement.vendorPerformanceEvaluationCycles'), array()) }}</li>
        <li>{{ trans('forms.add') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('digitalStar/vendorManagement.createNewCycle') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('digitalStar/vendorManagement.createNewCycle') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    @if($errors->has('form'))
                        @include('layout.partials.flash_message_view', array('notificationLevel' => 'danger', 'message' => $errors->first('form')))
                    @endif
                    {{ Form::open(array('route' => array('digital-star.cycle.store'), 'class' => 'smart-form', 'data-submit-loading' => '1')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.startDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('start_date') ? 'state-error' : null }}}">
                                    <input type="date" name="start_date" value="{{ Input::old('start_date') ?? date('Y-m-d') }}">
                                </label>
                                {{ $errors->first('start_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.endDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('end_date') ? 'state-error' : null }}}">
                                    <input type="date" name="end_date" value="{{ Input::old('end_date') ?? date('Y-m-d') }}">
                                </label>
                                {{ $errors->first('end_date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.vpeCycleName') }}}:</label>
                                <label class="input">
                                    {{ Form::textArea('remarks', Input::old('remarks'), array('class' => 'fill-horizontal', 'rows' => 3)) }}
                                </label>
                            </section>
                        </div>

                        <div class="row">
                            <div class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('digitalStar/digitalStar.weightageStarRating') }}}</label>
                            </div>

                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('digitalStar/digitalStar.weightageCompany') }}} <span class="required">*</span>:</label>
                                <label class="input">
                                    <i class="icon-append">%</i>
                                    {{ Form::number('weight_company', Input::old('weight_company'), [
                                        'required' => 'required',
                                        'min' => 0,
                                        'max' => 100,
                                    ]) }}
                                </label>
                                <em class="invalid" data-error="weight_company"></em>
                            </section>

                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('digitalStar/digitalStar.weightageProject') }}} <span class="required">*</span>:</label>
                                <label class="input">
                                    <i class="icon-append">%</i>
                                    {{ Form::number('weight_project', Input::old('weight_project'), [
                                        'required' => 'required',
                                        'min' => 0,
                                        'max' => 100,
                                    ]) }}
                                </label>
                                <em class="invalid" data-error="weight_project"></em>
                            </section>
                        </div>

                        <footer>
                            {{ link_to_route('digital-star.cycle.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            const weightCompanyInput = $("[name='weight_company']");
            const weightProjectInput = $("[name='weight_project']");

            if (weightCompanyInput.length === 0 || weightProjectInput.length === 0) {
                console.warn("Weight inputs not found.");
                return;
            }

            function adjustWeights(changedInput, otherInput) {
                let changedValue = parseInt(changedInput.val(), 10) || 0;

                changedValue = Math.min(100, Math.max(0, changedValue));
                changedInput.val(changedValue);

                otherInput.val(100 - changedValue);
            }

            weightCompanyInput.on("input", function () {
                adjustWeights(weightCompanyInput, weightProjectInput);
            });

            weightProjectInput.on("input", function () {
                adjustWeights(weightProjectInput, weightCompanyInput);
            });

            $('[data-submit-loading="1"]').on('submit', function(e) {
                if (typeof app_progressBar !== 'undefined' && app_progressBar.toggle) {
                    app_progressBar.toggle();
                }
                return true;
            });
        });
    </script>
@endsection