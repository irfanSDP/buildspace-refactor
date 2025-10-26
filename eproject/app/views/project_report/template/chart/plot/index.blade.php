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
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projectReport.chart.template.index', trans('projectReportChart.templates'), array()) }}</li>
        <li>{{ $chart->title }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReportChart.plots') }}
			</h1>
		</div>

        {{--@if (! $chart->is_locked)--}}
		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 button-row">
            <a href="{{ route('projectReport.chart.plot.template.create', array($chart->id)) }}" class="btn btn-primary btn-md pull-right header-btn">
				<i class="fa fa-plus"></i> {{ trans('projectReportChart.newPlot') }}
			</a>
		</div>
        {{--@endif--}}
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReportChart.plots') }} </h2>
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

        const templateLinkFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            if (rowData.hasOwnProperty('route:edit')) {
                let templateLink = document.createElement('a');
                templateLink.href = rowData['route:edit'];
                templateLink.innerHTML = rowData['plot_type'];
                templateLink.dataset.toggle = 'tooltip';
                templateLink.title = "{{ trans('projectReportChart.editTemplate') }}";
                templateLink.style['user-select'] = 'none';
                return templateLink;
            } else {
                return rowData['plot_type'];
            }
        }

        const actionsFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            let container = document.createElement('div');
            container.style.textAlign = "left";

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

            if (rowData.hasOwnProperty('route:delete')) {
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
                { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('projectReportChart.plotType') }}", field: 'plot_type', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: templateLinkFormatter },
                { title:"{{ trans('projectReportChart.dataGrouping') }}", field: 'data_grouping', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('projectReportChart.categoryColumn') }}", field: 'category_column', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('projectReportChart.valueColumn') }}", field: 'value_column', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('projectReportChart.accumulative') }}", field: 'is_accumulated', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('general.actions') }}", width: 120, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
            ],
            layout: "fitColumns",
            ajaxURL: "{{ route('projectReport.chart.plot.template.list', array($chart->id)) }}",
            placeholder: "{{ trans('projectReportChart.noTemplatesAvailable') }}",
            columnHeaderSortMulti: false,
        });

        $(document).on('click', '#yesNoModal [data-action="actionYes"]', deleteTemplateHandler);
        $(document).on('click', '#lockRevisionYesNoModal [data-action="actionYes"]', lockRevisionHander);

        async function lockRevisionHander(e) {
            e.preventDefault();

            const url = $(this).data('route_lock_revision');

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
        };

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
        };
    });
</script>	
@endsection