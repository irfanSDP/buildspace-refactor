@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
          border: none;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ trans('projectReport.projectReportDashboard') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReport.projectReportType') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReport.projectReportType') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="project-report-types-list-table"></div>
			</div>
		</div>
	</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const linkFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                const link = document.createElement('a');
                link.href = rowData['route:show'];
                link.innerHTML = rowData.title;
                link.dataset.toggle = 'tooltip';
                link.title = "{{ trans('projectReport.projectReport') }}";
                link.style['user-select'] = 'none';

                return link;
            }

            const projectReportTypesListTable = new Tabulator('#project-report-types-list-table', {
                fillHeight: true,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
                pagination:"local",
                paginationSize: 50,
                layout:"fitColumns",
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('projectReport.title') }}", field: 'title', headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: linkFormatter },
                ],
                ajaxConfig: 'GET',
                ajaxURL: "{{ route('projectReport.dashboard.projectTypes.list') }}",
            });
        });
    </script>
@endsection