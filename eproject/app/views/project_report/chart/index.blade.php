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
		<li>{{ trans('projectReportChart.projectReportChart') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReportChart.projectReportChart') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReportChart.charts') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="data-table"></div>
			</div>
		</div>
	</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let dataTable = null;

        const linkFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            if (rowData.hasOwnProperty('route:show')) {
                let templateLink = document.createElement('a');
                templateLink.href = rowData['route:show'];
                templateLink.target = '_blank';
                templateLink.innerHTML = rowData.title;
                templateLink.dataset.toggle = 'tooltip';
                templateLink.title = "{{ trans('projectReportChart.showChart') }}";
                templateLink.style['user-select'] = 'none';
                return templateLink;
            } else {
                return rowData.title;
            }
        }

        const actionsFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            let container = document.createElement('div');
            container.style.textAlign = 'left';

            if (rowData.hasOwnProperty('route:show')) {
                const btn = document.createElement('a');
                btn.href = rowData['route:show'];
                btn.target = '_blank';
                btn.dataset.toggle = 'tooltip';
                btn.title = "{{ trans('projectReportChart.showChart') }}";
                btn.className = 'btn btn-xs btn-primary';
                btn.innerHTML = '<i class="fa fa-chart-line"></i>';
                //btn.style['margin-right'] = '5px';

                container.appendChild(btn);
            }

            return container;
        }

        dataTable = new Tabulator('#data-table', {
            fillHeight: true,
            pagination: "local",
            paginationSize: 30,
            columns: [
                { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('projectReportChart.title') }}", field: 'title', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: linkFormatter },
                { title:"{{ trans('projectReportChart.chartType') }}", field: 'chart_type', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('projectReportChart.projectReportTemplate') }}", field: 'report_type', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('general.actions') }}", width: 120, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
            ],
            layout: "fitColumns",
            ajaxURL: "{{ route('projectReport.charts.list') }}",
            placeholder: "{{ trans('projectReportChart.noChartsAvailable') }}",
            columnHeaderSortMulti: false,
        });
    });
</script>	
@endsection