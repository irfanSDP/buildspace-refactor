<script type="text/javascript">
$(document).ready(function() {
    'use strict';

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
});
</script>