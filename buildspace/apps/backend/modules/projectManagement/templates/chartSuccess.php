<?php
$chartTitles = [$projectSchedule->title, $projectSchedule->ProjectStructure->MainInformation->title];
?>
<script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {
        var options = {
            series: [{
                name: "Cost (<?php echo $projectSchedule->ProjectStructure->MainInformation->Currency->currency_code?>)",
                data: <?php echo $chartData?>
            }],
            chart: {
                height: '98%',
                type: 'area',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: <?php echo json_encode($chartTitle) ?>,
                align: 'left',
                style: {
                    fontSize:  '12px',
                }
            },
            subtitle: {
                text: <?php echo json_encode($chartTitles) ?>,
                align: 'left',
                style: {
                    fontSize:  '10px',
                    fontWeight:  'bold',
                }
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "<?php echo $projectSchedule->ProjectStructure->MainInformation->Currency->currency_code?> "+$.number(value, 2);
                    }
                }
            },
            xaxis: {
                categories: <?php echo json_encode($chartLabels)?>,
            }
        };
        var chart = new ApexCharts(document.querySelector("#chartNode"), options);
        chart.render();
    });
    //]]>
</script>
<div id="chartNode" style="width: 100%; height: 100%;"></div>

