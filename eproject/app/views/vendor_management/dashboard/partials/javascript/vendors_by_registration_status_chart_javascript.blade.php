<script>
    var vendorsByRegistrationStatusChart = new ApexCharts(document.querySelector("#vendorsByRegistrationStatusChart"), {
        series: [],
        labels: [],
        chart: {
            width: 500,
            type: 'pie',
        },
        dataLabels: {
            formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                return roundToDecimal(value) + '%';
            }
        },
        tooltip: {
            enabled: true,
            followCursor: true,
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                let seriesTotal   = series.reduce((a, b) => a + b, 0);
                let currentSeries = series[seriesIndex];
                let percentage    = roundToDecimal(((currentSeries / seriesTotal) * 100.0));
                
                return '<div class="chart_tooltip">' +
                       '<span>' + w.globals.labels[seriesIndex] + ' - ' + series[seriesIndex] + ' (' + percentage + '%)' + '</span>' +
                       '</div>';
            }
        },
        legend: {
            show: true,
            position: 'right',
            horizontalAlign: 'right',
            itemMargin: {
                horizontal: 0,
                vertical: 0
            },
            formatter: function(seriesName, opts) {
                let seriesTotal   = opts.w.globals.series.reduce((a, b) => a + b, 0);
                let currentSeries = opts.w.globals.series[opts.seriesIndex];
                let percentage    = roundToDecimal(((currentSeries / seriesTotal) * 100.0));

                return `${seriesName} - ${currentSeries} (${percentage}%)`;
            }
        },
        responsive: [{
            breakpoint: 500,
            options: {
                chart: {
                    width: 500
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });

    vendorsByRegistrationStatusChart.render();

    var formInputs = {
        country: null,
        state: null,
        vendor_group: null,
        vendor_category: null,
        vendor_work_category: null,
        vendor_work_subcategory: null,
        registration_status: null,
        company_status: null,
        preq_grade: null,
        vpe_grade: null,
    };

    renderVendorsByRegistrationStatusChart(formInputs, 'vendorsByRegistrationStatus');

    function renderVendorsByRegistrationStatusChart(data, identifier) {
        data.identifier = identifier;

        $.ajax({
            url: "{{ route('vendorManagement.dashboard.vendorStatistics') }}",
            method: 'GET',
            data: data,
            success: function(response){
                vendorsByRegistrationStatusChart.updateOptions({
                    labels: response.labels,
                    series: response.series,
                    chart: {
                        width: 450,
                    }
                }, true, true, true);
            }
        });
    }
</script>