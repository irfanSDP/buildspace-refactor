
<?php $selectedCountryId = ($selectedCountry) ? $selectedCountry->id : -1?>
<script type="text/javascript">
$(document).ready(function() {
    'use strict';

    var widgets = ['subsidiaries', 'B', 'C', 'D', 'E'];
    
    for (var i = 0; i < widgets.length; i++) {
        $('#developer_dashboard_widget-'+widgets[i]).jarvisWidgets({
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

    var dashboardBTbl,
        dashboardCTbl;

@if($subsidiaryProjectCount)
    dashboardBTbl = new Tabulator("#dashboard-B-table", {
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 300,
        tooltips:true,
        dataLoaded: function (data) {
            var ticks = [],
                budgetData = [],
                contractSumData = [],
                variationOrderData = [];

            $.each(data, function (idx, obj) {
                ticks.push([idx, obj.name]);
                budgetData.push([idx, obj.overall_budget]);
                contractSumData.push([idx, obj.awarded_contract_sum]);
                variationOrderData.push([idx, obj.variation_order]);
            });

            var series = [{
                'label': '{{trans('tenders.budget')}}',
                'color': '#39a1f4',
                'data': budgetData,
                'stack': 0,
                'bars': {
                    'align': 'right'
                }
            },{
                'label': '{{trans('tenders.awardedContractSum')}}',
                'color': '#ffc241',
                'data': contractSumData,
                'stack': 1,
                'bars': {
                    'align': 'left'
                }
            },{
                'label': '{{trans('contractManagement.variationOrder')}}',
                'color': '#fd3995',
                'data': variationOrderData,
                'stack': 1,
                'bars': {
                    'align': 'left'
                }
            }];

            options.xaxis.ticks = ticks;
            options.legend.container = $("#bar-chart-B-legend");

            setTimeout(function(){
                if($("#bar-chart-B").length && $("#bar-chart-B").is(':visible'))
                    $.plot($("#bar-chart-B"), series, options);
            },100);
        },
        columns: [{
            title:"{{trans('projects.subsidiary')}}", field: 'name', cssClass:"text-center text-left", minWidth:240, headerSort:false, formatter:"textarea"
        },{
            title:"{{trans('tenders.budget')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'overall_budget', cssClass:"text-right text-middle", width: 120, headerSort:false, formatter:"money"
        },{
            title:"{{trans('tenders.awardedContractSum')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'awarded_contract_sum', cssClass:"text-right text-middle", width: 180, headerSort:false, formatter:"money"
        },{
            title:"{{trans('contractManagement.variationOrder')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'variation_order', cssClass:"text-right text-middle", width: 140, headerSort:false, formatter:"money"
        },{
            title:"Saving/Overrun @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'overrun_amount', cssClass:"text-right text-middle", width: 160, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
            formatterParams: {
                innerHtml: [{
                    innerHtml: function(data){
                        var val = parseFloat(data.overrun_amount);
                        if(val < 0){
                            return '<span class="badge bg-color-red">'+$.number(val, 2)+'</span>';
                        }
                        return $.number(val, 2);
                    }
                }]
            }
        },{
            title:"Saving/Overrun (%)", field: 'overrun_percentage', cssClass:"text-center text-middle", width: 140, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
            formatterParams: {
                innerHtml: [{
                    innerHtml: function(data){
                        var val = parseFloat(data.overrun_percentage);
                        if(val < 0){
                            return (data.overall_budget) ? '<span class="badge bg-color-red">'+$.number(val, 2)+' %</span>' : 0.00 + " %";
                        }
                        return $.number(val, 2) + " %";
                    }
                }]
            }
        }]
    });

    @if($workCategories)
    var workCategories = {{ json_encode($workCategories) }}
    $.each(workCategories, function (idx, obj){
        if((idx in default_colors)){
            var k = Object.keys(default_colors[idx]);
            workCategories[idx]['color'] = (k[0]!='white') ? default_colors[idx][k[0]] : '#2196F3';
        }else{
            workCategories[idx]['color'] = '#2196F3';
        }
    });
    dashboardCTbl = new Tabulator("#work_categories-info-table", {
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 328,
        tooltips:true,
        columnHeaderVertAlign:"bottom",
        dataLoaded: function (data) {
            $('#developer_dashboard_widget_content-C').empty();
            $.each(data, function (idx, obj) {
                var series = [];
                var cnt = 0;

                var bootstrapGridColum = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
                if(data.length > 1 && data.length <= 4){
                    bootstrapGridColum = 'col-xs-12 col-sm-6 col-md-6 col-lg-6';
                }else if(data.length > 4){
                    bootstrapGridColum = 'col-xs-12 col-sm-6 col-md-4 col-lg-4';
                }

                $.map(Object.keys(obj), function(val, i) {
                    
                    if (val.indexOf("_overrun_amount") != -1) {
                        
                        var id = val.split('_overrun_amount')[0];
                        
                        var matched = false;
                        for (var index = 0; index < series.length; ++index) {
                            var workCategory = series[index];
                            if(workCategory.id == id){
                                matched = true;
                                break;
                            }
                        }

                        if(!matched && workCategories.some(item => item.id == id) && parseFloat(obj[val]) != 0){
                            var wc = workCategories.filter(item => item.id == id)[0];
                            series.push({
                                id: id,
                                label: wc.name,
                                color: wc.color,
                                data: []
                            });
                        }

                        if(parseFloat(obj[val]) != 0){
                            for (var idx = 0; idx < series.length; ++idx) {
                                var seriesData = series[idx];
                                if(seriesData.id == id){
                                    series[idx].data.push([cnt, obj[val]]);
                                    cnt++
                                    break;
                                }
                            }

                        }
                    }
                });
                if(series.length){
                    $('#developer_dashboard_widget_content-C').append('<article class="'+bootstrapGridColum+'">'
                    +'<div class="jarviswidget" data-widget-colorbutton="false" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false">'
                    +'<div style="overflow-y:hidden;">'
                    +'<div class="widget-body" style="width:380px;">'
                    +'<div class="row">'
                    +'<div class="col col-xs-12">'
                    +'<div class="well">'+DOMPurify.sanitize(obj.name)+'</div>'
                    +'<div id="'+idx+'-bar-chart-C-legend"></div>'
                    +'<div id="'+idx+'-bar-chart-C" class="chart"></div>'
                    +'</div>'
                    +'</div>'
                    +'</div>'
                    +'</div>'
                    +'</div>'
                    +'</article>');

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
                           container: $("#"+idx+"-bar-chart-C-legend"),
                           noColumns: 0
                        }

                    };

                    setTimeout(function(){
                        var plot = $.plot($("#"+idx+"-bar-chart-C"), series, horizontal_options);
                    },100);
                }
               
            });
        },
        columns:[
            {title:"{{trans('projects.subsidiary')}}", field:"name", cssClass:"text-center text-left", minWidth:480, headerSort:false, formatter:"textarea"},
            @foreach($overrunWorkCategories as $idx => $workCategory)
            {
                title:"{{{$workCategory->name}}}",
                cssClass:"text-center text-middle",
                headerSort:false,
                columns:[
                {title:"@if($selectedCountry) {{$selectedCountry->currency_code}} @else - @endif", field:"{{$workCategory->id}}_overrun_amount", cssClass:"text-right text-middle", sorter:"number", width: 140, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        innerHtml: [{
                            innerHtml: function(data){
                                var val = parseFloat(data['{{$workCategory->id}}_overrun_amount']);
                                if(val < 0){
                                    return '<span class="badge bg-color-red">'+$.number(val, 2)+'</span>';
                                }
                                return (val) ? $.number(val, 2) : "";
                            }
                        }]
                    }
                },
                {title:"%", field:"{{$workCategory->id}}_overrun_percentage", cssClass:"text-right text-middle", width: 80, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        innerHtml: [{
                            innerHtml: function(data){
                                var val = parseFloat(data['{{$workCategory->id}}_overrun_percentage']);
                                if(val < 0){
                                    return '<span class="badge bg-color-red">'+$.number(val, 2)+' %</span>';
                                }
                                return (val) ? $.number(val, 2) + " %" : "";
                            }
                        }]
                    }
                }]
            }@if($idx==($overrunWorkCategories->count()-1)) @else , @endif

            @endforeach
        ]
    });
    @endif

@endif

    var tabulatorSelectedIndexes = [],
        updatingTabulatorSelections = false;

    var subsidairyListTbl = new Tabulator("#subsidairies-list-table", {
        ajaxURL: "{{ route('dashboard.subsidiaries.ajax',[$selectedCountryId, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 280,
        tooltips:true,
        dataTree:true,
        dataTreeStartExpanded:true,
        dataTreeSelectPropagate:true,
        dataTreeChildColumnCalcs:true,
        dataTreeExpandElement:"<i class='fa fa-sm fa-plus-square'>&nbsp;</i>",
        dataTreeCollapseElement:"<i class='fa fa-sm fa-minus-square'>&nbsp;</i>",
        selectable: true, 
        selectablePersistence: true,
        rowSelectionChanged:function(data, rows){
            tabulatorSelectedIndexes = [];
            $.each(data, function (idx, obj) {
                tabulatorSelectedIndexes.push(obj.id);
            })

            $('#subsidiaries-dashboard-generate').prop('disabled', !(tabulatorSelectedIndexes.length));
            if(!(tabulatorSelectedIndexes.length)){
                for (var i = 0; i < widgets.length; i++){
                    var f = $('#developer_dashboard-'+widgets[i]+'-info');
                    if(f && !f.hasClass('jarviswidget-collapsed')){
                        f.addClass('jarviswidget-collapsed').children('div').slideUp('fast');
                    }
                }
            }
        },
        dataLoaded: function (data) {
            if (tabulatorSelectedIndexes.length > 0) {
                updatingTabulatorSelections = true;
                $.each(data, function (idx, obj) {
                    if (tabulatorSelectedIndexes.indexOf(obj.id) > -1) {
                        subsidairyListTbl.selectRow(obj.id);
                    }
                })
                updatingTabulatorSelections = false;
            }

            $('#subsidiaries-dashboard-generate').prop('disabled', !(tabulatorSelectedIndexes.length));
            if(!(tabulatorSelectedIndexes.length)){
                for (var i = 0; i < widgets.length; i++){
                    var f = $('#developer_dashboard-'+widgets[i]+'-info');
                    if(f && !f.hasClass('jarviswidget-collapsed')){
                        f.addClass('jarviswidget-collapsed').children('div').slideUp('fast');
                    }
                }
            }
        },
        columns: [{
            title:"{{trans('projects.subsidiary')}}", field: 'name', cssClass:"text-center text-left", minWidth:220, headerSort:false, formatter:"textarea", responsive:0
        },{
            formatter: "rowSelection", titleFormatter: "rowSelection", cssClass:"text-center text-middle", field: 'id', width: 12, 'align': 'center', headerSort:false
        }]
    });

    $("#subsidiaries-dashboard-generate").on('click', function(e){
        var ids = tabulatorSelectedIndexes;
        for (var i = 0; i < widgets.length; i++){
            var f = $('#developer_dashboard-'+widgets[i]+'-info');
            if(f && f.hasClass('jarviswidget-collapsed')){
                f.removeClass('jarviswidget-collapsed').children('div').slideDown('fast');
            }
        }

        if(dashboardBTbl){
            dashboardBTbl.setData("{{ route('dashboard.subsidiaries.B.ajax',[$selectedCountryId, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}", {ids:ids});
        }

        if(dashboardCTbl){
            dashboardCTbl.setData("{{ route('dashboard.subsidiaries.C.ajax',[$selectedCountryId, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}", {ids:ids});
        }

        @if($workCategories)
            $.ajax({
                url: "{{ route('dashboard.subsidiaries.D.ajax',[$selectedCountryId, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
                type: "get", //send it through get method
                data: {ids: ids},
                success: function(res) {
                    $('#developer_dashboard_widget_content-D').empty();

                    var bootstrapGridColum = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
                    if(res.length > 1){
                        bootstrapGridColum = 'col-xs-12 col-sm-12 col-md-6 col-lg-6';
                    }

                    $.each(res, function (idx, obj) {
                        var cnt = Object.keys(obj.work_categories).length;

                        $('#developer_dashboard_widget_content-D').append('<article class="'+bootstrapGridColum+'">'
                            +'<div class="jarviswidget" data-widget-colorbutton="false" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false">'
                            +'<div style="overflow-y:hidden;">'
                            +'<div class="widget-body no-padding">'
                            +'<div class="widget-body-toolbar row"><div class="col-xs-12 well">'+DOMPurify.sanitize(obj.name)+'</div></div>'
                            +'<div id="dashboard-D-table-'+idx+'"></div>'
                            +'<div id="dashboard-D-bar_chart-legend-'+idx+'"></div>'
                            +'<div id="dashboard-D-bar_chart-'+idx+'" class="chart"></div>'
                            +'</div>'
                            +'</div>'
                            +'</div>'
                            +'</article>');

                        var columns = [{
                            title:"{{trans('tenders.workCategory')}}", field:'name', cssClass:"text-center text-left", minWidth: 240, headerSort:false, formatter:"textarea"
                        },{
                            title:"{{trans('tenders.budget')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'overall_budget', cssClass:"text-right text-middle", width: 120, headerSort:false, formatter:"money"
                        },{
                            title:"{{trans('tenders.awardedContractSum')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'awarded_contract_sum', cssClass:"text-right text-middle", width: 180, headerSort:false, formatter:"money"
                        },{
                            title:"{{trans('contractManagement.variationOrder')}} @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'variation_order', cssClass:"text-right text-middle", width: 140, headerSort:false, formatter:"money"
                        },{
                            title:"Saving/Overrun @if($selectedCountry) ({{$selectedCountry->currency_code}}) @endif", field: 'overrun_amount', cssClass:"text-right text-middle", width: 160, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                            formatterParams: {
                                innerHtml: [{
                                    innerHtml: function(data){
                                        var val = parseFloat(data.overrun_amount);
                                        if(val < 0){
                                            return '<span class="badge bg-color-red">'+$.number(val, 2)+'</span>';
                                        }
                                        return $.number(val, 2);
                                    }
                                }]
                            }
                        },{
                            title:"Saving/Overrun (%)", field: 'overrun_percentage', cssClass:"text-center text-middle", width: 140, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                            formatterParams: {
                                innerHtml: [{
                                    innerHtml: function(data){
                                        var val = parseFloat(data.overrun_percentage);
                                        if(val < 0){
                                            return (data.overall_budget) ? '<span class="badge bg-color-red">'+$.number(val, 2)+' %</span>' : 0.00 + " %";
                                        }
                                        return $.number(val, 2) + " %";
                                    }
                                }]
                            }
                        }];
                        
                        new Tabulator("#dashboard-D-table-"+idx, {
                            layout:"fitColumns",
                            placeholder: "{{ trans('general.noMatchingResults') }}",
                            height: 200,
                            columns: columns,
                            data: obj.work_categories,
                        });

                        var ticks = [],
                        budgetData = [],
                        contractSumData = [],
                        variationOrderData = [];

                        $.each(obj.work_categories, function (idx, obj) {
                            ticks.push([idx, obj.name]);
                            budgetData.push([idx, obj.overall_budget]);
                            contractSumData.push([idx, obj.awarded_contract_sum]);
                            variationOrderData.push([idx, obj.variation_order]);
                        });

                        var series = [{
                            'label': '{{trans('tenders.budget')}}',
                            'color': '#39a1f4',
                            'data': budgetData,
                            'stack': 0,
                            'bars': {
                                'align': 'right'
                            }
                        },{
                            'label': '{{trans('tenders.awardedContractSum')}}',
                            'color': '#ffc241',
                            'data': contractSumData,
                            'stack': 1,
                            'bars': {
                                'align': 'left'
                            }
                        },{
                            'label': '{{trans('contractManagement.variationOrder')}}',
                            'color': '#fd3995',
                            'data': variationOrderData,
                            'stack': 1,
                            'bars': {
                                'align': 'left'
                            }
                        }];

                        setTimeout(function(){
                            options.xaxis.ticks = ticks;
                            options.legend.container = $("#dashboard-D-bar_chart-legend-"+idx);

                            if($("#dashboard-D-bar_chart-"+idx).length && $("#dashboard-D-bar_chart-"+idx).is(':visible'))
                                $.plot($("#dashboard-D-bar_chart-"+idx), series, options);
                        },100);
                    });
                }
            });
        @endif

        $.ajax({
            url: "{{ route('dashboard.subsidiaries.E.years.ajax',[$selectedCountryId, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
            type: "get",
            data: {ids: ids},
            success: function(years) {
                var chartEToggleElem = $('#chart-E-toggle');

                chartEToggleElem.empty();

                if(!years.length){
                    chartEToggleElem.append('<div class="alert alert-warning text-middle text-center">'
                    + '<i class="fa-fw fa fa-exclamation-triangle"></i>'
                    + 'No record of <strong>Certified Payment</strong> in the system.'
                    + '</div>');
                    return false;
                }

                var str = '<div class="smart-form" data-options="filter-options"><section><div class="inline-group"><label class="control-label" for="subsidiaryFilter"><strong>{{trans('projects.year')}}</strong>&nbsp;</label>'
                + '<select id="overall_certified_payment_year-select" name="year" class="form-control select2" data-action="filter" data-select-width="180px">'
                + '<option value="-1">{{trans('documentManagementFolders.all')}}</option>';

                $.each(years, function (idx, year) {
                    str += '<option value="'+year+'">'+year+'</option>';
                });

                str += '</select></div></section></div>';

                chartEToggleElem.append(str);

                $('#overall_certified_payment_year-select').select2({
                    theme: 'bootstrap',
                    allowClear : true,
                    width : '180px'
                });

                $('#overall_certified_payment_year-select').on("change.select2", function(e){
                    var url = "{{ route('dashboard.subsidiaries.E.ajax',[$selectedCountryId, ':year', $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}";
                    url = url.replace(':year', $(this).val());
                    $.ajax({
                        url: url,
                        type: "get",
                        data: {ids: ids},
                        success: function(res) {

                            var content = $('#developer_dashboard_widget_content-E');

                            content.empty();

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

                            var bootstrapGridColum = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
                            if(res.length > 1){
                                bootstrapGridColum = 'col-xs-12 col-sm-12 col-md-6 col-lg-6';
                            }

                            $.each(res, function (idx, obj) {
                                if(obj.hasOwnProperty('cost_vs_time') && obj.cost_vs_time.length){
                                    content.append('<article class="'+bootstrapGridColum+'">'
                                    + '<div class="jarviswidget" data-widget-colorbutton="false" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false">'
                                    + '<div style="overflow-y:hidden;">'
                                    + '<div class="widget-body no-padding">'
                                    + '<div class="widget-body-toolbar row"><div class="col-xs-12 well">'+DOMPurify.sanitize(obj.name)+'</div></div>'
                                    + '<section id="chart-'+obj.id+'-section-cost_vs_time">'
                                    + '<h5 style="padding-left:8px;padding-right:8px;"><i class="fa fa-chart-bar"></i> {{ trans('projects.costVsTime')}}</h5>'
                                    + '<div id="chart-E-'+obj.id+'-cost_vs_time" class="chart">'
                                    + '<div class="text-middle text-center" style="padding-top:32px;">'
                                    + '<div class="spinner-border text-primary" role="status">'
                                    + '<span class="sr-only">Loading...</span>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</section>'
                                    + '<section id="chart-'+obj.id+'-section-cumulative_cost">'
                                    + '<h5 style="padding-left:8px;padding-right:8px;"><i class="fa fa-chart-line"></i> {{ trans('projects.accumulativeCost')}}</h5>'
                                    + '<div id="chart-E-'+obj.id+'-cumulative_cost" class="chart">'
                                    + '<div class="text-middle text-center" style="padding-top:32px;">'
                                    + '<div class="spinner-border text-primary" role="status">'
                                    + '<span class="sr-only">Loading...</span>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</section>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</article>');

                                    $.plot($("#chart-E-"+obj.id+"-cost_vs_time"), [{
                                        label: "{{ trans('projects.costVsTime')}}",
                                        data: obj.cost_vs_time,
                                        color: '#2196F3',
                                        bars: {
                                            show: true,
                                            fill: 0.9,
                                            lineWidth: 0,
                                            align: 'center',
                                            barWidth: 0.4
                                        }
                                    }], options);

                                    $.plot($("#chart-E-"+obj.id+"-cumulative_cost"), [{
                                        label : "{{ trans('projects.accumulativeCost')}}",
                                        data : obj.cumulative_cost,
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
                                }
                            });
                        }
                    });
                });

                $('#overall_certified_payment_year-select').trigger("change.select2");
            }
        });
    });
});

</script>