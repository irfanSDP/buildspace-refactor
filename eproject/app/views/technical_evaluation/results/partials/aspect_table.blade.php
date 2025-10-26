<?php use \PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption as Option; ?>
<div class="jarviswidget ">
    <a name="{{{ $aspect->name }}}"></a>
    <header>
        <h2> {{{ $aspect->name }}} </h2>
    </header>
    <div>
        <div class="widget-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                    <tr>
                        <th style="width: 15px;" class="text-center text-middle">{{{ trans('technicalEvaluation.ref') }}}</th>
                        <th style="width: auto; min-width:300px;" class="text-center text-middle">{{{ trans('technicalEvaluation.criteria') }}}</th>
                        <th style="width: 40px;" class="text-center text-middle">{{{ trans('technicalEvaluation.score') }}}</th>
                        @foreach($tenderers as $tenderer)
                            <th class="text-center text-middle" style="width:300px;">
                                {{{ $tenderer->name }}}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <?php $index = 1; ?>
                    @foreach($aspect->children as $criterion)
                        <tr>
                            <td class="text-center text-middle">
                                {{{ $index++ }}}
                            </td>
                            <td class="text-left text-middle">
                                <a href="{{ route('technicalEvaluation.results.inDepth', array($project->id, $tender->id, $aspect->id)) }}" class="plain">
                                    {{{ $criterion->name }}}
                                </a>
                            </td>
                            <td class="text-center text-middle">
                                {{{ $criterion->value }}}
                            </td>
                            @foreach($tenderers as $tenderer)
                                <td class="text-center text-middle">
                                    {{{ number_format(Option::getTendererScore($tenderer, $criterion), 2) }}}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot style="color:#333!important;">
                    <tr class="active">
                        <th class="text-right text-middle" colspan="2">
                            {{ trans('technicalEvaluation.total') }}
                        </th>
                        <th class="text-center text-middle">
                            {{{ number_format(($setReference->set->getChildrenValueTotal() * 100), 2) }}}
                        </th>
                        @foreach($tenderers as $tenderer)
                            <th class="text-center text-middle">
                                {{{ number_format(Option::getTendererScore($tenderer, $aspect), 2) }}}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="active">
                        <th class="text-right text-middle" colspan="2">
                            {{ trans('technicalEvaluation.weighting') }}
                        </th>
                        <th class="text-center text-middle">
                            {{{ number_format($aspect->value, 2) }}}
                        </th>
                        @foreach($tenderers as $tenderer)
                            <th class="text-center text-middle">
                                {{{ number_format($aspect->value, 2) }}}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="text-right text-middle" colspan="2">
                            {{ trans('technicalEvaluation.overallScore') }}
                        </th>
                        <th class="text-center text-middle">
                            {{{ number_format(($aspect->getChildrenValueTotal() * $aspect->value), 2) }}}
                        </th>
                        @foreach($tenderers as $tenderer)
                            <th class="text-center text-middle">
                                {{{ number_format((Option::getTendererScore($tenderer, $aspect) * $aspect->value), 2) }}}
                            </th>
                        @endforeach
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>