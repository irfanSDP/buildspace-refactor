@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.setups.index', trans('vendorManagement.setup'), array()) }}</li>
        <li>{{{ $evaluation->project->short_title }}}</li>
        <li>{{ trans('forms.edit') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('forms.edit') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('forms.edit') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::model($evaluation, array('route' => array('vendorPerformanceEvaluation.setups.update', $evaluation->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.startDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('start_date') ? 'state-error' : null }}}">
                                    {{ Form::text('start_date', Input::old('start_date'), array('class' => 'datetimepicker')) }}
                                </label>
                                {{ $errors->first('start_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.endDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('end_date') ? 'state-error' : null }}}">
                                    {{ Form::text('end_date', Input::old('end_date'), array('class' => 'datetimepicker')) }}
                                </label>
                                {{ $errors->first('end_date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.setups.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            showTodayButton: true,
            allowInputToggle: true
        });
    </script>
@endsection