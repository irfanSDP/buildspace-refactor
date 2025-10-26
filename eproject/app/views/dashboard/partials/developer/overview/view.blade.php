<div class="row">
    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <header>
            <h2>Overall Budget vs Awarded Contract Sum & Variation Orders</h2>
        </header>
        
        <div class="no-padding">
            <div class="widget-body">
                <div id="overall-bar-chart-A-legend"></div>    
                <div id="overall-bar-chart-A" class="chart"></div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <div class="panel panel-blue bg-color-blue">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $overallBudget = ($projectInfo) ? $projectInfo['overall_budget'] : 0 ?>
                        <h5> <span class="txt-color-white">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif {{number_format($overallBudget, 2, '.', ',')}}</span> {{trans('tenders.budget')}} </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-orange bg-color-orange">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $awardedContractSum = ($projectInfo) ? $projectInfo['awarded_contract_sum'] : 0 ?>
                        <h5> <span class="txt-color-white">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif {{number_format($awardedContractSum, 2, '.', ',')}}</span> {{trans('tenders.awardedContractSum')}} </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-red bg-color-red">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $variationOrder = ($projectInfo) ? $projectInfo['variation_order'] : 0 ?>
                        <h5> <span class="txt-color-white">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif {{number_format($variationOrder, 2, '.', ',')}}</span> {{trans('contractManagement.variationOrder')}} </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-green bg-color-green">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $overrunAmount = ($projectInfo) ? $projectInfo['overrun_amount'] : 0 ?>
                        <?php $textColor = ($overrunAmount < 0) ? "red" : "white" ?>
                        <h5> <span class="txt-color-{{$textColor}}">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif {{number_format($overrunAmount, 2, '.', ',')}}</span> Saving / Overrun </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-blue bg-color-white">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $overrunPercentage = ($projectInfo) ? $projectInfo['overrun_percentage'] : 0 ?>
                        <?php $textColor = ($overrunPercentage < 0) ? "red" : "#000" ?>
                        <h5 style="color:#000;"> <span class="txt-color-{{$textColor}}">{{number_format($overrunPercentage, 2)}}%</span> Saving / Overrun (%) </h5>
                    </li>
                </ul>
            </div>
        </div>
    </article>
</div>
<div id="overview_widget-section-A" class="row">
    <article class="col col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-table"></i> </span> 
                <h2 class="hidden-mobile">Overall Budget Vs Awarded Contract Sum & Variation Orders According to Work Categories</h2>
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
        </div>
    </article>
</div>

<div id="overview_widget-section-B" class="row">
    <article class="col col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-chart-bar"></i> </span>
                <h2 class="hidden-mobile">Overall Saving/Overrun According to Work Categories</h2>
            </header>

            <div class="no-padding">
                <div class="widget-body">
                    <div id="bar-chart-B-legend"></div>    
                    <div id="bar-chart-B" class="chart"></div>
                </div>
            </div>
        </div>
    </article>
</div>

<div id="overview_widget-section-C" class="row">
    <article class="col col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-file-invoice-dollar"></i> </span>
                <h2 class="hidden-mobile">Overall Certified Payment</h2>
            </header>

            <div class="widget-body">
                <div class="smart-form" data-options="filter-options">
                    <section>
                        <div id="chart-C-toggle" class="inline-group">
                            <label class="control-label" for="subsidiaryFilter"><strong>{{trans('projects.year')}}</strong></label>
                            <select id="overall_certified_payment_year-select" name="year" class="form-control select2" data-action="filter" data-select-width="180px">
                                <option value="-1">{{trans('documentManagementFolders.all')}}</option>
                                @foreach($certifiedPaymentYears as $k => $year)
                                    <option value="{{ $year }}">{{$year}}</option>
                                @endforeach
                            </select>
                        </div>
                    </section>
                </div>

                <section id="chart-section-cost_vs_time">
                    <h5><i class="fa fa-chart-bar"></i> {{ trans('projects.costVsTime')}}</h5>
                    <div id="chart-C-cost_vs_time" class="chart">
                        <div class="text-middle text-center" style="padding-top:32px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="chart-section-cumulative_cost">
                    <h5><i class="fa fa-chart-line"></i> {{ trans('projects.accumulativeCost')}}</h5>
                    <div id="chart-C-cumulative_cost" class="chart">
                        <div class="text-middle text-center" style="padding-top:32px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </article>
</div>