<div id="pte_vs_award_table" style="display:none;">
    <div class="jarviswidget " id="wid-id-0" data-widget-editbutton="false">
        <header>
            <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-dollar-sign"></i> </span>
            <h2>{{ trans('openTenderAwardRecommendation.pteVSaward') }}</h2>
        </header>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-middle text-center" style="width:64px;">{{ trans('openTenderAwardRecommendation.item') }}</th>
                        <th rowspan="2" class="text-middle text-left">{{ trans('openTenderAwardRecommendation.description') }}</th>
                        <th class="text-middle text-right">{{ trans('openTenderAwardRecommendation.consultantPTE') }}</th>
                        <th class="text-middle text-right">{{{ $pteVsAwardData['company_name'] }}}</th>
                    </tr>
                    <tr>
                        <th class="text-middle text-right" style="width:160px;">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}}</th>
                        <th class="text-middle text-right">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $number = 1; ?>
                    @if(!is_null($pteVsAwardData['billData']))
                        @foreach ($pteVsAwardData['billData'] as $billData)
                            <tr>
                                <td class="text-middle text-center squeeze">{{{ $number++ }}}</td>
                                <td class="text-middle text-left">{{{ $billData['description'] }}}</td>
                                <td class="text-middle text-right">{{ formatAmount($billData['consultant_pte']) }}</td>
                                <td class="text-middle text-right">{{ formatAmount($billData['billAmount']) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td>&nbsp;</td>
                        <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.totalAmount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</td>
                        <td class="text-middle text-right">{{ is_null($pteVsAwardData['billData']) ? '-' : formatAmount($pteVsAwardData['consultantEstimateTotal']) }}</td>
                        <td class="text-middle text-right">{{ is_null($pteVsAwardData['billData']) ? '-' : formatAmount($pteVsAwardData['billAmountTotal']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>