@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show',  str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.show',  trans('technicalEvaluation.technicalEvaluationResults'), array($project->id, $tender->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.summary',  trans('technicalEvaluation.summary'), array($project->id, $tender->id)) }}</li>
        <li>{{{ trans('technicalEvaluation.inDepth') }}}</li>
    </ol>
@endsection

@section('content')

    <?php use \PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption as Option; ?>

    <div class="row">
        <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-balance-scale"></i>
                {{{ trans('technicalEvaluation.technicalEvaluation') }}} [ {{{ trans('technicalEvaluation.inDepth') }}} ]
            </h1>
        </div>
        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('technicalEvaluation.results.summary', array($project->id, $tender->id)) }}" class="btn btn-default pull-right">
                {{ trans('technicalEvaluation.backToSummary') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('technicalEvaluation.results') }}} ({{{ $aspect->name }}})</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover datatable table-condensed">
                                <thead>
                                <?php $rowspan = $tenderers->isEmpty() ? "" : 2; ?>
                                <tr>
                                    <th rowspan="{{{ $rowspan }}}" style="width: 15px;" class="text-center text-middle">{{{ trans('technicalEvaluation.ref') }}}</th>
                                    <th rowspan="{{{ $rowspan }}}" style="width: 40px;" class="text-center text-middle">{{{ trans('technicalEvaluation.score') }}}</th>
                                    <th rowspan="{{{ $rowspan }}}" style="width: auto; min-width: 300px;" class="text-center text-middle">{{{ trans('technicalEvaluation.items') }}}</th>
                                    @foreach($tenderers as $tenderer)
                                        <th colspan="3" class="text-center text-middle" style="min-width: 280px;">
                                            {{{ $tenderer->name }}}
                                        </th>
                                    @endforeach
                                </tr>
                                @if(!$tenderers->isEmpty())
                                    <tr>
                                        @foreach($tenderers as $tenderer)
                                            <th class="text-center text-middle">
                                                {{ trans('technicalEvaluation.selection') }}
                                            </th>
                                            <th class="text-center text-middle">
                                                {{ trans('technicalEvaluation.remarks') }}
                                            </th>
                                            <th class="text-center text-middle">
                                                {{ trans('technicalEvaluation.score') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                @endif
                                </thead>
                                <tbody>
                                    <?php $index = 1; ?>
                                    @foreach($aspect->children as $criterion)
                                        <tr class="success">
                                            <td class="text-center text-middle">
                                                {{{ $index++ }}}
                                            </td>
                                            <td class="text-center text-middle">
                                                {{{ number_format($criterion->value, 2) }}}
                                            </td>
                                            <td class="text-left text-middle">
                                                <strong>{{{ $criterion->name }}}</strong>
                                            </td>
                                            @foreach($tenderers as $tenderer)
                                                <td></td>
                                                <td></td>
                                                <td class="text-center text-middle">
                                                    {{{ number_format(Option::getTendererScore($tenderer, $criterion), 2) }}}
                                                </td>
                                            @endforeach
                                        </tr>
                                        @foreach($criterion->children as $item)
                                            <tr class="active">
                                                <td>
                                                    <!-- Index -->
                                                </td>
                                                <td class="text-center text-middle">
                                                    {{{ number_format($item->value, 2) }}}
                                                </td>
                                                <td class="text-left text-middle">
                                                    {{{ $item->name }}}
                                                </td>
                                                @foreach($tenderers as $tenderer)
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                @endforeach
                                            </tr>
                                            @foreach($item->children as $option)
                                                <tr>
                                                    <td>
                                                        <!-- Index -->
                                                    </td>
                                                    <td class="text-center text-middle">
                                                        {{{ number_format($option->value, 2) }}}
                                                    </td>
                                                    <td class="text-left text-middle">
                                                        <em>{{{ $option->name }}}</em>
                                                    </td>
                                                    @foreach($tenderers as $tenderer)
                                                        <td class="text-center text-middle">
                                                            @if(array_key_exists($option->id, $selectedOptionIds[$tenderer->id]))
                                                                <i class="fa fa-check"></i>
                                                            @endif
                                                        </td>
                                                        <td class="text-right text-middle">
                                                            {{{ Option::getOptionRemarks($tenderer, $option) }}}
                                                        </td>
                                                        <td class="text-center text-middle">
                                                            @if(array_key_exists($option->id, $selectedOptionIds[$tenderer->id]))
                                                                {{{ number_format($option->value, 2) }}}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="info">
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                @foreach($tenderers as $tenderer)
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-center text-middle">
                                            {{ trans('technicalEvaluation.total') }}
                                        </th>
                                        <th class="text-center text-middle">
                                            {{{ number_format($aspect->getChildrenValueTotal(), 2) }}}
                                        </th>
                                        <th>
                                            &nbsp;
                                        </th>
                                        @foreach($tenderers as $tenderer)
                                            <th>
                                                &nbsp;
                                            </th>
                                            <th>
                                                &nbsp;
                                            </th>
                                            <th class="text-center text-middle">
                                                {{{ number_format(Option::getTendererScore($tenderer, $aspect), 2) }}}
                                            </th>
                                        @endforeach
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.datatable' ).DataTable({
                sDom: 't',
                scrollX: '500px',
                scrollY: '500px',
                paging: false,
                bSort: false
            });
        });
    </script>
@endsection