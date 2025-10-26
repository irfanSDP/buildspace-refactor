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
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        @if ($permissionType === 'submit')
            <li>{{ link_to_route('projectReport.index', trans('projectReport.submitReport'), [$project->id]) }}</li>
        @elseif ($permissionType === 'reminder')
            <li>{{ link_to_route('projectReport.notification.reportTypes', trans('projectReportNotification.navigationTitle'), [$project->id, 'permission_type' => 'reminder']) }}</li>
        @endif
        <li>{{ trans('projectReport.projectReportType') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-file-lines"></i> {{ trans('projectReport.projectReports') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReport.projectReports') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="project-reports-list-table"></div>
			</div>
		</div>
	</div>
    @include('templates.generic_table_modal', [
		'modalId'    => 'previousRevisionReportModal',
		'title'      => trans('projectReport.previousRevisions'),
		'tableId'    => 'previousRevisionReportTable',
		'showCancel' => true,
		'cancelText' => trans('forms.close'),
	])
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            let previousRevisionReportTable = null;

            const templateLinkFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                const templateLink = document.createElement('a');
                templateLink.href = rowData['route:show'];
                templateLink.innerHTML = rowData.mapping_title;
                templateLink.dataset.toggle = 'tooltip';
                templateLink.title = "{{ trans('projectReport.designTemplate') }}";
                templateLink.style['user-select'] = 'none';

                return templateLink;
            }

            const revisionFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                if(rowData.project_report_revision === undefined) return 'N/A';

                return rowData.project_report_revision == 0 ? "{{ trans('projectReport.original') }}" : rowData.project_report_revision;
            }

            const statusFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                if(rowData.project_report_status === undefined) return 'N/A';

                return rowData.project_report_status == 0 ? "{{ trans('projectReport.original') }}" : rowData.project_report_status;
            }

            const previousRevisionsFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                if(rowData.project_report_revision === null || rowData.project_report_revision == 0) return null;

                const viewPreviousRevisionButton = document.createElement('a');
                viewPreviousRevisionButton.title = "{{ trans('projectReport.previousRevisions') }}";
                viewPreviousRevisionButton.className = "btn btn-xs btn-success";
                viewPreviousRevisionButton.innerHTML = '<i class="fa fa-eye"></i>';
                viewPreviousRevisionButton.style['margin-right'] = '5px';
                viewPreviousRevisionButton.href = '#';
                viewPreviousRevisionButton.dataset.toggle = 'modal';
                viewPreviousRevisionButton.dataset.target = '#previousRevisionReportModal';

                viewPreviousRevisionButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#previousRevisionReportModal').data('url', rowData['route:previousRevisions']);
                });

                return viewPreviousRevisionButton;
            };

            const viewReportButtonFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

				const viewReportButton = document.createElement('a');
				viewReportButton.title = "{{ trans('general.view') }}";
				viewReportButton.className = "btn btn-xs btn-success";
				viewReportButton.innerHTML = '<i class="fas fa-eye"></i>';
				viewReportButton.href = rowData['route:show'];
				viewReportButton.target = '_blank';

				return viewReportButton;
			}

            const projectReportsListTable = new Tabulator('#project-reports-list-table', {
                fillHeight: true,
                pagination:"local",
                paginationSize: 50,
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('projectReport.title') }}", field: 'mapping_title', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: templateLinkFormatter },
                    { title:"{{ trans('projectReport.revision') }}", field: 'project_report_revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: revisionFormatter },
                    { title:"{{ trans('general.status') }}", field: 'status_text', width: 160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: statusFormatter },
                    { title:"{{ trans('projectReport.previousRevisions') }}", width: 120, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: previousRevisionsFormatter },
                ],
                layout:"fitColumns",
                ajaxURL: "{{ route('projectReport.mappings.list', [$project->id, 'permission_type' => $permissionType]) }}",
                placeholder:"{{ trans('projectReport.noTemplatesAvailable') }}",
                columnHeaderSortMulti:false,
            });

            const previousRevisionFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                if(rowData.revision === undefined) return 'N/A';

                return rowData.revision == 0 ? "{{ trans('projectReport.original') }}" : rowData.revision;
            }

            $(document).on('shown.bs.modal', '#previousRevisionReportModal', function(e) {
				e.preventDefault();

				const url = $(this).data('url');

				previousRevisionReportTable = new Tabulator('#previousRevisionReportTable', {
                    height:300,
                    columns: [
                        { title: "{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title: "{{ trans('projectReport.title') }}", field:"title", cssClass:"text-left", align: 'left', headerSort: false, headerFilter: 'input' },
						{ title: "{{ trans('projectReport.revision') }}", field: 'revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: previousRevisionFormatter },
						{ title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: viewReportButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    ajaxConfig: "GET",
                    pagination:"local",
                    placeholder:"{{{ trans('general.noRecordsFound') }}}",
                    columnHeaderSortMulti:false,
                });
			})
        });
    </script>
@endsection