@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.tender_analysis_table.index', 'Tender Analysis', [$project->id, $tender->id]) }}</li>
        <li>{{ trans('openTenderAwardRecommendation.budgetVSaward') }}</li>
    </ol>
    @include('projects.partials.project_status')
@endsection
<?php
    function formatAmount($amount) {
        $formattedAmount = number_format(abs($amount), 2, '.', ',');
        return ($amount < 0) ? HTML::decode('<font class="invalid">(' . $formattedAmount . ')</font>') : $formattedAmount;
    }
?>
@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-bluedark">
                <i class="fa fa-hand-holding-usd fa-fw"></i> {{ trans('openTenderAwardRecommendation.budgetVSaward') }}
            </h1>
        </div>
    </div>
    <section id="widget-grid">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget " id="wid-id-5" 
                    data-widget-colorbutton="false"
                    data-widget-editbutton="false"
                    data-widget-togglebutton="false"
                    data-widget-deletebutton="false"
                    data-widget-fullscreenbutton="false"
                    data-widget-custombutton="false"
                    data-widget-collapsed="false"
                    data-widget-sortable="false">
                    <header>
                        <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-hand-holding-usd"></i> </span>
                        <h2>{{ trans('openTenderAwardRecommendation.budgetVSaward') }}</h2>
                    </header>
                    <div>
                        <div class="jarviswidget-editbox"></div>
                        <div class="widget-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-middle text-center squeeze" style="width:64px;">{{ trans('openTenderAwardRecommendation.item') }}</th>
                                            <th class="text-middle text-center">{{ trans('openTenderAwardRecommendation.description') }}</th>
                                            <th class="text-middle text-right" style="width:160px;">{{ trans('openTenderAwardRecommendation.budget') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                            <th class="text-middle text-right" style="width:160px;">{{ trans('openTenderAwardRecommendation.award') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                            <th class="text-middle text-right" style="width:180px;">{{ trans('openTenderAwardRecommendation.savings') }} / ({{{ trans('openTenderAwardRecommendation.overrun') }}}) ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                            <th class="text-middle text-right" style="width:120px;">{{ trans('openTenderAwardRecommendation.variance') }} (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $number = 1; ?>
                                        <form class="smart-form" action="{{ route('open_tender.award_recommendation.report.tender_analysis_table.budgetvsaward.update', [$project->id, $tender->id]) }}" method="POST">
                                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                            @if(!is_null($results['billData']))
                                                @foreach ($results['billData'] as $billData)
                                                    <tr>
                                                        <td class="text-middle text-center">{{{ $number++ }}}</td>
                                                        <td class="text-middle text-left">{{{ $billData['description'] }}}</td>
                                                        <td class="text-middle text-right">
                                                            @if ($isEditable)
                                                                <input type="number" min="0.0" max="999999999999999.00" step="0.01" name="{{{ $billData['billId'] }}}" value="{{ $billData['budget'] }}" required></td>
                                                            @else
                                                                {{ formatAmount($billData['budget']) }}
                                                            @endif
                                                        </td>
                                                        <td class="text-middle text-right">{{ formatAmount($billData['billAmount']) }}</td>
                                                        <td class="text-middle text-right">{{ (empty($billData['budget'])) ? '-' : formatAmount($billData['budget'] - $billData['billAmount']) }}</td>
                                                        <td class="text-middle text-right">{{ (empty($billData['budget']) || $billData['budget'] == 0) ? '-' : formatAmount((($billData['budget'] - $billData['billAmount']) / $billData['budget']) * 100) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.totalAmount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</td>
                                                <td class="text-middle text-right">{{ empty($results['billData']) ? '-' : formatAmount($results['budgetTotal']) }}</td>
                                                <td class="text-middle text-right">{{ empty($results['billData']) ? '-' : formatAmount($results['billAmountTotal']) }}</td>
                                                <td class="text-middle text-right">{{ empty($results['budgetTotal']) ? '-' : formatAmount($results['budgetTotal'] - $results['billAmountTotal']) }}</td>
                                                <td class="text-middle text-right">{{ (empty($results['budgetTotal']) || $results['budgetTotal'] == 0) ? '-' : formatAmount((($results['budgetTotal'] - $results['billAmountTotal']) / $results['budgetTotal']) * 100) }}</td>
                                            </tr>
                                            @if ($isEditable && (!is_null($results['billData'])))
                                                <tr>
                                                    <td colspan="6"><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('openTenderAwardRecommendation.submit') }}</button></td>
                                                </tr>
                                            @endif
                                        </form>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection