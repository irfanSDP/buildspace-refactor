@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('riskRegister.index', trans('riskRegister.riskRegister'), array($project->id)) }}</li>
        <li>{{ trans('riskRegister.registerNew') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-exclamation-triangle"></i> {{{ trans('riskRegister.registerNew') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('riskRegister.registerNew') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('route' => array('riskRegister.store', $project->id), 'class' => 'smart-form', 'id' => 'add-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-lg-2 col-md-2">
                                        <label class="label">{{{ trans('riskRegister.reference') }}} :</label>
                                        <label class="input {{{ $errors->has('reference_number') ? 'state-error' : null }}}">
                                            {{ Form::number('reference_number', Input::old('reference_number') ? Input::old('reference_number') : $defaultReferenceNumber, array('min' => 1)) }}
                                        </label>
                                        {{ $errors->first('reference_number', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-lg-10 col-md-10">
                                        <label class="label">{{{ trans('riskRegister.subject') }}} <span class="required">*</span>:</label>
                                        <label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
                                            {{ Form::text('subject', Input::old('subject'), array('required' => 'required', 'autofocus' => true)) }}
                                        </label>
                                        {{ $errors->first('subject', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>
                            <fieldset>
                                @include('risk_register.partials.risk_form_fields')
                            </fieldset>
                            <footer>
                                {{ link_to_route('riskRegister.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                {{ Form::submit(trans('forms.add'), array('class' => 'btn btn-primary')) }}
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