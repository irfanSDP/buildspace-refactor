@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.cycle.index', trans('vendorManagement.vendorPerformanceEvaluationCycles'), array()) }}</li>
        <li>{{ trans('forms.add') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.createNewCycle') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.createNewCycle') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    @if($errors->has('form'))
                        @include('layout.partials.flash_message_view', array('notificationLevel' => 'danger', 'message' => $errors->first('form')))
                    @endif
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.cycle.store'), 'class' => 'smart-form', 'data-submit-loading' => '1')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.startDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('start_date') ? 'state-error' : null }}}">
                                    <input type="date" name="start_date" value="{{ Input::old('start_date') ?? date('Y-m-d') }}">
                                </label>
                                {{ $errors->first('start_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.endDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('end_date') ? 'state-error' : null }}}">
                                    <input type="date" name="end_date" value="{{ Input::old('end_date') ?? date('Y-m-d') }}">
                                </label>
                                {{ $errors->first('end_date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.vpeCycleName') }}}:</label>
                                <label class="input">
                                    {{ Form::textArea('remarks', Input::old('remarks'), array('class' => 'fill-horizontal', 'rows' => 3)) }}
                                </label>
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.cycle.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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
        $('[data-submit-loading="1"]').on('submit', function(e) {
            app_progressBar.toggle();
            return true;
        });
    </script>
@endsection