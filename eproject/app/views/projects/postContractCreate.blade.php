@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>Publish to Post Contract</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-upload"></i> Publish to Post Contract
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('url' => Request::fullUrl(), 'class' => 'smart-form', 'id' => 'add-form')) }}
                            @if($project->contract->type == \PCK\Contracts\Contract::TYPE_PAM2006)
                                @include('projects.partials.postContractForms.projectFormPostContract')
                            @elseif($project->contract->type == \PCK\Contracts\Contract::TYPE_INDONESIA_CIVIL_CONTRACT)
                                @include('projects.partials.postContractForms.indonesiaCivilContractInformation')
                            @endif
                            @include('daily_labour_reports.project_labour_rates')
                            <footer>
                                {{ link_to_route('projects.show', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                {{ Form::submit('Publish', array('class' => 'btn btn-primary')) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    @include('projects.postContractFormJs')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#add-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });
        });

        $('.commencement_date').datepicker({
            dateFormat : 'dd-M-yy',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',
            autoclose: true,
            onSelect : function(selectedDate) {
                $('.completion_date').datepicker('option', 'minDate', selectedDate);
            }
        });

        $('.completion_date').datepicker({
            dateFormat : 'dd-M-yy',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',
            autoclose: true,
            onSelect : function(selectedDate) {
                $('.commencement_date').datepicker('option', 'maxDate', selectedDate);
            }
        });</script>
@endsection