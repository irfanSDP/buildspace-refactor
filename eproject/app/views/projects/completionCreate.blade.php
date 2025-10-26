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
                <i class="fa fa-upload"></i> Publish to Completion
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
                        @include('projects.partials.projectFormCompletion')

                        <footer>
                            {{ link_to_route('projects.show', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-upload"></i> Publish', ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>
@endsection

@section('inline-js')
    $('.completion_date').datepicker({
        dateFormat : 'dd-M-yy',
        prevText : '<i class="fa fa-chevron-left"></i>',
        nextText : '<i class="fa fa-chevron-right"></i>',
        onSelect : function(selectedDate) {
            $('.commencement_date').datepicker('option', 'maxDate', selectedDate);
        }
    });
@endsection