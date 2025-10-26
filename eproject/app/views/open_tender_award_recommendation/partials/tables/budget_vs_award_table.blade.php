<div id="budget_vs_award_table" style="display:none;">
    <div class="jarviswidget " data-widget-editbutton="false">
        <header>
            <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-hand-holding-usd"></i> </span>
            <h2>{{ trans('openTenderAwardRecommendation.budgetVSaward') }}</h2>
        </header>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th class="text-middle text-center" style="width:64px;">{{ trans('openTenderAwardRecommendation.item') }}</th>
                        <th class="text-middle text-left">{{ trans('openTenderAwardRecommendation.description') }}</th>
                        <th class="text-middle text-right" style="width:160px;">{{ trans('openTenderAwardRecommendation.budget') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                        <th class="text-middle text-right" style="width:160px;">{{ trans('openTenderAwardRecommendation.award') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                        <th class="text-middle text-right" style="width:180px;">{{ trans('openTenderAwardRecommendation.savings') }} / ({{ trans('openTenderAwardRecommendation.overrun') }}) ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                        <th class="text-middle text-right" style="width:120px;">{{ trans('openTenderAwardRecommendation.variance') }} (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $number = 1; ?>
                    @if (!is_null($budgetVsAwardData['billData']))
                        @foreach ($budgetVsAwardData['billData'] as $billData)
                            <tr>
                                <td class="text-middle text-center squeeze">{{{ $number++ }}}</td>
                                <td class="text-middle text-left">{{{ $billData['description'] }}}</td>
                                <td class="text-middle text-right">{{ formatAmount($billData['budget']) }}</td>
                                <td class="text-middle text-right">{{ formatAmount($billData['billAmount']) }}</td>
                                <td class="text-middle text-right">{{ (empty($billData['budget'])) ? '-' : formatAmount($billData['budget'] - $billData['billAmount']) }}</td>
                                <td class="text-middle text-right">{{ (empty($billData['budget']) || $billData['budget'] == 0) ? '-' : formatAmount((($billData['budget'] - $billData['billAmount']) / $billData['budget']) * 100) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td>&nbsp;</td>
                        <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.totalAmount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</td>
                        <td class="text-middle text-right">{{ is_null($budgetVsAwardData['billData']) ? '-' : formatAmount($budgetVsAwardData['budgetTotal']) }}</td>
                        <td class="text-middle text-right">{{ is_null($budgetVsAwardData['billData']) ? '-' : formatAmount($budgetVsAwardData['billAmountTotal']) }}</td>
                        <td class="text-middle text-right">{{ empty($budgetVsAwardData['budgetTotal']) ? '-' : formatAmount($budgetVsAwardData['budgetTotal'] - $budgetVsAwardData['billAmountTotal']) }}</td>
                        <td class="text-middle text-right">{{ (empty($budgetVsAwardData['budgetTotal']) || $budgetVsAwardData['budgetTotal'] == 0) ? '-' : formatAmount((($budgetVsAwardData['budgetTotal'] - $budgetVsAwardData['billAmountTotal']) / $budgetVsAwardData['budgetTotal']) * 100) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>