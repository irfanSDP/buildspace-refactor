<script>
    var vendorsByStateChart = new ApexCharts(document.querySelector("#vendorsByStateChart"), {
        series: [{
          data: []
        }],
        chart: {
          type: 'bar',
          height: 500,
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            borderRadius: 4,
            horizontal: true,
          }
        },
        dataLabels: {
          enabled: false,
        },
        xaxis: {
          categories: [],
        },
        tooltip: {
            enabled: true,
            followCursor: true,
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                let seriesTotal   = series[seriesIndex].reduce((a, b) => a + b, 0);
                let currentSeries = series[seriesIndex][dataPointIndex];
                let percentage    = roundToDecimal(((currentSeries / seriesTotal) * 100.0));

                return '<div class="chart_tooltip" style="color:#fff;">' +
                        '<span>' + w.globals.labels[dataPointIndex] + ' - ' + currentSeries + ' (' + percentage + '%)' + '</span>' +
                        '</div>';
            },
        },
    });

    vendorsByStateChart.render();

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

    function renderVendorsByStateChart(data, identifier) {
        data.identifier = identifier;

        $.ajax({
            url: "{{ route('vendorManagement.dashboard.vendorStatistics') }}",
            method: 'GET',
            data: data,
            success: function(response){
                vendorsByStateChart.updateOptions({
                    series: [{
                        data: response.series,
                    }],
                    xaxis: {
                        categories: response.labels,
                    },
                    chart: {
                        width: 500,
                    }
                }, true, true, true);
            }
        });
    }
</script>