<div class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <h2>Overall Main Contracts vs Sub Contracts (Top 5 Highest Main Contract Amount)</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="bar-chart-top-5-legend"></div>    
                    <div id="bar-chart-top-5" class="chart"></div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <header>
            <h2>Overall Main Contracts vs Sub Contracts</h2>
        </header>

        <div class="widget-body no-padding">
            <div id="bar-chart-A-legend"></div>    
            <div id="bar-chart-A" class="chart"></div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <div class="panel panel-blue bg-color-blue">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $mainContractSum = ($projectInfo) ? $projectInfo['main_contract_total_sum'] : 0 ?>
                        <h5> <span class="txt-color-white">@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif {{{number_format($mainContractSum, 2, '.', ',')}}}</span> Main Contracts </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-orange bg-color-orange">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $subContractSum = ($projectInfo) ? $projectInfo['sub_contract_total_sum'] : 0 ?>
                        <h5> <span class="txt-color-white">@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif {{{number_format($subContractSum, 2, '.', ',')}}}</span> Sub Contracts </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-green bg-color-green">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $profitAmount = $mainContractSum - $subContractSum ?>
                        <?php $textColor = ($profitAmount < 0) ? "red" : "white" ?>
                        <h5> <span class="txt-color-{{{$textColor}}}">@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif {{{number_format($profitAmount, 2, '.', ',')}}}</span> Profit </h5>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-blue bg-color-white">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <?php $profitPercentage = ($mainContractSum) ? ($profitAmount / $mainContractSum) * 100 : 0 ?>
                        <?php $textColor = ($profitPercentage < 0) ? "red" : "#000" ?>
                        <h5 style="color:#000;"> <span class="txt-color-{{{$textColor}}}">{{{number_format($profitPercentage, 2)}}}%</span> Profit (%) </h5>
                    </li>
                </ul>
            </div>
        </div>
    </article>
</div>

<div id="main_contractor_dashboard_widget-main_contracts" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" id="main-contractor_dashboard-main_contracts-list" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-list"></i> </span>
                <h2 class="hidden-mobile">List of Main Contracts</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="row no-space">
                        <div id="main_contracts-list-table" class="tabulator-no-border"></div>
                    </div>
                </div>
            </div>
        </div>
    </article>
</div>

<div id="main_contractor_dashboard_widget-A" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-chart-bar"></i> </span>
                <h2 class="hidden-mobile">Contracts Information </h2>
            </header>

            <div>
                <div class="widget-body no-padding">
                    <div id ="main_contractor_dashboard_widget_content-A" style="padding-top:6px;"></div>
                </div>
            </div>
        </div>
    </article>
</div>