@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show',  str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.show',  trans('technicalEvaluation.technicalEvaluationResults'), array($project->id, $tender->id)) }}</li>
        <li>{{{ trans('technicalEvaluation.summary') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-balance-scale"></i> {{{ trans('technicalEvaluation.technicalEvaluation') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                @include('technical_evaluation.results.partials.index_action_menu')
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            @include('technical_evaluation.results.partials.summary_table')
        </div>
    </div>

    @foreach($setReference->set->children as $aspect)
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                @include('technical_evaluation.results.partials.aspect_table')
            </div>
        </div>
    @endforeach

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.datatable' ).DataTable({
                sDom: 't',
                scrollX: '500px',
                paging: false,
                bSort: false
            });

            $('[data-action=export-overall-report]').on('click', function(e){
                window.open($(this).data('route'), '_self');
            });
        });
    </script>
@endsection