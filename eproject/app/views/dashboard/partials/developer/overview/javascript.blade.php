<script type="text/javascript">
$(document).ready(function() {
    'use strict';

    var widgets = ['A', 'B', 'C'];
    for (var i = 0; i < widgets.length; i++) {
        $('#overview_widget-section-'+widgets[i]).jarvisWidgets({
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

    var options = {
        series: {
            stack: true,
            bars: {
                show: true,
                fill: 0.9,
                lineWidth: 0,
                align: 'center',
                barWidth: 0.4
            }
        },
        xaxis: {
            ticks: []
        },
        yaxis: {
            tickFormatter: function (v, axis) {
                var style = (parseFloat(v) < 0) ? 'style="color:red;"' : '';
                return "<span "+style+">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif " + $.number(v, 2)+"</span>";
            }
        },
        legend: {
            container:null,
            noColumns: 0
        },
        tooltip: true,
        tooltipOpts: {
            cssClass: 'tooltip-inner',
            defaultTheme: false,
            content: function(label, x, y){
                return '<h4 style="font-size:12px;">'+label+':</h4> @if($selectedCountry) {{$selectedCountry->currency_code}} @endif '+$.number(y, 2);
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

    options.xaxis.ticks = [[0, "Budget"], [1, "Awarded Contract Sum & VO"]];;
    options.legend.container = $("#overall-bar-chart-A-legend");

    <?php $projectOverallBudget = ($projectInfo) ? $projectInfo['overall_budget'] : 0;?>
    <?php $projectAwardedContractSum = ($projectInfo) ? $projectInfo['awarded_contract_sum'] : 0;?>
    <?php $projectVariationOrder = ($projectInfo) ? $projectInfo['variation_order'] : 0;?>
    $.plot($("#overall-bar-chart-A"), [{
        label: '{{trans('tenders.budget')}}',
        color: '#39a1f4',
        data: [[0, {{$projectOverallBudget}}], [1, 0]]
    },{
        label: '{{trans('tenders.awardedContractSum')}}',
        color: '#ffc241',
        data: [[0, 0], [1, {{$projectAwardedContractSum}}]]
    },{
        label: '{{trans('contractManagement.variationOrder')}}',
        color: '#fd3995',
        data: [[0, 0], [1, {{$projectVariationOrder}}]]
    }],
    options);

    var ticks = [],
    budgetData = [],
    contractSumData = [],
    variationOrderData = [],
    data = {{json_encode($overallBudgetRecords)}};
    
    $.each(data, function (idx, obj) {
        ticks.push([idx, obj.name]);
        budgetData.push([idx, obj.overall_budget]);
        contractSumData.push([idx, obj.awarded_contract_sum]);
        variationOrderData.push([idx, obj.variation_order]);
    });

    var series = [{
        'label': 'Overall Budget',
        'color': '#39a1f4',
        'data': budgetData,
        'stack': 0,
        'bars': {
            'align': 'right'
        }
    },{
        'label': 'Awarded Contract Sum',
        'color': '#ffc241',
        'data': contractSumData,
        'stack': 1,
        'bars': {
            'align': 'left'
        }
    },{
        'label': 'Variation Orders',
        'color': '#fd3995',
        'data': variationOrderData,
        'stack': 1,
        'bars': {
            'align': 'left'
        }
    }];

    options.xaxis.ticks = ticks;
    options.legend.container = $("#bar-chart-A-legend");

    $.plot($("#bar-chart-A"), series, options);

    series = [];

    $.each(data, function (idx, obj) {
        var color = '#2196F3';
        if((idx in default_colors)){
            var k = Object.keys(default_colors[idx]);
            color = (k[0]!='white') ? default_colors[idx][k[0]] : '#2196F3';
        }
        var overrunAmount = obj.overall_budget - (obj.awarded_contract_sum + obj.variation_order);
        series.push({
            label: obj.name,
            color: color,
            data: [[idx, overrunAmount]]
        });
    });
    

    var horizontal_options = {
        series: {
            bars: {
                show: true,
                fill: 0.9,
                barWidth:0.35,
                align: "center"
            },
        },
        yaxis:{
            tickFormatter: function (v, axis) {
                var style = (parseFloat(v) < 0) ? 'style="color:red;"' : '';
                return "<span "+style+">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif " + $.number(v, 2)+"</span>";
            }
        },
        xaxis: { show: false },
        tooltip: true,
        tooltipOpts: {
            cssClass: 'tooltip-inner',
            defaultTheme: false,
            content: function(label, x, y){
                return '<h4 style="font-size:12px;">'+label+':</h4> @if($selectedCountry) {{$selectedCountry->currency_code}} @endif '+$.number(y, 2);
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
            borderColor: '#f2f2f2',
            markings: { color: "#f2f2f2", yaxis: { to: 0 } }
        },
        legend:{
            container: $("#bar-chart-B-legend"),
            noColumns: 0
        }

    };

    $.plot($("#bar-chart-B"), series, horizontal_options);

    @if($selectedCountry)
    $('#overall_certified_payment_year-select').on("select2:select", function(e) {
        function onDataReceived(series) {
            var options = {
                grid : {
                    hoverable : true
                },
                xaxis: {
                    mode: "categories",
                    tickLength: 0
                },
                yaxis : {
                    tickFormatter: function (v, axis) {
                        var style = (parseFloat(v) < 0) ? 'style="color:red;"' : '';
                        return "<span "+style+">@if($selectedCountry) {{$selectedCountry->currency_code}} @endif " + $.number(v, 2)+"</span>";
                    }
                },
                legend: {
                    container:null,
                    noColumns: 0
                },
                tooltip: true,
                tooltipOpts: {
                    cssClass: 'tooltip-inner',
                    defaultTheme: false,
                    content: function(label, x, y){
                        return '<h4 style="font-size:12px;">'+label+':</h4> @if($selectedCountry) {{$selectedCountry->currency_code}} @endif '+$.number(y, 2);
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

            $.plot($("#chart-C-cumulative_cost"), [{
                label : "{{ trans('projects.accumulativeCost')}}",
                data : series.cumulative_cost,
                color: "#1dc9b7",
                points: {
                    symbol: "circle",
                    fillColor: "#1dc9b7",
                    show: true
                },
                lines: {
                    show: true,
                    fill: true
                }
            }], options);

            $.plot($("#chart-C-cost_vs_time"), [{
                label : "{{ trans('projects.costVsTime')}}",
                data : series.cost_vs_time,
                color: '#2196F3',
                bars: {
                    show: true,
                    fill: 0.9,
                    lineWidth: 0,
                    align: 'center',
                    barWidth: 0.4
                }
            }], options);
        }

        var url = '{{ route("dashboard.overall.certified.payment.ajax", [$selectedCountry->id, ":year", $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}';
        url = url.replace(':year', $(this).val());

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            beforeSend: function() {
                var loader = '<div class="text-middle text-center" style="padding-top:32px;">'
                +'<div class="spinner-border text-primary" role="status">'
                +'<span class="sr-only">Loading...</span>'
                +'</div>'
                +'</div>';
                $('#chart-C-cost_vs_time').html(loader);
                $('#chart-C-cumulative_cost').html(loader);
            },
            success: onDataReceived
        });
    });

    $('#overall_certified_payment_year-select').val('-1').trigger('select2:select');
    @endif
});
</script>