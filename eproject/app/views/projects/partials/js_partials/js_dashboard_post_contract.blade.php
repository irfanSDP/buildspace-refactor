<script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>

<script src="{{ asset('js/plugin/flot/jquery.flot.cust.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.resize.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.fillbetween.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.orderBar.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.pie.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.time.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.tooltip.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function() {

        @if(!empty($projectSchedules))
        $('select.form-control').select2({width:'280px', theme: 'bootstrap'});

        $('#project_schedule-select').on("select2:select", function(e) {
            $.ajax({
                url: "{{url('projects/projectScheduleCostTimeData/');}}/"+$(this).val(),
                type: "GET",
                dataType: "json",
                beforeSend: function( xhr ) {
                    $('#project_schedule-chart').html('<div class="text-middle text-center" style="padding-top:32px;">'
                    +'<div class="spinner-border text-primary" role="status">'
                    +'<span class="sr-only">Loading...</span>'
                    +'</div>'
                    +'</div>');
                },
                success: onDataReceived
            });
        });

        <?php $keys = array_keys($projectSchedules)?>
        $('#project_schedule-select').val('{{{$keys[0]}}}').trigger('select2:select');
        @endif

        @if(!empty($dashBoardData->records->contract_amt))
        var chart = new ApexCharts(document.querySelector("#contract_info-pie_chart"), {
            colors: ['#39a1f4', '#fd3995'],
            series: [{{$dashBoardData->records->contract_amt}}, {{$dashBoardData->records->variation_amt}}],
            labels: ["{{ trans('projects.contractAmount')}}", "{{ trans('projects.voAmount')}}"],
            chart: {
                type: 'donut',
                height: '200px'
            },
            legend: {
                show: true,
                position: 'right',
                horizontalAlign: 'left',
                fontSize: '12px',
                itemMargin: {
                    horizontal: 0,
                    vertical: 0
                },
                formatter: (val, s) => (s.seriesIndex > 0) ? val+":<br/> <strong>{{$project->modified_currency_code}} " +$.number(s.w.globals.series[1], 2)+"</strong>" : val+":<br/> <strong>{{$project->modified_currency_code}} " +$.number(s.w.globals.series[0], 2)+"</strong>"
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: (val) => "{{$project->modified_currency_code}} "+$.number(val, 2),
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        });
        chart.render();

        var chart2ActualValues = [{{$dashBoardData->records->contract_amt + $dashBoardData->records->variation_amt}}, {{$dashBoardData->records->up_to_date_claim_amt}}];

        chart2 = new ApexCharts(document.querySelector("#claim_info-pie_chart"), {
            chart: {
                height: 200,
                type: "radialBar",
            },
            series: [100, $.number(chart2ActualValues[1]/chart2ActualValues[0]*100, 2)],
            colors: ['#1dc9b7', '#ffc241'],
            labels: ["{{ trans('projects.contract')}} + {{ trans('projects.voAmount')}}", "{{ trans('projects.upToDateClaim')}}"],
            legend: {
                show: true,
                position: 'right',
                horizontalAlign: 'left',
                fontSize: '12px',
                itemMargin: {
                    horizontal: 0,
                    vertical: 0
                },
                formatter: (val, s) => (s.seriesIndex > 0) ? val+":<br/> <strong>{{$project->modified_currency_code}} " +$.number(chart2ActualValues[s.seriesIndex], 2)+"</strong>" : val+":<br/> <strong>{{$project->modified_currency_code}} " +$.number(chart2ActualValues[s.seriesIndex], 2)+"</strong>"
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: (val, s) => "{{$project->modified_currency_code}} "+$.number(chart2ActualValues[s.globals.series.indexOf(val)], 2),
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        });
        chart2.render();
        @endif
    });

    function onDataReceived(series) {
        var accumulativeCost = [];
        var costVsTime = [];
        var i;
        var labels = [];
        for (i = 0; i < series.accumulative_cost.length; i++) {
            labels.push(series.accumulative_cost[i][0]);
            accumulativeCost.push(series.accumulative_cost[i][1])
        }

        for (i = 0; i < series.cost_vs_time.length; i++) {
            labels.push(series.cost_vs_time[i][0]);
            costVsTime.push(series.cost_vs_time[i][1]);
        }

        labels = [...new Set(labels)];

        $('#project_schedule-chart').html("");
        var options = {
            series: [{
                name: "{{ trans('projects.costVsTime')}}",
                type: 'column',
                data: costVsTime
            },{
                name: "{{ trans('projects.accumulativeCost')}}",
                type: 'area',
                data: accumulativeCost
            },],
            chart: {
                height: 280,
                type: 'line',
                stacked: false,
                toolbar: {
                    show: false
                },
            },
            stroke: {
                width: [0, 2, 5],
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    columnWidth: '50%'
                }
            },
            fill: {
                opacity: [0.85, 0.25, 1],
                gradient: {
                    inverseColors: false,
                    shade: 'light',
                    type: "vertical",
                    opacityFrom: 0.85,
                    opacityTo: 0.55,
                    stops: [0, 100, 100, 100]
                }
            },
            labels: labels,
            markers: {
                size: 0
            },
            xaxis: {
                type: 'datetime'
            },
            yaxis: {
                title: {
                    text: 'Cost',
                },
                labels: {
                    formatter: function(v){
                        return "{{$project->modified_currency_code}} " +$.number(v, 2);
                    }
                },
                min: 0
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y) {
                        if (typeof y !== "undefined") {
                            return "{{$project->modified_currency_code}} " +$.number(y, 2);
                        }
                        return y;
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#project_schedule-chart"), options);
        chart.render();
    }
</script>