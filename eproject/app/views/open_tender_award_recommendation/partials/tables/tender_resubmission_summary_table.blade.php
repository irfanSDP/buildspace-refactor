<div id="{{{'tender_resubmission_' . $tenderResubmission->count . '_summary_table'}}}" style="display:none;">
    <div class="jarviswidget " data-widget-editbutton="false">
        <header>
            <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-file-alt"></i> </span>
            <h2>{{ trans('openTenderAwardRecommendation.tenderResubmission') . ' ' . $t->count . ' ' . trans('openTenderAwardRecommendation.summary') }}</h2>
        </header>

        <div class="panel-group smart-accordion-default" id="tender_resubmission_{{$t->count}}_summary-accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">
                        <a data-toggle="collapse" data-parent="#tender_resubmission_{{$t->count}}_summary-accordion" href="#tender_resubmission_summary-accordion_{{$t->count}}">
                            <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> {{ (strlen($tenderResubmissionData['tenderAlternativeTitle']) > 0) ? $tenderResubmissionData['tenderAlternativeTitle']." ".trans('openTenderAwardRecommendation.summary') : trans('openTenderAwardRecommendation.summary') }}
                        </a>
                    </h2>
                </div>
                <div id="tender_resubmission_summary-accordion_{{$t->count}}" class="panel-collapse collapse in">
                    <div class="panel-body no-padding">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-middle text-center" style="width:64px;">{{ trans('openTenderAwardRecommendation.rank') }}</th>
                                    <th rowspan="2" class="text-middle text-left">{{ trans('openTenderAwardRecommendation.tendererName') }}</th>
                                    <th rowspan="2" class="text-middle text-right" style="width:180px;">{{ trans('openTenderAwardRecommendation.previousTenderSum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                    <th rowspan="2" class="text-middle text-right" style="width:160px;">{{ trans('openTenderAwardRecommendation.adjustment') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                    <th rowspan="2" class="text-middle text-right" style="width:180px;">{{ trans('openTenderAwardRecommendation.revisedTenderSum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                    <th colspan="2" class="text-middle text-center">{{ trans('openTenderAwardRecommendation.varianceFromLowest') }}</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-right" width="160px;">{{ trans('openTenderAwardRecommendation.amount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                    <th class="text-middle text-right" width="98px;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $number = 1; ?>
                                @foreach ($tenderResubmissionData['details'] as $tendererDetail)
                                    <?php
                                        if($tendererDetail['revisedTenderSubmitted']) {
                                            $varianceFromTheLowest = $tendererDetail['revisedTenderSum'] - $tenderResubmissionData['lowestTenderSum'];
                                            $varianceFromTheLowestPercentage = 0.00;

                                            if(!empty($tenderResubmissionData['lowestTenderSum']) && $tenderResubmissionData['lowestTenderSum'] != 0) {
                                                $varianceFromTheLowestPercentage = round((($varianceFromTheLowest / $tenderResubmissionData['lowestTenderSum']) * 100), 2);
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
                                    <td class="text-middle text-right">{{ $revisedTenderSummaryExists ? (!is_null($revisedTenderSummary->consultant_estimate) ? formatAmount($revisedTenderSummary->consultant_estimate - $tenderResubmissionData['lowestTenderSum']) : '-') : '-' }}</td>
                                    <?php
                                        $consultantPtePercentage = '-';

                                        if($revisedTenderSummaryExists && (!is_null($revisedTenderSummary->consultant_estimate)) && !empty($tenderResubmissionData['lowestTenderSum']) && $tenderResubmissionData['lowestTenderSum'] != 0)
                                        {
                                            $consultantPtePercentage = formatAmount(round(((($revisedTenderSummary->consultant_estimate - $tenderResubmissionData['lowestTenderSum']) / $tenderResubmissionData['lowestTenderSum']) * 100), 2));
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
                                    <td class="text-middle text-right">{{ $revisedTenderSummaryExists ? (!is_null($revisedTenderSummary->budget) ? formatAmount($revisedTenderSummary->budget - $tenderResubmissionData['lowestTenderSum']) : '-') : '-' }}</td>
                                    <?php
                                        $budgetPercentage = '-';
                                        
                                        if($revisedTenderSummaryExists && (!is_null($revisedTenderSummary->budget)) && !empty($tenderResubmission['lowestTenderSum']) && $tenderResubmission['lowestTenderSum'] != 0)
                                        {
                                            $budgetPercentage = formatAmount(round(((($revisedTenderSummary->budget - $tenderResubmissionData['lowestTenderSum']) / $tenderResubmissionData['lowestTenderSum']) * 100), 2));
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