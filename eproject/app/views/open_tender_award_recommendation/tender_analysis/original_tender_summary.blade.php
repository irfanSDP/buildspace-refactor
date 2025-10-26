@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $project->latestTender->current_tender_name, [$project->id, $project->latestTender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $project->latestTender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.tender_analysis_table.index', 'Tender Analysis', [$project->id, $project->latestTender->id]) }}</li>
        <li>{{ trans('openTenderAwardRecommendation.summary') }}</li>
    </ol>
    @include('projects.partials.project_status')
@endsection
<?php $isEditable = isset($isEditable) ? $isEditable : true; ?>
<?php
    function formatAmount($amount) {
        $formattedAmount = number_format(abs($amount), 2, '.', ',');
        return ($amount < 0) ? HTML::decode('<font class="invalid">(' . $formattedAmount . ')</font>') : $formattedAmount;
    }
?>
@section('content')
    <article class="col-sm-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title txt-color-bluedark">
                    <i class="fa fa-file-alt fa-fw"></i>
                    {{ trans('openTenderAwardRecommendation.originalTenderSummary') }}
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="jarviswidget " data-widget-editbutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-file-alt"></i> </span>
                    <h2>{{ trans('openTenderAwardRecommendation.originalTender') }}</h2>
                </header>

                <div>
                    <div class="jarviswidget-editbox"></div>
                    <div class="widget-body">

                        <div class="panel-group smart-accordion-default" id="original_tender_summary-accordion">
                            
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h2 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#original_tender_summary-accordion" href="#original_tender_summary-accordion-1">
                                            <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> {{ (strlen($tendererDetails['tenderAlternativeTitle']) > 0) ? $tendererDetails['tenderAlternativeTitle']." ".trans('openTenderAwardRecommendation.summary') : trans('openTenderAwardRecommendation.summary') }}
                                        </a>
                                    </h2>
                                </div>
                                <div id="original_tender_summary-accordion-1" class="panel-collapse collapse in">
                                    <div class="panel-body no-padding">
                                        <table class="table table-hover table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="text-middle text-center" width="64px;">{{ trans('openTenderAwardRecommendation.rank') }}</th>
                                                    <th rowspan="2" class="text-middle text-left">{{ trans('openTenderAwardRecommendation.tendererName') }}</th>
                                                    <th rowspan="2" class="text-middle text-right" width="160px;">{{ trans('openTenderAwardRecommendation.tenderSum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                                    <th colspan="2" class="text-middle text-center">{{ trans('openTenderAwardRecommendation.varianceFromLowest') }}</th>
                                                    <th rowspan="2" class="text-middle text-center" width="160px;">{{ trans('openTenderAwardRecommendation.completionPeriod') }} ({{{ $tendererDetails['completionPeriodMetric'] }}})</th>
                                                </tr>
                                                <tr>
                                                    <th class="text-middle text-right" width="160px;">{{ trans('openTenderAwardRecommendation.amount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                                    <th class="text-middle text-right" width="98px;">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $number = 1; ?>
                                                @foreach ($tendererDetails['details'] as $tendererDetail)
                                                <?php 
                                                    if($tendererDetail['submitted']) {
                                                        $varianceFromTheLowest = $tendererDetail['tenderSum'] - $tendererDetails['lowestTenderSum'];
                                                        $varianceFromTheLowestPercentage = 0.00;

                                                        if(!empty($tendererDetails['lowestTenderSum']) and $tendererDetails['lowestTenderSum'] != 0) {
                                                            $varianceFromTheLowestPercentage = ($tendererDetails['lowestTenderSum']) ? round((($varianceFromTheLowest / $tendererDetails['lowestTenderSum']) * 100), 2) : 0;
                                                        }
                                                    }
                                                ?>
                                                <tr>
                                                    <td class="squeeze text-middle text-center">{{{ $number++ }}}</td>
                                                    <td class="text-middle text-left">{{{ $tendererDetail['tendererName'] }}}</td>
                                                    <td class="text-middle text-right">{{ $tendererDetail['submitted'] ? formatAmount($tendererDetail['tenderSum']) : '-'}}</td>
                                                    <td class="text-middle text-right">{{ $tendererDetail['submitted'] ? formatAmount($varianceFromTheLowest) : '-' }}</td>
                                                    <td class="squeeze text-middle text-right">{{ $tendererDetail['submitted'] ? formatAmount($varianceFromTheLowestPercentage) : '-' }}</td>
                                                    <td class="squeeze text-middle text-center">{{ number_format($tendererDetail['completionPeriod'], 2, '.', ',') }}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.consultantPTE') }}</td>
                                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->consultant_estimate)) ? '-' : formatAmount($tenderSummary->consultant_estimate) }}</td>
                                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->consultant_estimate)) ? '-' : formatAmount($tenderSummary->consultant_estimate - $tendererDetails['lowestTenderSum']) }}</td>
                                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->consultant_estimate) || empty($tendererDetails['lowestTenderSum']) || $tendererDetails['lowestTenderSum'] == 0) ? '-' : formatAmount(round(((($tenderSummary->consultant_estimate - $tendererDetails['lowestTenderSum']) / $tendererDetails['lowestTenderSum']) * 100), 2)) }}</td>
                                                    <td class="text-middle text-center">{{ number_format($tendererDetails['completionPeriod'], 2, '.', ',') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.budget') }}</td>
                                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->budget)) ? '-' : formatAmount($tenderSummary->budget) }}</td>
                                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->budget)) ? '-' : formatAmount($tenderSummary->budget - $tendererDetails['lowestTenderSum']) }}</td>
                                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->budget) || empty($tendererDetails['lowestTenderSum']) || $tendererDetails['lowestTenderSum'] == 0) ? '-' : formatAmount(round(((($tenderSummary->budget - $tendererDetails['lowestTenderSum']) / $tendererDetails['lowestTenderSum']) * 100), 2)) }}</td>
                                                    <td class="text-middle text-center">{{ number_format($tendererDetails['completionPeriod'], 2, '.', ',') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </article>
    @if ($isEditable)
        <section id="widget-grid-2" class="">
            <div class="row">
                <article class="col-sm-12 col-md-6 col-lg-6" id="updateConsultEstimateSection">
                    <div class="jarviswidget" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                        <header>
                            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                            <h2>{{ trans('openTenderAwardRecommendation.updateConsultantPTE') }}</h2>
                        </header>
                        <div>
                            <div class="widget-body no-padding">
                                <form class="smart-form" method="POST" action="{{ route('open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary.consultantEstimate.update', [$project->id, $tender->id]) }}">
                                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                    <fieldset>			
                                        <section>
                                            <label class="label">{{ trans('openTenderAwardRecommendation.consultantPTE') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</label>
                                            <label class="input">
                                                <input type="number" class="input-sm" min="0.0" max="999999999999999.00" step="0.01" class="text-left" name="consultant_estimate" value="" required>
                                            </label>
                                        </section>
                                    </fieldset>
                                    <footer>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('openTenderAwardRecommendation.submit') }}</button>
                                    </footer>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
                <article class="col-sm-12 col-md-6 col-lg-6" id="updateBudgetSection">
                    <div class="jarviswidget" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                        <header>
                            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                            <h2>{{ trans('openTenderAwardRecommendation.updateBudget') }}</h2>
                        </header>
                        <div>
                            <div class="widget-body no-padding">
                                <form class="smart-form" method="POST" action="{{ route('open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary.budget.update', [$project->id, $tender->id]) }}">
                                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                    <fieldset>			
                                        <section>
                                            <label class="label">{{ trans('openTenderAwardRecommendation.budget') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</label>
                                            <label class="input">
                                                <input type="number" class="input-sm" min="0.0" max="999999999999999.00 step="0.01" class="text-left" name="budget" value="" required>
                                            </label>
                                        </section>
                                    </fieldset>
                                    <footer>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('openTenderAwardRecommendation.submit') }}</button>
                                    </footer>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    @endif
@endsection