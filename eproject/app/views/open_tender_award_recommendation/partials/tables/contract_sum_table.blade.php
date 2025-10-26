<div id="contract_sum_table" style="display:none;">
    <div class="jarviswidget " id="wid-id-0" data-widget-editbutton="false">
        <header>
            <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-money-check-alt"></i> </span>
            <h2>{{ trans('openTenderAwardRecommendation.contractSum') }}</h2>
        </header>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th class="text-middle text-center" style="width:64px;">{{ trans('openTenderAwardRecommendation.no') }}</th>
                        <th class="text-middle text-left">{{ trans('openTenderAwardRecommendation.description') }}</th>
                        <th class="text-middle text-right" style="width:180px;">{{ trans('openTenderAwardRecommendation.amount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $number = 1; ?>
                    @if (!is_null($contractSum['billData']))
                        @foreach ($contractSum['billData'] as $billData)
                            <tr>
                                <td class="text-middle text-center squeeze">{{{ $number++ }}}</td>
                                <td class="text-middle text-left">{{{ $billData['description'] }}}</td>
                                <td class="text-middle text-right">{{ formatAmount($billData['billAmount']) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <td>&nbsp;</td>
                    <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.totalContractSum') }}</td>
                    <td class="text-middle text-right">{{ is_null($contractSum['billData']) ? '-' : formatAmount($contractSum['billAmountTotal']) }}</td>
                </tbody>
            </table>
        </div>
    </div>
</div>