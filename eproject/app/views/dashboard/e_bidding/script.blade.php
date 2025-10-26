<script>
    $(document).ready(function() {
        var charts = {};    // Initialize an empty object to store the chart instances
        var subsidiaryListTbl = null;

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

            if (chartContainer.length) {
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
                            height: '100%',
                            columns: options,
                            data: data,
                            pagination: 'local',
                            paginationSize: 10,
                            placeholder: "{{ trans('general.noMatchingResults') }}",
                            columnHeaderSortMulti: false,
                        };
                        chart = new Tabulator('#chart_' + id, tabulatorOptions);
                    } else if (chartContainer.hasClass('counter-chart')) {
                        var counter = $('#counter_' + id);
                        counter.text(data);
                        var n = counter[0].nextSibling;
                        while (n) {
                            var next = n.nextSibling;
                            n.parentNode.removeChild(n);
                            n = next;
                        }
                        counter.after(document.createTextNode(' ' + options));
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
        }

        function submitChartFiltersForm() {
            var optionsBidMode = $('#options_bid_mode');
            var optionsSubsidiary = $('#options_subsidiaries_container');
            //var chart = $('#chart_'+id);
            //var loadingSpinner = $('#loading_spinner_'+id);

            var data = {};

            if (optionsBidMode.length) {
                data.filter_bid_mode = optionsBidMode.val();
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
                url: "{{ route('dashboard.ebidding.stats') }}",
                method: 'GET',
                data: data,
                before: function () {
                    //chart.hide();
                    //loadingSpinner.show();
                },
                success: function (response) {
                    if (response) {
                        $.each(response, function (i, item) {
                            if (item && item.data !== undefined) {
                                renderChart(item.id, item.data, item.options);
                            }
                        });
                    }
                },
                complete: function () {
                    //loadingSpinner.hide();
                    //chart.show();
                },
                error: function (request, status, error) {
                    // error
                }
            });
        }

        function initChartContainers() {
            $('.chart-container').each(function() {
                var chartContainer = $(this);
                var chartId = chartContainer.data('r');
                var chartContainerId = '#chart_container_' + chartId;

                if (chartContainer.find('.jarviswidget').length > 0) {
                    $(chartContainerId).jarvisWidgets({
                        grid: 'article',
                        widgets: '.jarviswidget',
                        buttonsHidden: false,
                        toggleButton: true,
                        toggleClass: 'fa fa-minus | fa fa-plus',
                        toggleSpeed: 200,
                        fullscreenButton: false,
                        fullscreenClass: 'fa fa-expand | fa fa-compress',
                        fullscreenDiff: 3,
                        editbutton: false,
                        colorbutton: false,
                        deletebutton: false,
                        sortable: false,
                        buttonOrder: '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%'
                    });
                }
            });

            subsidiaryListTbl = new Tabulator('#options_subsidiaries_table', {
                ajaxURL: "{{ route('dashboard.ebidding.subsidiaries') }}",
                ajaxParams: {
                    bid_mode_slug: $('#options_bid_mode').val()   // send along with every request
                },
                layout: 'fitColumns',
                fillHeight: false,
                height: '200px',
                placeholder: "{{ trans('general.noMatchingResults') }}",
                selectable: true,
                selectablePersistence: true,
                rowFormatter: function(row) {
                    // Check if row should be selected
                    var data = row.getData();
                    if (data.selected) {
                        row.select();
                        // Add the row's ID to tabulatorSelectedIndexes
                        $('#options_subsidiaries_container').data('d').push(data.id.toString());
                    }
                },
                rowSelectionChanged: function(data, rows) {
                    // Clear tabulatorSelectedIndexes
                    $('#options_subsidiaries_container').data('d', []);

                    // Repopulate tabulatorSelectedIndexes with the IDs of the currently selected rows
                    $.each(data, function(idx, obj) {
                        $('#options_subsidiaries_container').data('d').push(obj.id.toString()); // Ensure IDs are strings
                    });

                    $('#subsidiaries_generate').prop('disabled', data.length === 0);
                    if (data.length === 0) {
                        submitChartFiltersForm();
                    }
                },
                columns: [{
                    title:"{{ trans('projects.subsidiary') }}", field: 'name', cssClass:'text-center text-left', minWidth:220, headerSort:false, formatter:'textarea', responsive:0
                },{
                    titleFormatter: 'rowSelection', field: 'id', cssClass:'text-center text-middle', width: 12, 'align': 'center', headerSort:false, formatter: 'rowSelection'
                }]
            });

            // Initial chart rendering
            submitChartFiltersForm();
        }

        function setSubsidiaryList(bidMode) {
            if (subsidiaryListTbl) {
                subsidiaryListTbl.setData("{{ route('dashboard.ebidding.subsidiaries') }}", { bid_mode_slug: bidMode });
                $('#options_subsidiaries_container').data('d', []);
                $('#subsidiaries_generate').prop('disabled', true);
            }
        }

        initChartContainers();

        // Select filter change triggers submit
        $('.options_filter').on('select2:select', function (e) {
            setSubsidiaryList($('#options_bid_mode').val());
            submitChartFiltersForm();
        });

        $('.subsidiaries_generate').on('click', function(e) {
            submitChartFiltersForm();
        });
    });
</script>