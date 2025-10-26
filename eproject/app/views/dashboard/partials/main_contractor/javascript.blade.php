<?php $selectedCountryId = ($selectedCountry) ? $selectedCountry->id : -1?>
<script type="text/javascript">
$(document).ready(function() {
    'use strict';

    var widgets = ['main_contracts'];
    
    for (var i = 0; i < widgets.length; i++) {
        $('#main_contractor_dashboard_widget-'+widgets[i]).jarvisWidgets({
            grid : 'article',
            widgets : '.jarviswidget',
            buttonsHidden : false,
            toggleButton : true,
            toggleClass : 'fa fa-minus | fa fa-plus',
            toggleSpeed : 200,
            fullscreenButton : true,
            fullscreenClass : 'fa fa-expand | fa fa-compress',
            fullscreenDiff : 3,
            buttonOrder : '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%'
        });
    }

    <?php $highestMainContracts = ($projectInfo) ? array_values($projectInfo['highest_main_contracts']) : []?>

    var data = {{json_encode($highestMainContracts)}};
    var ticks = [], mainContractAmt = [], subContractAmt = [], profitAmt = [];
    $.each(data, function (idx, obj) {
        var title = (obj.title.length > 25) ? obj.title.substring(0,38) + '...' : obj.title;
        ticks.push([idx, title]);
        mainContractAmt.push([idx, obj.main_contract_total]);
        subContractAmt.push([idx, obj.sub_contract_total]);
        profitAmt.push([idx, obj.profit]);
    });

    var series = [{
        label: "Main Contract Amount",
        color: '#39a1f4',
        data: mainContractAmt,
        stack: 0,
        bars: {
            show: true,
            align: 'right',
            fill: 0.9,
            lineWidth: 0,
            barWidth: 0.4
        }
    },{
        label: "Sub Contract Amount",
        color: '#ffc241',
        data: subContractAmt,
        stack: 1,
        bars: {
            show: true,
            align: 'left',
            fill: 0.9,
            lineWidth: 0,
            barWidth: 0.4
        }
    },{
        label: "Profit",
        color: '#1dc9b7',
        data: profitAmt,
        lines: {show: true},
        points: {show: true}
    }];

    var options = {
        xaxis: {
            ticks: ticks
        },
        yaxis: {
            tickFormatter: function (v, axis) {
                var style = (parseFloat(v) < 0) ? 'style="color:red;"' : '';
                return "<span "+style+">@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif " + $.number(v, 2)+"</span>";
            }
        },
        tooltip: true,
        tooltipOpts: {
            cssClass: 'tooltip-inner',
            defaultTheme: false,
            content: function(label, x, y){
                return '<h4 style="font-size:12px;">'+label+':</h4> @if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif '+$.number(y, 2);
            },
            onHover: function (flotItem, $tooltipEl) {
                $('[rel=tooltip]').tooltip('hide');//competing with bootstrap tooltip so we need to hide it
            }
        },
        grid: {
            hoverable: true,
            clickable: true,
            tickColor: '#f2f2f2',
            borderWidth: 1,
            borderColor: '#f2f2f2'
        }
    };

    options.legend = {
        show: true,
        container:  $("#bar-chart-top-5-legend")
    }

    var placeholder = $("#bar-chart-top-5");
    $.plot(placeholder, series, options);

    <?php $mainContractSum = ($projectInfo) ? $projectInfo['main_contract_total_sum'] : 0?>
    <?php $subContractSum = ($projectInfo) ? $projectInfo['sub_contract_total_sum'] : 0?>
    <?php $profitAmount = $mainContractSum -  $subContractSum?>

    series = [{
        label: "Main Contracts",
        color: '#39a1f4',
        data: [[0, {{{$mainContractSum}}}]],
        bars: {
            show: true,
            align: 'center',
            fill: 0.9,
            lineWidth: 0,
            barWidth: 0.4
        }
    },{
        label: "Sub Contracts",
        color: '#ffc241',
        data: [[1, {{{$subContractSum}}}]],
        bars: {
            show: true,
            align: 'center',
            fill: 0.9,
            lineWidth: 0,
            barWidth: 0.4
        }
    },{
        label: "Profit",
        color: '#1dc9b7',
        data: [[2, {{{$profitAmount}}}]],
        bars: {
            show: true,
            align: 'center',
            fill: 0.9,
            lineWidth: 0,
            barWidth: 0.4
        }
    }];

    options.legend = {
        show: false
    }

    options.xaxis.ticks = [[
        0, 'Main Contracts',
    ],[
        1, 'Sub Contracts',
    ],[
        2, 'Profit'
    ]];

    $.plot( $("#bar-chart-A"), series, options);

    var mainContractListTbl = new Tabulator("#main_contracts-list-table", {
        ajaxURL: "{{ route('dashboard.main.contracts.ajax',[$selectedCountryId, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 360,
        tooltips:true,
        ajaxFiltering:true,
        pagination:"remote",
        paginationSize:10,
        dataLoaded: function (data) {
            $('#main_contractor_dashboard_widget_content-A').empty();
            $.each(data, function (idx, obj) {
                
                var elemStr = '<article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">'
                    +'<div class="jarviswidget" style="padding:0;" data-widget-colorbutton="false" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false">'
                    +'<div style="overflow-y:hidden;">'
                    +'<div class="widget-body no-padding">'
                    +'<div class="widget-body-toolbar row"><div class="col-xs-6 col-sm-9 col-md-9 col-lg-9 well">'+obj.project_title+'</div> <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3 text-right"><label>Contract Amount</label> <p>@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif '+$.number(obj.contract_value, 2)+'</p></div></div>';

                if(obj.subsidiaries.length){
                    elemStr += '<div id="'+idx+'-main_contractor-tbl-list">'
                    +'<table id="main_contractor-main_contracts-list-tbl-'+idx+'">'
                    +'<thead><tr><th>Sub Contract Title</th><th tabulator-width="180" tabulator-cssClass="text-center text-right">Contract Amount @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif</th></tr></thead><tbody>';
                
                    for(var k=0;k<obj.subsidiaries.length;k++){
                        elemStr += '<tr><td>'+obj.subsidiaries[k].project_title+'</td><td>'+$.number(obj.subsidiaries[k].contract_value ,2)+'</td></tr>';
                    }

                    elemStr += '</tbody></table></div>';
                }
                
                var val = parseFloat(obj.profit_amount);
                var profitAmt = (val < 0) ? '<span class="txt-color-red">@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif'+$.number(val, 2)+'</span>' : '<span class="txt-color-blue">@if($selectedCountry) {{{$selectedCountry->currency_code}}} @endif'+$.number(val, 2)+'</span>';
                var pct = parseFloat(obj.profit_percentage);
                var profitPct = (pct < 0) ? '<span class="txt-color-red">'+$.number(pct, 2)+'%</span>' : '<span class="txt-color-purple">'+$.number(pct, 2)+'%</span>';

                elemStr += '<div class="widget-footer" style="border-top:none;">'
                    +'<div id="bar-chart-main_contracts_list-'+idx+'" class="chart" style="height:120px;"></div>'
                    +'<ul id="sparks">'
                    +'<li class="sparks-info" style="overflow:inherit;"><h5> Profit @if($selectedCountry) ({{{$selectedCountry->currency_code}}}) @endif '+profitAmt+'</h5></li>'
                    +'<li class="sparks-info" style="overflow:inherit;"><h5> Profit % '+profitPct+'</h5></li>'
                    +'</ul>'
                    +'</div>'
                    +'</div>'
                    +'</div>'
                    +'</div>'
                    +'</article>';

                $('#main_contractor_dashboard_widget_content-A').append(elemStr);
                
                if(obj.subsidiaries.length){
                    setTimeout(function(){
                        if($('#main_contractor-main_contracts-list-tbl-'+idx).length && $('#main_contractor-main_contracts-list-tbl-'+idx).is(':visible'))
                            var table = new Tabulator('#main_contractor-main_contracts-list-tbl-'+idx, {
                                layout:"fitColumns",
                                placeholder: "{{ trans('general.noMatchingResults') }}",
                                height: 180
                            });
                    },100);
                }

                series = [{
                    label: "Main Contracts",
                    color: '#39a1f4',
                    data: [[0, obj.contract_value]],
                    bars: {
                        show: true,
                        align: 'center',
                        fill: 0.9,
                        barWidth: 0.2
                    }
                },{
                    label: "Sub Contracts",
                    color: '#ffc241',
                    data: [[1, obj.sub_contract_total]],
                    bars: {
                        show: true,
                        align: 'center',
                        fill: 0.9,
                        barWidth: 0.2
                    }
                },{
                    label: "Profit",
                    color: '#1dc9b7',
                    data: [[2, obj.profit_amount]],
                    bars: {
                        show: true,
                        align: 'center',
                        fill: 0.9,
                        barWidth: 0.2
                    }
                }];

                options.xaxis.ticks = [[
                    0, 'Main Contracts',
                ],[
                    1, 'Sub Contracts',
                ],[
                    2, 'Profit'
                ]];

                $.plot( $("#bar-chart-main_contracts_list-"+idx), series, options);
            });

        },
        columns: [{
            title:"No.", field:'rownum', cssClass:"text-center text-middle", width: 12, headerSort:false
        },{
            title:"{{ trans('projects.title') }}", field: 'project_title', cssClass:"text-center text-left", minWidth:220, headerSort:false, formatter:"textarea", headerFilter:"input", headerFilterPlaceholder:"{{trans('dashboard.filterSubsidiaries')}}"
        },{
            title:"{{ trans('projects.reference') }}", field: 'reference', cssClass:"text-center text-middle", width: 210, headerSort:false, headerFilter:"input", headerFilterPlaceholder:"{{trans('dashboard.filterCompanies')}}"
        }]
    });
});

</script>