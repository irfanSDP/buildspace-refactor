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
        <li>{{ trans('projectReport.projectReport') . ' ' . trans('projectReport.userPermissions') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('projectReport.userPermissions') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div id="project-report-types-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.generic_table_modal', [
        'modalId'    => 'assignedUsersModal',
        'title'      => trans('projectReport.assignUsers'),
        'tableId'    => 'assignedUsersTable',
        'showCancel' => true,
    ])
    @include('templates.generic_table_modal', [
        'modalId'    => 'assignUsersModal',
        'title'      => trans('projectReport.assignUsers'),
        'tableId'    => 'assignUsersTable',
        'showSubmit' => true,
        'showCancel' => true,
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ])
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const buttonWithCountFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                const {role} = formatterParams;
                const {
                    count: assignedUsersCount,
                    route_assigned_users: routeAssignedUsers,
                    route_assignable_users: routeAssignableUsers,
                } = rowData[role];

                if (rowData[role].enable === true) {
                    const buttonWithCount = document.createElement('button');
                    buttonWithCount.title = "{{ trans('projectReport.assignUsers') }}";
                    buttonWithCount.className = "btn btn-xs btn-warning";
                    buttonWithCount.innerHTML = `${assignedUsersCount} {{ trans('general.usersBracket') }}`;
                    buttonWithCount.dataset.toggle = 'modal';
                    buttonWithCount.dataset.target = '#assignedUsersModal';

                    buttonWithCount.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#assignedUsersModal').data('report_type_id', rowData.report_type_id);
                        $('#assignedUsersModal').data('identifier', rowData[role].identifier);
                    });
                    return buttonWithCount;
                } else {
                    const spanText = document.createElement('span');
                    spanText.title = "{{ trans('projectReportNotification.onlyAvailableForLatestRev') }}";
                    spanText.innerHTML = "{{ trans('general.notAvailable') }}";
                    return spanText;
                }
			}

            const projectReportTypesTable = new Tabulator('#project-report-types-table', {
                height:500,
                pagination:"local",
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('projectReport.title') }}", field: 'title', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    {
                        title:"{{ trans('projectReport.projectReport') }}", headerSort:false, cssClass:"text-center text-middle",
                        columns: [
                            { title:"{{ trans('general.submitters') }}", width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter: buttonWithCountFormatter, formatterParams: {role: 'submitter'} },
                            { title:"{{ trans('general.verifiers') }}", width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter: buttonWithCountFormatter, formatterParams: {role: 'verifier'} },
                        ]
                    },
                    {
                        title:"{{ trans('projectReportNotification.title') }}", headerSort:false, cssClass:"text-center text-middle",
                        columns: [
                            { title:"{{ trans('projectReportNotification.editors') }}", width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter: buttonWithCountFormatter, formatterParams: {role: 'editor'} },
                            { title:"{{ trans('projectReportNotification.receivers') }}", width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter: buttonWithCountFormatter, formatterParams: {role: 'receiver'} },
                        ]
                    },
                ],
                layout:"fitColumns",
                ajaxURL: "{{ route('projectReport.userPermissions.reportTypes.list', [$project->id]) }}",
                placeholder:"{{ trans('projectReport.noTemplatesAvailable') }}",
                columnHeaderSortMulti:false,
            });

            const checkFuturePendingTasks = async userPermissionId => {
                try {
                    const url = new URL("{{ route('projectReport.userPermissions.futurePendingTasks.check', [$project->id]) }}");
                    url.searchParams.append('userPermissionId', userPermissionId);

                    const promise = await fetch(url);

                    if(!promise.ok || (promise.status !== 200)) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    return await promise.json();
                } catch(err) {
                    console.error(err.message);
                }
            };

            const actionsFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();
                const container = document.createElement('div');

                const deleteButton = document.createElement('a');
                deleteButton.dataset.toggle = 'tooltip';
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.style['margin-right'] = '5px';

                deleteButton.addEventListener('click', async function(e) {
                    e.preventDefault();

                    //app_progressBar.toggle();

                    try {
                        const pendingTasksCheck = await checkFuturePendingTasks(rowData.id);
    
                        if(pendingTasksCheck.hasPendingTasks) {
                            $.smallBox({
                                title : "{{ trans('projectReport.unableToRevokePermission') }}",
                                content : "<i class='fa fa-close'></i> <i>{{ trans('projectReport.userHasPendingTasks') }}</i>",
                                color : "#C46A69",
                                sound: true,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                            
                            return;
                        }

                        $('#yesNoModalMessage').html("{{ trans('projectReport.userWillBeRemoved') . ' ' . trans('general.sureToProceed') }}");
                        $('#yesNoModal [data-action="actionYes"]').data('user_permission_id', rowData.id);
                        $('#yesNoModal').modal('show');
                    } catch(err) {
                        console.error(err.message);
                    } finally {
                        app_progressBar.maxOut();
                        app_progressBar.hide();
                    }
                });
                
                container.appendChild(deleteButton);

                return container;
            }

            let assignedUsersTable = null;

            $('#assignedUsersModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                const reportTypeId = $(this).data('report_type_id');
                const identifier = $(this).data('identifier');

                assignedUsersTable = new Tabulator('#assignedUsersTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('users.name') }}", field: 'user_name', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                        { title:"{{ trans('users.email') }}", field: 'user_email', width: 300, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxConfig: "GET",
                    ajaxURL: "{{ route('projectReport.userPermissions.assignedUsers.get', [$project->id]) }}",
                    ajaxParams: {
                        reportTypeId,
                        identifier,
                    },
                    placeholder:"{{ trans('general.noRecordsFound') }}",
                    columnHeaderSortMulti:false,
                });

                $(this).find('.modal-footer').find('button[data-action="assign_users"]').remove();
                $(this).find('.modal-footer').append(`<button class="btn btn-warning btn-md pull-left" data-action="assign_users" data-report_type_id="${reportTypeId}" data-identifier="${identifier}"><i class="fa fa-check-square"></i> {{{ trans("projectReport.assignUsers") }}}</button>`);
            });

            $(document).on('click', '#assignedUsersModal [data-action="assign_users"]', function() {
                const reportTypeId = $(this).data('report_type_id');
                const identifier = $(this).data('identifier');

                $('#assignUsersModal').data('report_type_id', reportTypeId);
                $('#assignUsersModal').data('identifier', identifier);
                $('#assignUsersModal').modal('show');
            });

            let assignUsersTable = null;

            $('#assignUsersModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                const reportTypeId = $(this).data('report_type_id');
                const identifier = $(this).data('identifier');

                assignUsersTable = new Tabulator('#assignUsersTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { formatter:"rowSelection", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('users.name') }}", field: 'name', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                        { title:"{{ trans('users.email') }}", field: 'email', width: 300, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                    ],
                    layout:"fitColumns",
                    ajaxConfig: "GET",
                    ajaxURL: "{{ route('projectReport.userPermissions.assignableUsers.get', [$project->id]) }}",
                    ajaxParams: {
                        reportTypeId,
                        identifier,
                    },
                    placeholder:"{{ trans('general.noRecordsFound') }}",
                    columnHeaderSortMulti:false,
                    rowSelectionChanged: function(data, rows) {
                        $('#assignUsersModal [data-action="actionSave"]').prop('disabled', rows.length <= 0);
                    },
                });
            });

            $(document).on('click', '#assignUsersModal [data-action="actionSave"]', saveSelectedUsersHandler);
            $(document).on('click', '#yesNoModal [data-action="actionYes"]', revokeUserPermissionHandler);

            async function updateTableData(table) {
                let currentPage = table.getPage();
                await table.setData();
                table.setPage(currentPage);
            }

            async function saveSelectedUsersHandler(e) {
                e.preventDefault();
                e.stopPropagation();

                app_progressBar.toggle();

                try {
                    const url = "{{ route('projectReport.userPermissions.grant', [$project->id]) }}";
                    const selectedData = assignUsersTable.getSelectedData();
                    const selectedIds = selectedData.map(data => data.id);
                    const reportTypeId = $('#assignUsersModal').data('report_type_id');
                    const identifier = $('#assignUsersModal').data('identifier');

                    const options = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            reportTypeId,
                            identifier,
                            userIds: selectedIds,
                            _token: '{{{ csrf_token() }}}'
                        }),
                    };

                    const promise = await fetch(url, options);
                    const response = await promise.json();

                    if(!promise.ok || (promise.status !== 200) || !response.success) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    $('#assignUsersModal').modal('hide');

                    await updateTableData(assignedUsersTable);
                    await updateTableData(projectReportTypesTable);
                } catch(err) {
                    console.error(err.message);
                    SmallErrorBox.refreshAndRetry();
                } finally {
                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            };

            async function revokeUserPermissionHandler(e) {
                e.preventDefault();
                e.stopPropagation();

                app_progressBar.toggle();

                try {
                    const userPermissionId = $(this).data('user_permission_id');
                    const url = "{{ route('projectReport.userPermissions.revoke', [$project->id]) }}";
                    const options = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            userPermissionId,
                            _token: '{{{ csrf_token() }}}'
                        }),
                    };

                    const promise = await fetch(url, options);
                    const response = await promise.json();

                    if(!promise.ok || (promise.status !== 200) || !response.success) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    await updateTableData(projectReportTypesTable);
                    await updateTableData(assignedUsersTable);
                } catch(err) {
                    console.error(err.message);
                    SmallErrorBox.refreshAndRetry();
                } finally {
                    $('#yesNoModal').modal('hide');

                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            };
        });
    </script>
@endsection