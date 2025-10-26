<div id="original_tender_summary_table" style="display:none;">
    <div class="jarviswidget " data-widget-editbutton="false">
        <header>
            <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-file-alt"></i> </span>
            <h2>{{ trans('openTenderAwardRecommendation.originalTenderSummary') }}</h2>
        </header>

        <div class="panel-group smart-accordion-default" id="original_tender_summary-accordion">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">
                        <a data-toggle="collapse" data-parent="#original_tender_summary-accordion" href="#original_tender_summary-accordion-1">
                            <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> {{ (strlen($originalTenderSummaryData['tenderAlternativeTitle']) > 0) ? $originalTenderSummaryData['tenderAlternativeTitle']." ".trans('openTenderAwardRecommendation.summary') : trans('openTenderAwardRecommendation.summary') }}
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
                                    <th rowspan="2" class="text-middle text-center" width="160px;">{{ trans('openTenderAwardRecommendation.completionPeriod') }} ({{{ $originalTenderSummaryData['completionPeriodMetric'] }}})</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-right" width="160px;">{{ trans('openTenderAwardRecommendation.amount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                    <th class="text-middle text-right" width="98px;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $number = 1; ?>
                                @foreach ($originalTenderSummaryData['details'] as $tendererDetail)
                                <?php 
                                    if($tendererDetail['submitted'])
                                    {
                                        $varianceFromTheLowest = $tendererDetail['tenderSum'] - $originalTenderSummaryData['lowestTenderSum'];
                                        $varianceFromTheLowestPercentage = 0.00;

                                        if(!empty($originalTenderSummaryData['lowestTenderSum']) and $originalTenderSummaryData['lowestTenderSum'] != 0)
                                        {
                                            $varianceFromTheLowestPercentage = ($originalTenderSummaryData['lowestTenderSum']) ? round((($varianceFromTheLowest / $originalTenderSummaryData['lowestTenderSum']) * 100), 2) : 0;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td class="squeeze text-middle text-center">{{{ $number++ }}}</td>
                                    <td class="text-middle text-left">{{{ $tendererDetail['tendererName'] }}}</td>
                                    <td class="text-middle text-right">{{ $tendererDetail['submitted'] ? formatAmount($tendererDetail['tenderSum']) : '-' }}</td>
                                    <td class="text-middle text-right">{{ $tendererDetail['submitted'] ? formatAmount($varianceFromTheLowest) : '-' }}</td>
                                    <td class="squeeze text-middle text-right">{{ $tendererDetail['submitted'] ? formatAmount($varianceFromTheLowestPercentage) : '-' }}</td>
                                    <td class="squeeze text-middle text-center">{{ number_format($tendererDetail['completionPeriod'], 2, '.', ',') }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td>&nbsp;</td>
                                    <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.consultantPTE') }}</td>
                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->consultant_estimate)) ? '-' : formatAmount($tenderSummary->consultant_estimate) }}</td>
                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->consultant_estimate)) ? '-' : formatAmount($tenderSummary->consultant_estimate - $originalTenderSummaryData['lowestTenderSum']) }}</td>
                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->consultant_estimate) || empty($originalTenderSummaryData['lowestTenderSum']) || $originalTenderSummaryData['lowestTenderSum'] == 0) ? '-' : formatAmount(round(((($tenderSummary->consultant_estimate - $originalTenderSummaryData['lowestTenderSum']) / $originalTenderSummaryData['lowestTenderSum']) * 100), 2)) }}</td>
                                    <td class="text-middle text-center">{{ number_format($originalTenderSummaryData['completionPeriod'], 2, '.', ',') }}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.budget') }}</td>
                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->budget)) ? '-' : formatAmount($tenderSummary->budget) }}</td>
                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->budget)) ? '-' : formatAmount($tenderSummary->budget - $originalTenderSummaryData['lowestTenderSum']) }}</td>
                                    <td class="text-middle text-right">{{ (is_null($tenderSummary) || is_null($tenderSummary->budget) || empty($originalTenderSummaryData['lowestTenderSum']) || $originalTenderSummaryData['lowestTenderSum'] == 0) ? '-' : formatAmount(round(((($tenderSummary->budget - $originalTenderSummaryData['lowestTenderSum']) / $originalTenderSummaryData['lowestTenderSum']) * 100), 2)) }}</td>
                                    <td class="text-middle text-center">{{ number_format($originalTenderSummaryData['completionPeriod'], 2, '.', ',') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>

    </div>
</div>