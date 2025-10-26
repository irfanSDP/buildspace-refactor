<div class="row">
    <article class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <header>
            <h2>Overall Budget Vs Awarded Contract Sum & Variation Orders According to Work Categories</h2>
        </header>
        
        <div class="no-padding">
            <div class="widget-body">
                <div id="bar-chart-A-legend"></div>    
                <div id="bar-chart-A" class="chart"></div>
            
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="min-width:280px;">{{trans('projects.workCategory')}}</th>
                                <th class="text-middle text-right" style="width:160px;">{{trans('tenders.budget')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif</th>
                                <th class="text-middle text-right" style="width:200px;">{{trans('tenders.awardedContractSum')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif</th>
                                <th class="text-middle text-right" style="width:160px;">{{trans('contractManagement.variationOrder')}}  @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif</th>
                                <th class="text-middle text-right" style="width:160px;">Saving/Overrun @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif</th>
                                <th class="text-middle text-right" style="width:140px;">Saving/Overrun (%)</th>
                            </tr>
                            <tbody>
                            <?php
                            $totalBudget = 0;
                            $totalContractSum = 0;
                            $totalVariationOrder = 0;
                            $totalOverrun = 0;
                            ?>
                            @if(count($overallBudgetRecords))
                                @foreach($overallBudgetRecords as $record)
                                <tr>
                                    <td>{{$record['name']}}</td>
                                    <td class="text-middle text-right">{{number_format($record['overall_budget'], 2, '.', ',')}}</td>
                                    <td class="text-middle text-right">{{number_format($record['awarded_contract_sum'], 2, '.', ',')}}</td>
                                    <td class="text-middle text-right">{{number_format($record['variation_order'], 2, '.', ',')}}</td>
                                    <td class="text-middle text-right">
                                    <?php
                                    $overrunAmount = $record['overall_budget'] - ($record['awarded_contract_sum'] + $record['variation_order']);

                                    $totalBudget += $record['overall_budget'];
                                    $totalContractSum += $record['awarded_contract_sum'];
                                    $totalVariationOrder += $record['variation_order'];
                                    $totalOverrun += $overrunAmount;
                                    ?>
                                    @if($overrunAmount < 0)
                                    <span class="badge bg-color-red">{{number_format($overrunAmount, 2, '.', ',')}}</span>
                                    @else
                                    {{number_format($overrunAmount, 2, '.', ',')}}
                                    @endif
                                    </td>
                                    <td class="text-middle text-right">
                                    <?php
                                    $overrunPercentage = ($record['overall_budget']) ? $overrunAmount / $record['overall_budget'] * 100 : 0;
                                    ?>
                                    @if($overrunPercentage < 0)
                                    <span class="badge bg-color-red">{{number_format($overrunPercentage, 2, '.', ',')}} %</span>
                                    @else
                                    {{number_format($overrunPercentage, 2, '.', ',')}} %
                                    @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="6" class="text-middle text-center alert-warning">
                                    {{trans('general.noDataAvailable')}}
                                </td>
                            </tr>
                            @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>{{trans('finance.totalAmount')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif</td>
                                    <td class="text-middle text-right">{{number_format($totalBudget, 2, '.', ',')}}</td>
                                    <td class="text-middle text-right">{{number_format($totalContractSum, 2, '.', ',')}}</td>
                                    <td class="text-middle text-right">{{number_format($totalVariationOrder, 2, '.', ',')}}</td>
                                    <td class="text-middle text-right">
                                    @if($totalOverrun < 0)
                                    <span class="badge bg-color-red">{{number_format($totalOverrun, 2, '.', ',')}}</span>
                                    @else
                                    {{number_format($totalOverrun, 2, '.', ',')}}
                                    @endif
                                    </td>
                                    <td class="text-middle text-right">
                                    <?php
                                    $totalOverrunPercentage = ($totalBudget) ? $totalOverrun / $totalBudget * 100 : 0;
                                    ?>
                                    @if($totalOverrunPercentage < 0)
                                    <span class="badge bg-color-red">{{number_format($totalOverrunPercentage, 2, '.', ',')}} %</span>
                                    @else
                                    {{number_format($totalOverrunPercentage, 2, '.', ',')}} %
                                    @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </article>
</div>

<div class="row">
    <article class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <div>
                <div class="widget-body no-padding smart-form">
                    <div class="text-middle text-center" style="padding-top:8px;">
                        <h5>Overall Saving/Overrun According to Work Categories</h5>
                        <div id="bar-chart-B-legend"></div>    
                        <div id="bar-chart-B" class="chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </article>
</div>