<script>
    $(document).ready(function() {
        var charts = {};    // Initialize an empty object to store the chart instances

        function setFormatterOptions(container, opts) {
            if (container.hasClass('graph-chart')) {
                opts.yaxis = {
                    labels: {
                        show: true,
                        formatter: value => $.number(value, 0, '.', ',')
                    }
                };
            }

            if (container.hasClass('pie-chart') || container.hasClass('donut-chart')) {
                opts.tooltip = {
                    y: {
                        formatter: value => $.number(value, 0, '.', ',')
                    }
                };
            }

            if (container.hasClass('donut-chart')) {
                opts.plotOptions = {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    formatter: w => $.number(
                                        w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                                        0, '.', ','
                                    )
                                }
                            }
                        }
                    }
                };
            }
        }

        function renderChart(id, data, options) {
            var chart = charts[id];
            var chartContainer = $('#chart_' + id);

            if (chart) {
                if (chartContainer.hasClass('table-chart')) {
                    // Update the existing Tabulator table
                    chart.setColumns(options);
                    chart.setData(data);
                } else {
                    // Update the existing chart
                    chart.updateSeries(data);
                    setFormatterOptions(chartContainer, options);
                    chart.updateOptions(options);
                }
            } else {
                if (chartContainer.hasClass('table-chart')) {
                    // Initialize a new Tabulator instance
                    var tabulatorOptions = {
                        layout: 'fitColumns',
                        fillHeight: false,
                        columns: options,
                        data: data,
                        pagination: 'local',
                        paginationSize: 30,
                        placeholder: "{{ trans('projectReportChart.noDataAvailable') }}",
                        columnHeaderSortMulti: false,
                    };
                    chart = new Tabulator('#chart_' + id, tabulatorOptions);
                } else {
                    // Create a new chart instance
                    setFormatterOptions(chartContainer, options);
                    chart = new ApexCharts(chartContainer[0], {
                        ...options,
                        series: data
                    });
                    chart.render();
                }
                // Store the new chart instance in the charts object
                charts[id] = chart;
            }
        }

        function submitChartFiltersForm(id) {
            var optionsYear = $('#options_year_'+id);
            var optionsGrouping = $('#options_grouping_'+id);
            var optionsSubsidiary = $('#options_subsidiaries_container_'+id);
            var chart = $('#chart_'+id);
            var loadingSpinner = $('#loading_spinner_'+id);

            var data = {};

            if (optionsYear.length) {
                data.filter_year = optionsYear.val();
            }
            if (optionsGrouping.length) {
                let selectedValue = optionsGrouping.find('option:selected').val();
                if (selectedValue) {
                    optionsGrouping.val(selectedValue).trigger('change.select2'); // Use change.select2 for select2
                } else {
                    // Set the default value if no option is selected
                    optionsGrouping.val(optionsGrouping.find('option:first').val()).trigger('change.select2');
                }
                data.filter_grouping = optionsGrouping.val();
            }
            if (optionsSubsidiary.length) {
                var subsidiariesData = optionsSubsidiary.data('d');
                if (Array.isArray(subsidiariesData)) {
                    data.filter_subsidiaries = subsidiariesData.join(',');
                } else {
                    data.filter_subsidiaries = [subsidiariesData];
                }
            }

            $.ajax({
                url: $('#chart_container_'+id).data('l'),
                method: 'GET',
                data: data,
                before: function () {
                    chart.hide();
                    loadingSpinner.show();
                },
                success: function (response) {
                    if (response) {
                        if (response.data && response.options) {
                            // Update the chart with the new data and options
                            renderChart(id, response.data, response.options);
                        }
                    }
                },
                complete: function () {
                    loadingSpinner.hide();
                    chart.show();
                },
                error: function (request, status, error) {
                    // error
                }
            });
        }

        $('.chart-container').each(function() {
            var chartContainer = $(this);
            var chartId = chartContainer.data('r');

            $('#chart_container_'+chartId).jarvisWidgets({
                grid : 'article',
                widgets : '.jarviswidget',
                buttonsHidden : false,
                toggleButton : true,
                toggleClass : 'fa fa-minus | fa fa-plus',
                toggleSpeed : 200,
                fullscreenButton : false,
                fullscreenClass : 'fa fa-expand | fa fa-compress',
                fullscreenDiff : 3,
                editbutton : false,
                colorbutton : false,
                deletebutton : false,
                sortable : false,
                buttonOrder : '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%'
            });

            var subsidiaryListTbl = new Tabulator('#options_subsidiaries_table_' + chartId, {
                ajaxURL: $('#options_subsidiaries_container_' + chartId).data('l'),
                layout: 'fitColumns',
                fillHeight: false,
                placeholder: "{{ trans('general.noMatchingResults') }}",
                tooltips:true,
                dataTree:true,
                dataTreeStartExpanded:true,
                dataTreeSelectPropagate:true,
                dataTreeChildColumnCalcs:true,
                dataTreeExpandElement: "<i class='fa fa-sm fa-plus-square'>&nbsp;</i>",
                dataTreeCollapseElement: "<i class='fa fa-sm fa-minus-square'>&nbsp;</i>",
                selectable: true,
                selectablePersistence: true,
                rowFormatter: function(row) {
                    // Check if row should be selected
                    var data = row.getData();
                    if (data.selected) {
                        row.select();
                        // Add the row's ID to tabulatorSelectedIndexes
                        $('#options_subsidiaries_container_'+chartId).data('d').push(data.id.toString());
                    }
                },
                rowSelectionChanged: function(data, rows) {
                    // Clear tabulatorSelectedIndexes
                    $('#options_subsidiaries_container_'+chartId).data('d', []);

                    // Repopulate tabulatorSelectedIndexes with the IDs of the currently selected rows
                    $.each(data, function(idx, obj) {
                        $('#options_subsidiaries_container_'+chartId).data('d').push(obj.id.toString()); // Ensure IDs are strings
                    });

                    $('#subsidiaries_generate_'+chartId).prop('disabled', data.length === 0);
                },
                columns: [{
                    title:"{{ trans('projects.subsidiary') }}", field: 'name', cssClass:'text-center text-left', minWidth:220, headerSort:false, formatter:'textarea', responsive:0
                },{
                    titleFormatter: 'rowSelection', field: 'id', cssClass:'text-center text-middle', width: 12, 'align': 'center', headerSort:false, formatter: 'rowSelection'
                }]
            });

            submitChartFiltersForm(chartId);
        });

        $('.subsidiaries_generate').on('click', function(e) {
            var t = $(this);
            var p = t.parents('.chart-container');
            submitChartFiltersForm(p.data('r'));
        });

        $('.options_year, .options_grouping').on('select2:select', function (e) {
            var t = $(this);
            var p = t.parents('.chart-container');
            var r = p.data('r');

            if (t.hasClass('options_grouping')) {
                if (parseInt(t.val()) === parseInt({{ \PCK\ProjectReport\ProjectReportChartPlot::GRP_YEARLY }})) {
                    $('#options_year_container_'+r).hide();
                } else {
                    $('#options_year_container_'+r).show();
                }
            }

            submitChartFiltersForm(r);
        });
    });
</script>