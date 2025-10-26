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
                    {{{ $tender->current_tender_name }}} {{ trans('openTenderAwardRecommendation.summary') }}
                </h1>
            </div>
        </div>

        <div class="row">
            <div class="jarviswidget " data-widget-editbutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-file-alt"></i> </span>
                    <h2>{{{ $tender->current_tender_name }}} {{ trans('openTenderAwardRecommendation.summary') }}</h2>
                </header>

                <div>
                    <div class="jarviswidget-editbox"></div>
                    <div class="widget-body">

                        <div class="panel-group smart-accordion-default" id="tender_resubmission_{{$tender->id}}_summary-accordion">
                            
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h2 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#tender_resubmission_{{$tender->id}}_summary-accordionn" href="#tender_resubmission_summary-accordion_{{$tender->id}}">
                                            <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> {{ (strlen($tendererDetails['tenderAlternativeTitle']) > 0) ? $tendererDetails['tenderAlternativeTitle']." ".trans('openTenderAwardRecommendation.summary') : trans('openTenderAwardRecommendation.summary') }}
                                        </a>
                                    </h2>
                                </div>
                            </div>

                            <div id="tender_resubmission_summary-accordion_{{$tender->id}}" class="panel-collapse collapse in">
                                <div class="panel-body no-padding">
                                
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="text-middle text-center" width="68px;">{{ trans('openTenderAwardRecommendation.rank') }}</th>
                                                <th rowspan="2" class="text-middle text-left">{{ trans('openTenderAwardRecommendation.tendererName') }}</th>
                                                <th rowspan="2" class="text-middle text-right" width="180px;">{{ trans('openTenderAwardRecommendation.previousTenderSum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                                <th rowspan="2" class="text-middle text-right" width="160px;">{{ trans('openTenderAwardRecommendation.adjustment') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                                <th rowspan="2" class="text-middle text-right" width="180px;">{{ trans('openTenderAwardRecommendation.revisedTenderSum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                                <th colspan="2" class="text-middle text-center">{{ trans('openTenderAwardRecommendation.varianceFromLowest') }}</th>
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
                                                    if($tendererDetail['revisedTenderSubmitted']) {
                                                        $varianceFromTheLowest = $tendererDetail['revisedTenderSum'] - $lowestTenderSum;
                                                        $varianceFromTheLowestPercentage = 0.00;

                                                        if(!empty($lowestTenderSum) && $lowestTenderSum != 0) {
                                                            $varianceFromTheLowestPercentage = round((($varianceFromTheLowest / $lowestTenderSum) * 100), 2);
                                                        }
                                                    }
                                                ?>
                                                <tr>
                                                    <td class="squeeze text-middle text-center">{{{ $number++ }}}</td>
                                                    <td class="text-middle text-left">{{{ $tendererDetail['tendererName'] }}}</td>
                                                    <td class="text-middle text-right">{{ $tendererDetail['originalTenderSumSubmitted'] ? formatAmount($tendererDetail['originalTenderSum']) : '-' }}</td>
                                                    <td class="text-middle text-right">{{ is_null($tendererDetail['adjustment']) ? '-' : formatAmount($tendererDetail['adjustment']) }}</td>
                                                    <td class="text-middle text-right">{{ $tendererDetail['revisedTenderSubmitted'] ? formatAmount($tendererDetail['revisedTenderSum']) : '-' }}</td>
                                                    <td class="text-middle text-right">{{ $tendererDetail['revisedTenderSubmitted'] ? formatAmount($varianceFromTheLowest) : '-' }}</td>
                                                    <td class="text-middle text-right">{{ $tendererDetail['revisedTenderSubmitted'] ? formatAmount($varianceFromTheLowestPercentage) : '-' }}</td>
                                                </tr>
                                            @endforeach
                                            <?php
                                                $originalTenderSummaryExists = !is_null($originalTenderSummary);
                                                $revisedTenderSummaryExists = !is_null($revisedTenderSummary);
                                                $bothExist  = $originalTenderSummaryExists && $revisedTenderSummaryExists;
                                            ?>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.consultantPTE') }}</td>
                                                <td class="text-middle text-right">{{ is_null($originalTenderSummary) ? '-' : formatAmount($originalTenderSummary->consultant_estimate) }}</td>
                                                <td class="text-middle text-right">{{ $bothExist ? (!is_null($originalTenderSummary->consultant_estimate) && (!is_null($revisedTenderSummary->consultant_estimate)) ? formatAmount($revisedTenderSummary->consultant_estimate - $originalTenderSummary->consultant_estimate) : '-') : '-' }}</td>
                                                <td class="text-middle text-right">{{ is_null($revisedTenderSummary) ? '-' : formatAmount($revisedTenderSummary->consultant_estimate) }}</td>
                                                <td class="text-middle text-right">{{ $revisedTenderSummaryExists ? (!is_null($revisedTenderSummary->consultant_estimate) ? formatAmount($revisedTenderSummary->consultant_estimate - $lowestTenderSum) : '-') : '-' }}</td>
                                                <?php
                                                    $consultantPtePercentage = '-';

                                                    if($revisedTenderSummaryExists && (!is_null($revisedTenderSummary->consultant_estimate)) && !empty($lowestTenderSum) && $lowestTenderSum != 0)
                                                    {
                                                        $consultantPtePercentage = formatAmount(round(((($revisedTenderSummary->consultant_estimate - $lowestTenderSum) / $lowestTenderSum) * 100), 2));
                                                    }
                                                ?>
                                                <td class="text-middle text-right">{{ $consultantPtePercentage }}</td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.budget') }}</td>
                                                <td class="text-middle text-right">{{ is_null($originalTenderSummary) ? '-' : formatAmount($originalTenderSummary->budget) }}</td>
                                                <td class="text-middle text-right">{{ $bothExist ? (!is_null($originalTenderSummary->budget) && (!is_null($revisedTenderSummary->budget)) ? formatAmount($revisedTenderSummary->budget - $originalTenderSummary->budget) : '-') : '-' }}</td>
                                                <td class="text-middle text-right">{{ is_null($revisedTenderSummary) ? '-' : formatAmount($revisedTenderSummary->budget) }}</td>
                                                <td class="text-middle text-right">{{ $revisedTenderSummaryExists ? (!is_null($revisedTenderSummary->budget) ? formatAmount($revisedTenderSummary->budget - $lowestTenderSum) : '-') : '-' }}</td>
                                                <?php
                                                    $budgetPercentage = '-';

                                                    if($revisedTenderSummaryExists && (!is_null($revisedTenderSummary->budget)) && !empty($lowestTenderSum) && $lowestTenderSum != 0)
                                                    {
                                                        $budgetPercentage = formatAmount(round(((($revisedTenderSummary->budget - $lowestTenderSum) / $lowestTenderSum) * 100), 2));
                                                    }
                                                ?>
                                                <td class="text-middle text-right">{{ $budgetPercentage }}</td>
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
                                                <input type="number" class="input-sm" min="0.0" max="999999999999999.00" step="0.01" class="text-left" name="budget" value="" required>
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