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
        <li>{{ link_to_route('projectReport.template.index', trans('projectReport.templates'), []) }}</li>
        <li>{{ link_to_route('projectReport.type.index', trans('projectReport.reportTypes'), []) }}</li>
		<li>{{ $reportType->title }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReport.templateMappings') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReport.templateMappings') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="project-report-template-mapping-bindings-list-table"></div>
			</div>
		</div>
	</div>
    @include('templates.generic_table_modal', [
		'modalId'    => 'templateSelectionModal',
		'title'      => trans('projectReport.approvedTemplates'),
		'tableId'    => 'templateSelectionTable',
        'showSubmit' => true,
		'showCancel' => true,
		'cancelText' => trans('forms.close'),
	])
    @include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'message'   => trans('projectReport.sureToUnlinkTemplate'),
    ])
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            let templateSelectionTable = null;

            const actionsFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                const container = document.createElement('div');

                if (rowData.hasOwnProperty('route:bind')) {
                    const bindTemplateButton = document.createElement('a');
                    bindTemplateButton.dataset.toggle = 'tooltip';
                    bindTemplateButton.title = "{{ trans('projectReport.bindTemplate') }}";
                    bindTemplateButton.className = 'btn btn-xs btn-primary';
                    bindTemplateButton.innerHTML = '<i class="fas fa-link"></i>';
                    bindTemplateButton.style['margin-right'] = '5px';
                    bindTemplateButton.dataset.toggle = 'modal';
                    bindTemplateButton.dataset.target = '#templateSelectionModal';

                    bindTemplateButton.addEventListener('click', function(e) {
                        $('#templateSelectionModal [data-action="actionSave"]').data('url', rowData['route:bind']);

                        if(cell.getRow().getData().hasOwnProperty('template_id')) {
                            $('#templateSelectionModal').data('template_id', rowData.template_id);
                        } else {
                            $('#templateSelectionModal').data('template_id', null);
                        }
                    });
                    container.appendChild(bindTemplateButton);
                }

                if (rowData.hasOwnProperty('route:toggle_latest_rev')) {
                    const showLatestRevisionButton = document.createElement('button');
                    showLatestRevisionButton.dataset.id = 'btnShowLatestRev_' + rowData.id;
                    showLatestRevisionButton.dataset.title = "{{ trans('projectReport.latestRevSettingButton') }}";
                    showLatestRevisionButton.title = "{{ trans('projectReport.latestRevSettingButton') }}";
                    showLatestRevisionButton.className = 'btn btn-xs btn-primary';
                    showLatestRevisionButton.innerHTML = rowData.latest_rev ? '<i class="fas fa-list-ol"></i>' : '<i class="fas fa-1"></i>';
                    showLatestRevisionButton.style['margin-right'] = '5px';

                    showLatestRevisionButton.addEventListener('click', function (e) {
                        e.preventDefault();

                        $.ajax({
                            url: rowData['route:toggle_latest_rev'],
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            success: function (data) {
                                if (data) {
                                    if (data.success) {
                                        projectReportTemplateMappingBindingsListTable.setData();
                                    }
                                }
                            },
                            error: function (request, status, error) {
                                // error
                            }
                        });
                    });

                    container.appendChild(showLatestRevisionButton);
                }

                if (rowData.hasOwnProperty('route:lock')) {
                    const lockTemplateBtn = document.createElement('button');
                    lockTemplateBtn.dataset.id = 'btnLockTemplate_' + rowData.id;
                    lockTemplateBtn.dataset.title = "{{ trans('projectReport.lockTemplate') }}";
                    lockTemplateBtn.title = "{{ trans('projectReport.lockTemplate') }}";
                    lockTemplateBtn.className = 'btn btn-xs btn-primary';
                    lockTemplateBtn.innerHTML = '<i class="fas fa-lock"></i>';
                    lockTemplateBtn.style['margin-right'] = '5px';

                    lockTemplateBtn.addEventListener('click', function (e) {
                        e.preventDefault();

                        $.ajax({
                            url: rowData['route:lock'],
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            success: function (data) {
                                if (data) {
                                    if (data.success) {
                                        projectReportTemplateMappingBindingsListTable.setData();
                                    }
                                }
                            },
                            error: function (request, status, error) {
                                // error
                            }
                        });
                    });
                    container.appendChild(lockTemplateBtn);
                }

                return container;
            };

            const projectReportTemplateMappingBindingsListTable = new Tabulator('#project-report-template-mapping-bindings-list-table', {
                fillHeight: true,
                pagination: "local",
                paginationSize: 30,
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('projectReport.projectType') }}", field: 'project_type', width:120, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('projectReport.title') }}", field: 'template_title', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('projectReport.latestRevSetting') }}", field: 'show_latest_rev', width:150, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
                ],
                layout: "fitColumns",
                ajaxURL: "{{ route('projectReport.type.mappings.list', [$reportType->id]) }}",
                placeholder: "{{ trans('projectReport.noTemplatesAvailable') }}",
                columnHeaderSortMulti: false,
            });

            $('#templateSelectionModal').on('shown.bs.modal', function (e) {
                e.preventDefault();

                templateSelectionTable = new Tabulator('#templateSelectionTable', {
                    height:300,
                    columns: [
                        { formatter:"rowSelection", width:30, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false },
                        { title: "{{ trans('projectReport.title') }}", field:"title", cssClass:"text-left", align: 'left', headerSort: false, headerFilter: 'input' },
						{ title: "{{ trans('projectReport.revision') }}", field: 'revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('projectReport.latest.approved.templates.list') }}",
                    ajaxConfig: "GET",
                    pagination:"local",
                    selectable: 1,
                    placeholder:"{{{ trans('general.noRecordsFound') }}}",
                    columnHeaderSortMulti:false,
                    dataLoaded: function(data) {
                        const templateId = $('#templateSelectionModal').data('template_id');

                        if(templateId == null) return;

                        this.selectRow(templateId);
                    },
                    
                    rowSelectionChanged:function(data, rows){
                        $('#templateSelectionModal [data-action="actionSave"]').prop('disabled', (rows.length == 0));
                    },
                });
            });

            $(document).on('click', '#templateSelectionModal [data-action="actionSave"]', bindTemplateHandler);
            $(document).on('click', '#yesNoModal [data-action="actionYes"]', unbindTemplateHandler);

            async function bindTemplateHandler(e) {
                e.preventDefault();

                app_progressBar.toggle();

                const url = $(this).data('url');
                const [templateId] = templateSelectionTable.getSelectedData().map(data => data.id);

                try {
                    const options = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            templateId: templateId,
                            _token: '{{{ csrf_token() }}}'
                        }),
                    };

                    const promise = await fetch(url, options);

                    if(!promise.ok || (promise.status !== 200)) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    const response = await promise.json();

                    if(!response.success) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }
                    
                    $('#templateSelectionModal').modal('hide');
                    projectReportTemplateMappingBindingsListTable.setData();
                } catch(err) {
                    console.error(err.message);
                    SmallErrorBox.refreshAndRetry();
                } finally {
                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            }

            async function unbindTemplateHandler(e) {
                e.preventDefault();

                const url = $(this).data('url');

                try {
                    const options = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            _token: '{{{ csrf_token() }}}'
                        }),
                    };

                    const promise = await fetch(url, options);

                    if(!promise.ok || (promise.status !== 200)) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    const response = await promise.json();

                    if(!response.success) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }
                    
                    $('#yesNoModal').modal('hide');
                    projectReportTemplateMappingBindingsListTable.setData();
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