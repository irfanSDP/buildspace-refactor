<?php use \PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption as Option; ?>
<div class="jarviswidget jarviswidget-color-purple">
    <header>
        <h2> {{{ trans('technicalEvaluation.summary') }}} </h2>
    </header>
    <div>
        <div class="widget-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                    <tr>
                        <th style="width: 15px;" class="text-center text-middle">{{{ trans('technicalEvaluation.ref') }}}</th>
                        <th style="width: auto; min-width: 250px;" class="text-center text-middle">{{{ trans('technicalEvaluation.summaryItems') }}}</th>
                        <th style="width: 40px;" class="text-center text-middle">{{{ trans('technicalEvaluation.weighting') }}}</th>
                        <th style="width: 40px;" class="text-center text-middle">{{{ trans('technicalEvaluation.score') }}}</th>
                        @foreach($tenderers as $tenderer)
                            <th class="text-center text-middle" style="width:300px;">
                                {{{ $tenderer->name }}}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <?php $index = 'A'; ?>
                    @foreach($setReference->set->children as $aspect)
                        <tr>
                            <td class="text-center text-middle">
                                {{{ $index++ }}}
                            </td>
                            <td class="text-left text-middle">
                                <a href="#{{{ $aspect->name }}}" class="plain">{{{ $aspect->name }}}</a>
                            </td>
                            <td class="text-center text-middle">
                                {{{ $aspect->value * 100 }}}%
                            </td>
                            <td class="text-center text-middle">
                                {{{ number_format($aspect->value * 100, 2) }}}
                            </td>
                            @foreach($tenderers as $tenderer)
                                <td class="text-center text-middle">
                                    {{{ number_format((Option::getTendererScore($tenderer, $aspect) * $aspect->value), 2) }}}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot style="color:#333!important;">
                    <tr>
                        <th class="text-center text-middle" colspan="2">
                            {{ trans('technicalEvaluation.total') }}
                        </th>
                        <th class="text-center text-middle">
                            {{{ $setReference->set->getChildrenValueTotal() * 100 }}}%
                        </th>
                        <th class="text-center text-middle">
                            {{{ number_format(($setReference->set->getChildrenValueTotal() * 100), 2) }}}
                        </th>
                        @foreach($tenderers as $tenderer)
                            <th class="text-center text-middle">
                                {{{ number_format(Option::getTendererScore($tenderer, $setReference->set), 2) }}}
                            </th>
                        @endforeach
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>