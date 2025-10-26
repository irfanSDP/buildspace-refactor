@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}
		.tabulator .badgeContainer .badge {
			margin: 3px 3px 3px 0;
			padding: 0 5px;
		}
		.tabulator .badgeContainer .badge:last-child {
			margin-right: 0;
		}
		.tabulator .text-wrap {
			white-space: normal !important;
			word-break: break-word;
		}
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
		<li>{{ link_to_route('projectReport.notification.reportTypes', trans('projectReport.reportTypes'), [$project->id, 'permission_type' => 'reminder']) }}</li>
		<li>{{ $mappingTitle }}</li>
		<li>{{ trans('projectReportNotification.title') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-bell"></i> {{ trans('projectReportNotification.title') }}
			</h1>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
			<a href="{{ route('projectReport.notification.create', [$project->id, $mappingId]) }}" class="btn btn-primary pull-right">
				<i class="fa fa-plus"></i> {{ trans('projectReportNotification.newTemplate') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReportNotification.templates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="notificationsTable"></div>
			</div>
		</div>
	</div>

	@include('project_report.template.notification.partials.preview_contents_modal', [
		'modalId' => 'previewContentsModal',
		'title'	  => trans('projectReportNotification.preview'),
	])
	@include('templates.yesNoModal', array(
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ))
@endsection

@section('js')
	<script>
		$(document).ready(function(e) {
			let dataTable = null;

			var notifyDatesFormatter = function(cell, formatterParams, onRendered) {
				var rowData 	= cell.getRow().getData();
				var notifyDatesData	= rowData['notifyDatesData'];

				var container = document.createElement('div');
				container.className = 'badgeContainer';

				$.each(notifyDatesData, function(index, data) {
					var el = document.createElement('span');
					el.className = data['status'] ? 'badge bg-color-greenDark' : 'badge bg-color-red';
					el.innerHTML = data['date'];

					container.appendChild(el);
				});

				return container;
			}

			var statusFormatter = function(cell, formatterParams, onRendered) {
				var table 			 = cell.getTable();
				var row				 = cell.getRow();
                var rowData 	     = row.getData();
				var buttonText       = rowData.isPublished ? "{{ trans('general.activate') }}" : "{{ trans('general.deactivate') }}";
				var buttonColorClass = rowData.isPublished ? 'btn-success' : 'btn-warning';
				var buttonPopupTitle = rowData.isPublished ? "{{ trans('general.activate') }}" : "{{ trans('general.deactivate') }}";

				var togglePublishButton			= document.createElement('a');
				togglePublishButton.id 		 	= 'btnTogglePublish_' + rowData.id;
				togglePublishButton.className 	= 'btn btn-xs ' + buttonColorClass;
				togglePublishButton.innerHTML 	= buttonText;
				togglePublishButton.title 	 	= buttonPopupTitle;

				togglePublishButton.addEventListener('click', function(e) {
					e.preventDefault();

					table.modules.ajax.showLoader();

					$.ajax({
                        type: 'POST',
                        url: rowData['route:publish'],
                        dataType: "json",
						data: { _token:_csrf_token },
                        success: function(response) {
                            if(response.success) {
								table.updateRow(rowData.id, response.row);
								dataTable.setData();
                                table.modules.ajax.hideLoader();
                            }
                        },
                        error: function(){
                            table.modules.ajax.hideLoader();
                        }
                    });
				});

				return togglePublishButton;
            }

			var actionFormatter = function(cell, formatterParams, onRendered) {
				var rowData = cell.getRow().getData();

				var previewButton = document.createElement('a');
				previewButton.id  = 'btnPreview_' + rowData.id;
				previewButton.innerHTML = '<i class="fas fa-search"></i>';
				previewButton.className = 'btn btn-xs btn-primary';
				previewButton.title = "{{ trans('projectReportNotification.preview') }}";
				previewButton.style['margin-right'] = '5px';
				previewButton.dataset.toggle = 'modal';
				previewButton.dataset.target = '#previewContentsModal';

				previewButton.addEventListener('click', function(e) {
					e.preventDefault();

					$('#previewContentsModal div[data-control="contents"]').html('');

					$.ajax({
                        type: 'GET',
                        url: rowData['route:preview'],
                        dataType: 'json',
                        success: function(response) {
							$('#previewContentsModal [data-control="title"]').text(response.subject);

							$('#previewContentsModal div[data-control="contents"]').append(response.body);
                        },
                        error: function(){
                        },
                    });
				});

				var container = document.createElement('div');
				container.appendChild(previewButton);

				if (rowData.hasOwnProperty('route:edit')) {
					var editButton = document.createElement('a');
					editButton.id  = 'btnEdit_' + rowData.id;
					editButton.innerHTML = '<i class="fas fa-edit"></i>';
					editButton.className = 'btn btn-xs btn-primary';
					editButton.title = "{{ trans('projectReportNotification.editTemplate') }}";
					editButton.style['margin-right'] = '5px';
					editButton.style['user-select'] = 'none';
					editButton.href = rowData['route:edit'];

					container.appendChild(editButton);
				}

				if (rowData.hasOwnProperty('route:delete')) {
					var deleteButton = document.createElement('a');
					deleteButton.id  = 'btnDelete_' + rowData.id;
					deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
					deleteButton.className = 'btn btn-xs btn-danger';
					deleteButton.title = "{{ trans('projectReportNotification.deleteTemplate') }}";
					deleteButton.style['user-select'] = 'none';
					deleteButton.dataset.toggle = 'modal';
					deleteButton.dataset.target = '#yesNoModal';

					deleteButton.addEventListener('click', function(e) {
						e.preventDefault();

						$('#yesNoModalMessage').html("{{ trans('projectReportChart.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
						$('#yesNoModal [data-action="actionYes"]').data('route_delete', rowData['route:delete']);
					});

					container.appendChild(deleteButton);
				}

				return container
			}

			dataTable = new Tabulator('#notificationsTable', {
				layout: 'fitColumns',
				ajaxURL: "{{ route('projectReport.notification.list', [$project->id, $mappingId]) }}",
				ajaxConfig: 'GET',
				placeholder: "{{ trans('general.noRecordsFound') }}",
				columnHeaderSortMulti: false,
				pagination: 'local',
				paginationSize: 30,
				columns:[
					{ title:"{{ trans('general.no') }}", width: 30, cssClass:'text-center text-middle', headerSort:false, formatter:'rownum' },
					{ title:"{{ trans('general.name') }}", field: 'templateName', hozAlign:'left', headerFilter:'input', headerSort:false },
					{ title:"{{ trans('projectReportNotification.categoryColumn') }}", field: 'categoryColumn', hozAlign:'left', headerFilter:'input', headerSort:false },
					{ title:"{{ trans('projectReportNotification.valueColumn') }}", field: 'valueColumnContent', width:'180', headerFilter:'input', headerSort:false },
					{ title:"{{ trans('projectReportNotification.period').' '.trans('general.before') }}", field: 'periods', width:'180', headerFilter:'input', headerSort:false },
					{ title:"{{ trans('projectReportNotification.reminderDates') }}", field: 'notifyDates', width:'300', cssClass:'text-wrap', headerFilter:'input', headerSort:false, formatter: notifyDatesFormatter },
					{ title:"{{ trans('general.status') }}", field: 'isPublishedLabels', hozAlign:'center', width:'120', cssClass:'text-center text-middle', headerFilter:'input', headerSort:false, formatter: statusFormatter },
					{ title:"{{ trans('general.actions') }}", hozAlign:'center', width:'120', cssClass:'text-center text-middle', headerSort:false, formatter: actionFormatter }
				],
			});

			$(document).on('click', '#yesNoModal [data-action="actionYes"]', deleteTemplateHandler);

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
		});
	</script>
@endsection