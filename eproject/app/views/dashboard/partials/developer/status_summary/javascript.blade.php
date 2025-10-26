<script type="text/javascript">
$(document).ready(function() {
    'use strict';

    var widgets = ['procurement_method', 'project_status', 'e_tender_waiver_status', 'e_auction_waiver_status'];
    var eTenderOtherStatus = {{\PCK\Buildspace\NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS}};
    var eAuctionOtherStatus = {{\PCK\Buildspace\NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS}};

    for (var i = 0; i < widgets.length; i++) {
        $('#status_summary_widget-'+widgets[i]).jarvisWidgets({
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

    var procurementMethodTbl = new Tabulator("#dashboard-procurement_method-table", {
        ajaxURL: "{{ route('dashboard.procurement.method.ajax',[$selectedCountry->id, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 320,
        tooltips:true,
        dataLoaded: function (data) {
            var obj = data.find(el => el.id === "last-row");
            if(obj){
                var cnt = 0;
                var series = [];
                var colors = [];
                var labels = [];
                for(var k in obj){
                    var id = k.substring(0, k.indexOf("_total"));
                    if(id && obj.hasOwnProperty(id+'_total') && obj[id+'_total'] !== undefined){
                        var color = '#2196F3';
                        if((cnt in default_colors)){
                            var k = Object.keys(default_colors[cnt]);
                            color = (k[0]!='white') ? default_colors[cnt][k[0]] : '#2196F3';
                        }
                        labels.push(DOMPurify.sanitize(obj[id+'_procurement_method_name']));
                        colors.push(color);
                        series.push(obj[id+'_total']);
                        cnt++;
                    }
                }

                if(series.length){
                    var chart = new ApexCharts(document.querySelector("#procurement_method-pie-chart"), {
                        colors: colors,
                        series: series,
                        labels: labels,
                        chart: {
                            type: 'donut',
                            height: '200px'
                        },
                        legend: {
                            show: true,
                            fontSize: '10px',
                            position: 'bottom'
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: '100%'
                                }
                            }
                        }]
                    });
                    chart.render();
                }
            }else{
                procurementMethodTbl.destroy();
                $('#status_summary_widget-procurement_method').remove();
            }
        },
        columns: [
            {title:"{{trans('projects.subsidiary')}}", field: 'name', cssClass:"text-center text-left", minWidth:240, headerSort:false, formatter:"textarea"}
            @foreach($procurementMethods as $procurementMethod)
            ,{
                title:"{{$procurementMethod->name}}", field: '{{$procurementMethod->id}}_total', cssClass:"text-center text-middle", width: 120, headerSort:false, formatter:app_tabulator_utilities.lastRowFormatter
            }@endforeach
        ]
    });

    var projectStatusTbl = new Tabulator("#dashboard-project_status-table", {
        ajaxURL: "{{ route('dashboard.project.status.ajax',[$selectedCountry->id, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 320,
        tooltips:true,
        dataLoaded: function (data) {
            var obj = data.find(el => el.id === "last-row");
            if(obj){
                var cnt = 0;
                var series = [];
                var colors = [];
                var labels = [];
                for(var k in obj){
                    var id = k.substring(0, k.indexOf("_total"));
                    if(id && obj.hasOwnProperty(id+'_total') && obj[id+'_total'] !== undefined){
                        var color = '#2196F3';
                        if((cnt in default_colors)){
                            var k = Object.keys(default_colors[cnt]);
                            color = (k[0]!='white') ? default_colors[cnt][k[0]] : '#2196F3';
                        }
                        labels.push(DOMPurify.sanitize(obj[id+'_status_txt']));
                        colors.push(color);
                        series.push(obj[id+'_total']);
                        cnt++;
                    }
                }

                if(series.length){
                    var chart = new ApexCharts(document.querySelector("#project_status-pie-chart"), {
                        colors: colors,
                        series: series,
                        labels: labels,
                        chart: {
                            type: 'donut',
                            height: '200px'
                        },
                        legend: {
                            show: true,
                            fontSize: '10px',
                            position: 'bottom'
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: '100%'
                                }
                            }
                        }]
                    });
                    chart.render();
                }
            }else{
                projectStatusTbl.destroy();
                $('#status_summary_widget-project_status').remove();
            }
        },
        columns: [
            {title:"{{trans('projects.subsidiary')}}", field: 'name', cssClass:"text-center text-left", minWidth:240, headerSort:false, formatter:"textarea"}
            @foreach($projectStatuses as $id => $statusTxt)
            ,{
                title:"{{$statusTxt}}", field: '{{$id}}_total', cssClass:"text-center text-middle", width: 120, headerSort:false, formatter:app_tabulator_utilities.lastRowFormatter
            }@endforeach
        ]
    });

    var eTenderWaiverTbl = new Tabulator("#dashboard-e_tender_waiver_status-table", {
        ajaxURL: "{{ route('dashboard.e.tender.waiver.status.ajax',[$selectedCountry->id, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 320,
        tooltips:true,
        dataLoaded: function (data) {
            var obj = data.find(el => el.id === "last-row");
            if(obj){
                var cnt = 0;
                var series = [];
                var colors = [];
                var labels = [];
                for(var k in obj){
                    var id = k.substring(0, k.indexOf("_total"));
                    if(id && obj.hasOwnProperty(id+'_total') && obj[id+'_total'] !== undefined){
                        var color = '#2196F3';
                        if((cnt in default_colors)){
                            var k = Object.keys(default_colors[cnt]);
                            color = (k[0]!='white') ? default_colors[cnt][k[0]] : '#2196F3';
                        }
                        labels.push(DOMPurify.sanitize(obj[id+'_status_txt']));
                        colors.push(color);
                        series.push(obj[id+'_total']);
                        cnt++;
                    }
                }

                if(series.length){
                    var chart = new ApexCharts(document.querySelector("#e_tender_waiver_status-pie-chart"), {
                        colors: colors,
                        series: series,
                        labels: labels,
                        chart: {
                            type: 'donut',
                            height: '200px'
                        },
                        legend: {
                            show: true,
                            fontSize: '10px',
                            position: 'bottom'
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: '100%'
                                }
                            }
                        }]
                    });
                    chart.render();
                }

                var started = false;
                var url = '{{ route("dashboard.e.tender.waiver.status.other.ajax", [":id", $selectedCountry->id, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}';
                $.each(data, function (idx, o) {
                    if(o.hasOwnProperty(eTenderOtherStatus+'_total') && o.id != 'last-row'){
                        if(!started){
                            $('#status_summary_widget-content-e_tender_waiver_status').append('<article class="col col-xs-12" style="padding:8px;">'
                            +'<h5>Details of Other Status</h5>'
                            +'</article>');
                            started = true;
                        }
                        generateWaiverOtherStatusTable(eTenderOtherStatus, o, idx, url);
                    }
                });
            }else{
                eTenderWaiverTbl.destroy();
                $('#status_summary_widget-e_tender_waiver_status').remove();
            }
        },
        columns: [
            {title:"{{trans('projects.subsidiary')}}", field: 'name', cssClass:"text-center text-left", minWidth:240, headerSort:false, formatter:"textarea"}
            @foreach($eTenderWaiverStatuses as $waiverStatusVal => $waiverStatusTxt)
            ,{
                title:"{{$waiverStatusTxt}}", field: '{{$waiverStatusVal}}_total', cssClass:"text-center text-middle", width: 120, headerSort:false, formatter:app_tabulator_utilities.lastRowFormatter
            }@endforeach
            ,{
                title:"{{trans('projects.total')}}", field: 'overall_sum', cssClass:"text-center text-middle", width: 80, headerSort:false, formatter:app_tabulator_utilities.lastRowFormatter
            }
        ]
    });

    var eAuctionWaiverTbl = new Tabulator("#dashboard-e_auction_waiver_status-table", {
        ajaxURL: "{{ route('dashboard.e.auction.waiver.status.ajax',[$selectedCountry->id, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}",
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 320,
        tooltips:true,
        dataLoaded: function (data) {
            var obj = data.find(el => el.id === "last-row");
            if(obj){
                var cnt = 0;
                var series = [];
                var colors = [];
                var labels = [];
                for(var k in obj){
                    var id = k.substring(0, k.indexOf("_total"));
                    if(id && obj.hasOwnProperty(id+'_total') && obj[id+'_total'] !== undefined){
                        var color = '#2196F3';
                        if((cnt in default_colors)){
                            var k = Object.keys(default_colors[cnt]);
                            color = (k[0]!='white') ? default_colors[cnt][k[0]] : '#2196F3';
                        }
                        labels.push(DOMPurify.sanitize(obj[id+'_status_txt']));
                        colors.push(color);
                        series.push(obj[id+'_total']);
                        cnt++;
                    }
                }

                if(series.length){
                    var chart = new ApexCharts(document.querySelector("#e_auction_waiver_status-pie-chart"), {
                        colors: colors,
                        series: series,
                        labels: labels,
                        chart: {
                            type: 'donut',
                            height: '200px'
                        },
                        legend: {
                            show: true,
                            fontSize: '10px',
                            position: 'bottom'
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: '100%'
                                }
                            }
                        }]
                    });
                    chart.render();
                }

                var started = false;
                var url = '{{ route("dashboard.e.auction.waiver.status.other.ajax", [":id", $selectedCountry->id, $selectedFromMonth, $selectedFromYear, $selectedToMonth, $selectedToYear]) }}';
                $.each(data, function (idx, o) {
                    if(o.hasOwnProperty(eAuctionOtherStatus+'_total') && o.id != 'last-row'){
                        if(!started){
                            $('#status_summary_widget-content-e_auction_waiver_status').append('<article class="col col-xs-12" style="padding:8px;">'
                            +'<h5>Details of Other Status</h5>'
                            +'</article>');
                            started = true;
                        }
                        generateWaiverOtherStatusTable(eAuctionOtherStatus, o, idx, url);
                    }
                });
            }else{
                eAuctionWaiverTbl.destroy();
                $('#status_summary_widget-e_auction_waiver_status').remove();
            }
        },
        columns: [
            {title:"{{trans('projects.subsidiary')}}", field: 'name', cssClass:"text-center text-left", minWidth:240, headerSort:false, formatter:"textarea"}
            @foreach($eAuctionWaiverStatuses as $waiverStatusVal => $waiverStatusTxt)
            ,{
                title:"{{$waiverStatusTxt}}", field: '{{$waiverStatusVal}}_total', cssClass:"text-center text-middle", width: 120, headerSort:false, formatter:app_tabulator_utilities.lastRowFormatter
            }@endforeach
            ,{
                title:"{{trans('projects.total')}}", field: 'overall_sum', cssClass:"text-center text-middle", width: 80, headerSort:false, formatter:app_tabulator_utilities.lastRowFormatter
            }
        ]
    });
});

function generateWaiverOtherStatusTable(waiverType, obj, idx, url){
    var elid = (waiverType=={{\PCK\Buildspace\NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS}}) ? 'e_auction_waiver_status' : 'e_tender_waiver_status';

    $('#status_summary_widget-content-'+elid).append('<article class="col col-xs-12 col-sm-12 col-md-6 col-lg-6 " style="padding-left:8px;padding-right:8px;">'
        +'<div class="jarviswidget" data-widget-colorbutton="false" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false">'
        +'<div style="overflow-y:hidden;">'
        +'<div class="widget-body no-padding">'
        +'<div class="widget-body-toolbar row"><div class="col-xs-12 well">'+DOMPurify.sanitize(obj.name)+'</div></div>'
        +'<div id="'+elid+'-table_other-'+idx+'"></div>'
        +'</div>'
        +'</div>'
        +'</div>'
        +'</article>');

    url = url.replace(':id', obj.id);
    new Tabulator("#"+elid+"-table_other-"+idx, {
        ajaxURL: url,
        ajaxConfig:"get",
        layout:"fitColumns",
        placeholder: "{{ trans('general.noMatchingResults') }}",
        height: 200,
        columns: [{
            title:"{{trans('projects.development')}}", field:'project_title', cssClass:"text-center text-left", minWidth: 240, headerSort:false, formatter:"textarea"
        },{
            title:"{{trans('workCategories.workCategories')}}", field: 'work_category_name', cssClass:"text-center text-middle", width: 160, headerSort:false, formatter:"textarea"
        },{
            title:"{{trans('costData.details')}}", field: 'waiver_option_description', cssClass:"text-center text-middle", width: 120, headerSort:false, formatter:"textarea"
        }]
    });
}
</script>
