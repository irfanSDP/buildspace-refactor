@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}

        .button-row {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }

        .tick-icon {
            color: #0bb10b;
        }

        .cross-icon {
            color: #ff0a49;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ trans('projectReportChart.templates') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReportChart.templates') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 button-row">
            <a href="{{ route('projectReport.chart.template.create') }}" class="btn btn-primary btn-md pull-right header-btn">
				<i class="fa fa-plus"></i> {{ trans('projectReportChart.newTemplate') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReportChart.templates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="data-table"></div>
			</div>
		</div>
	</div>
	
	@include('templates.yesNoModal', array(
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ))
    @include('templates.yesNoModal', array(
        'modalId'   => 'lockRevisionYesNoModal',
        'titleId'   => 'lockRevisionYesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'lockRevisionYesNoModalMessage',
    ))
	@include('templates.warning_modal', array(
        'modalId'          => 'warningModal',
        'warningMessageId' => 'txtWarningMessage',
    ))
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let dataTable = null;

        const chartTypeFormatter = function(cell, formatterParams, onRendered) {
            const label = cell.getValue();
            const rowData = cell.getRow().getData();
            let icon = '';

            if (rowData['chart_icon'] !== null && rowData['chart_icon'] !== '' && rowData['chart_icon'] !== 'null') {
                icon = "<i class='"+ rowData['chart_icon'] +"' style='color:#2196f3;'></i> ";
            }

            return icon + label;
        }

        const tickCrossFormatter = function(cell, formatterParams, onRendered) {
            // Get the value of the cell
            let value = cell.getValue();

            // Check if the value is truthy
            if (value) {
                // If the value is truthy, return a tick icon
                return "<i class='fa fa-lg fa-check tick-icon'></i>";
            } else {
                // If the value is falsy, return a cross icon
                return "<i class='fa fa-lg fa-times cross-icon'></i>";
            }
        }

        const rearrangeHeader = function(cell, formatterParams, onRendered) {
            const titleDiv = document.createElement('div');
            titleDiv.innerHTML = "<div>{{ trans('projectReportChart.rearrange') }}</div><div>({{ trans('projectReportChart.dragAndDrop') }})</div>";
            return titleDiv;
        }

        const rearrangeBtnFormatter = function(cell, formatterParams, onRendered) {
            const btn = document.createElement('button');
            btn.dataset.toggle = 'tooltip';
            btn.title = "{{ trans('projectReportChart.dragAndDropToRearrange') }}";
            btn.className = 'btn btn-xs btn-default';
            btn.innerHTML = '<i class="fa fa-bars"></i>';
            return btn;
        }

        const templateLinkFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            if (rowData.hasOwnProperty('route:edit')) {
                let templateLink = document.createElement('a');
                templateLink.href = rowData['route:edit'];
                templateLink.innerHTML = rowData.title;
                templateLink.dataset.toggle = 'tooltip';
                templateLink.title = "{{ trans('projectReportChart.editTemplate') }}";
                templateLink.style['user-select'] = 'none';
                return templateLink;
            } else {
                return rowData.title;
            }
        }

        const actionsFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            let container = document.createElement('div');
            container.style.textAlign = "left";

            if (rowData.hasOwnProperty('route:plots')) {
                const btn = document.createElement('a');
                btn.href = rowData['route:plots'];
                btn.dataset.toggle = 'tooltip';
                btn.title = "{{ trans('projectReportChart.managePlots') }}";
                btn.className = 'btn btn-xs btn-primary';
                btn.innerHTML = '<i class="fa fa-chart-line"></i>';
                btn.style['margin-right'] = '5px';

                container.appendChild(btn);
            }

            /*if (rowData.hasOwnProperty('route:lock')) {
                const lockRevisionButton = document.createElement('a');
                lockRevisionButton.title = "{{--  trans('projectReportChart.lockTemplate') --}}";
                lockRevisionButton.className = "btn btn-xs btn-success";
                lockRevisionButton.innerHTML = '<i class="fa fa-lock"></i>';
                lockRevisionButton.style['margin-right'] = '5px';
                lockRevisionButton.dataset.action = 'lockTemplate';
                lockRevisionButton.dataset.url = rowData['route:lock'];
                lockRevisionButton.dataset.target = "#lockRevisionYesNoModal";
                lockRevisionButton.dataset.toggle = "modal";

                lockRevisionButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#lockRevisionYesNoModalMessage').html("{{ trans('projectReportChart.allContentsWillBeLocked') . ' ' . trans('general.sureToProceed') }}");
                    $('#lockRevisionYesNoModal [data-action="actionYes"]').data('route_lock_revision', rowData['route:lock']);
                });

                container.appendChild(lockRevisionButton);
            }*/

            if (rowData.hasOwnProperty('route:edit')) {
                const btn = document.createElement('a');
                btn.href = rowData['route:edit'];
                btn.dataset.toggle = 'tooltip';
                btn.title = "{{ trans('projectReportChart.editTemplate') }}";
                btn.className = 'btn btn-xs btn-primary';
                btn.innerHTML = '<i class="fa fa-edit"></i>';
                btn.style['margin-right'] = '5px';

                container.appendChild(btn);
            }

            if (rowData.hasOwnProperty('route:publish')) {
                const publishButton = document.createElement('button');
                publishButton.title = rowData['is_published'] === true ? "{{ trans('projectReportChart.unpublish') }}" : "{{ trans('projectReportChart.publish') }}";
                publishButton.className = 'btn btn-xs ' + (rowData['is_published'] === true ? 'btn-default' : 'btn-success');
                publishButton.innerHTML = rowData['is_published'] === true ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
                publishButton.style['margin-right'] = '5px';
                publishButton.dataset.action = 'publishTemplate';
                publishButton.dataset.url = rowData['route:publish'];

                publishButton.addEventListener('click', function (e) {
                    e.preventDefault();

                    $.ajax({
                        url: rowData['route:publish'],
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function (response) {
                            if (response) {
                                if (response.success) {
                                    dataTable.setData();
                                }
                            }
                        },
                        error: function (request, status, error) {
                            // error
                        }
                    });
                });

                container.appendChild(publishButton);
            }

            if(rowData.hasOwnProperty('route:delete')) {
                const deleteButton = document.createElement('a');
                deleteButton.id = 'btnDeleteForm_' + rowData.id;
                deleteButton.dataset.url = rowData['route:delete'];
                deleteButton.dataset.toggle = 'tooltip';
                deleteButton.title = "{{ trans('projectReportChart.deleteTemplate') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.style['margin-right'] = '5px';
                deleteButton.dataset.toggle = 'modal';
                deleteButton.dataset.target = '#yesNoModal';

                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#yesNoModalMessage').html("{{ trans('projectReportChart.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                    $('#yesNoModal [data-action="actionYes"]').data('route_delete', rowData['route:delete']);
                });
                
                container.appendChild(deleteButton);
            }

            return container;
        }

        dataTable = new Tabulator('#data-table', {
            fillHeight: true,
            pagination: "local",
            paginationSize: 30,
            columns: [
                { title:"{{ trans('general.no') }}", width:60, cssClass:"text-center text-middle", headerSort:false, formatter:"rownum" },
                { title:"{{ trans('projectReportChart.title') }}", field: 'title', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: templateLinkFormatter },
                { title:"{{ trans('projectReportChart.projectReportTemplate') }}", field: 'report_type', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('projectReportChart.chartType') }}", field: 'chart_type', width: 200, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: chartTypeFormatter },
                { title:"{{ trans('projectReportChart.dataGrouping') }}", field: 'data_grouping', width: 200, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('projectReportChart.plots') }}", field: 'total_plots', width: 120, cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                //{ title:"{{-- trans('projectReportChart.locked') --}}", field: 'is_locked', width: 120, cssClass:"text-center text-middle", headerSort:false, formatter:tickCrossFormatter },
                { title:"{{ trans('projectReportChart.published') }}", field: 'is_published', width: 120, cssClass:"text-center text-middle", headerSort:false, formatter:tickCrossFormatter },
                { titleFormatter: rearrangeHeader, width: 120, cssClass:"text-center text-middle", headerSort:false, formatter: rearrangeBtnFormatter },
                { title:"{{ trans('general.actions') }}", width: 160, cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
            ],
            layout: "fitColumns",
            ajaxURL: "{{ route('projectReport.chart.template.list') }}",
            placeholder: "{{ trans('projectReportChart.noTemplatesAvailable') }}",
            columnHeaderSortMulti: false,
            movableRows: true, //enable user movable rows
            rowMoved: function(row) {
                rearrangeRows();
            },
        });

        //$(document).on('click', '#lockRevisionYesNoModal [data-action="actionYes"]', lockRevisionHandler);
        $(document).on('click', '#yesNoModal [data-action="actionYes"]', deleteTemplateHandler);

        /*async function lockRevisionHandler(e) {
            e.preventDefault();

            app_progressBar.toggle();

            try {
                const url = $(this).data('route_lock_revision');
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }

                $('#lockRevisionYesNoModal').modal('hide');
                dataTable.setData();
            } catch(err) {
                console.error(err.message);
                SmallErrorBox.refreshAndRetry();
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        }*/

        async function deleteTemplateHandler(e) {
            e.preventDefault();
            e.stopPropagation();

            app_progressBar.toggle();

            try {
                const url = $(this).data('route_delete');
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }

                $('#yesNoModal').modal('hide');
                dataTable.setData();
            } catch(err) {
                console.error(err.message);
                SmallErrorBox.refreshAndRetry();
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        }

        async function rearrangeRows() {
            const url = "{{ route('projectReport.chart.template.rearrange') }}";

            // Get data for all rows
            const allRowData = dataTable.getData();

            // Prepare data for AJAX request
            const data = allRowData.map((rowData, index) => {
                const row = dataTable.getRow(rowData.id);
                const newOrder = row.getPosition() + 1; // Get the new order of the row

                return {
                    id: rowData.id,
                    order: newOrder, // Order is 1-indexed
                };
            });

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    rows: data,
                },
                success: function(response) {
                    if (response) {
                        if (response.success) {
                            dataTable.setData();
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // handle error
                }
            });
        }
    });
</script>	
@endsection