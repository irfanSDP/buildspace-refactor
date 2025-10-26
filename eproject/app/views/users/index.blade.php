@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>

        @if ( $currentUser->isSuperAdmin() )
            <li>
                {{ link_to_route('companies', 'Companies', array()) }}
            </li>
        @endif

        <li>{{{ trans('users.users') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-flag"></i> {{{ trans('users.users') }}} ({{{ $company->name }}})
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            @if($currentUser->canAddUser())
                <a href="{{route('companies.users.create', array($company->id))}}"
                   class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{{ trans('users.addUser') }}}
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="table-responsive">
                <table class="table table-hover" id="dt_basic_users">
                    <thead>
                    <tr>
                        <th style="width:120px;">&nbsp;</th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                    </tr>
                    <tr>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.actions') }}}</th>
                        <th style="vertical-align: middle;">{{{ trans('users.name') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.designation') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.email') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.contactNumber') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.status') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.blocked') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.admin') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.createdAt') }}}</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ( $company->users as $user )
                            <tr>
                                <td style="text-align: center; vertical-align: middle;" class="occupy-min">
                                    <a href="{{ route('companies.users.show', array($company->id, $user->id)) }}" class="btn btn-xs btn-default" title="{{ trans('forms.edit') }}" data-toggle="tooltip">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                    @if ( ! $user->confirmed )
                                        <button type="button" class="btn btn-xs btn-danger" data-delete_route="{{ route('companies.users.delete', array($company->id, $user->id)) }}" data-user_id="{{ $user->id }}" data-action="deleteUser">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <a href="{{ route('companies.users.resend_validation_email', array($company->id, $user->id)) }}" class="btn btn-xs btn-warning" title="{{ trans('general.resend') }}" data-toggle="tooltip">
                                            <i class="fa fa-envelope"></i>
                                        </a>
                                    @endif
                                    @if($currentUser->isSuperAdmin() && $company->usersCanBeTransferred())
                                        <a href="{{ route('users.company.switch', array($user->id)) }}" class="btn btn-xs btn-success" title="{{ trans('users.switchCompany') }}" data-toggle="tooltip">
                                            <i class="fa fa-exchange-alt"></i>
                                        </a>
                                    @endif
                                </td>
                                <td style="vertical-align: middle;">{{{ $user->name }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->designation }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->email }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->contact_number }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->confirmed ? trans('users.confirmed') : trans('users.pending') }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->account_blocked_status ? trans('users.yes') : trans('users.no') }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->isGroupAdmin() ? trans('users.yes') : trans('users.no') }}}</td>
                                <td style="text-align: center; vertical-align: middle;">{{{ $user->created_at->diffForHumans() }}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($currentUser->canImportUsers())
        <br/>

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="pull-left"><i class="fa fa-user"></i><i class="fa fa-plus"></i> {{ trans('users.importedUsers') }}</h1>
                <a class="btn btn-primary btn-md pull-right header-btn" data-toggle="modal" data-target="#selectUsersModal">
                    <i class="fa fa-check-square"></i>
                    {{{ trans('users.selectUsers') }}}
                </a>
                <table class="table table-hover " id="imported_users">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                        </th>
                    </tr>
                    <tr>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.actions') }}}</th>
                        <th style="vertical-align: middle;">{{{ trans('users.name') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.designation') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.email') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.contactNumber') }}}</th>
                        <th style="text-align: center; vertical-align: middle;">{{{ trans('users.admin') }}}</th>
                        <th style="vertical-align: middle;">{{{ trans('users.company') }}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $importedUsers as $user )
                        <tr>
                            <td style="text-align: center; vertical-align: middle;" class="occupy-min">
                                <button type="button" class="btn btn-xs btn-danger" data-delete_route="{{ route('companies.users.deport', array($company->id, $user->id)) }}" data-user_id="{{ $user->id }}" data-action="deleteUser"><i class="fa fa-trash"></i></button>
                            </td>
                            <td style="vertical-align: middle;">{{{ $user->name }}}</td>
                            <td style="text-align: center; vertical-align: middle;">{{{ $user->designation }}}</td>
                            <td style="text-align: center; vertical-align: middle;">{{{ $user->email }}}</td>
                            <td style="text-align: center; vertical-align: middle;">{{{ $user->contact_number }}}</td>
                            <td style="text-align: center; vertical-align: middle;">{{{ $user->isGroupAdmin() ? trans('users.yes') : trans('users.no') }}}</td>
                            <td style="vertical-align: middle;">{{{ $user->company ? $user->company->name : ''}}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    @include('templates.deleteUserConfirmationModal')
    @include('users.pendingTasks.pendingTaskListModal')
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var tenderingPendingTasksUrl = null;
            var tenderingPendingTasksTable = null;

            var postContractPendingTasksUrl = null;
            var postContractPendingTasksTable = null;
            
            var siteModulePendingTasksUrl = null;
            var siteModulePendingTasksTable = null;

            var letterOfAwardUserPermissionsUrl = null;
            var letterOfAwardUserPermissionsTable = null;

            var requestForVariationPermissionsUrl = null;
            var requestForVariationUserPermissionsTable = null;

            var constractManagementUserPermissionsUrl = null;
            var contractManagementUserPermissionsTable = null;

            var siteManagementUserPermissionsUrl = null;
            var siteManagementUserPermissionsTable = null;

            var requestForInspectionUserPermissionsUrl = null;
            var requestForInspectionUserPermissionsTable = null;

            var vendorPerformanceEvaluationApprovalsUrl = null;
            var vendorPerformanceEvaluationCompanyFormsTable = null;

            $("#dt_basic_users thead th input[type=text]").on( 'keyup change', function () {
                table
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            });

            var table = $('#dt_basic_users').DataTable({
                "sDom": "t",
                "bPaginate": false,
                "language": {
                    "emptyTable": "{{ trans('users.noUsers') }}"
                }
            });

            @if($currentUser->canImportUsers())
                $("#imported_users thead th input[type=text]").on( 'keyup change', function () {
                    importedUsersTable
                            .column( $(this).parent().index()+':visible' )
                            .search( this.value )
                            .draw();
                });

                var importedUsersTable = $('#imported_users').DataTable({
                    "sDom": "t",
                    "bPaginate": false,
                    "language": {
                        "emptyTable": "{{ trans('users.noImportedUsers') }}"
                    }
                });
            @endif

            $('[data-action="deleteUser"]').on('click', function(e) {
                var self = $(this);
                var userId = self.data('user_id');
                var validateUrl = "{{ route('company.user.delete.or.deport.pending.tasks.check') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);

                tenderingPendingTasksUrl = "{{ route('company.user.tendering.pending.tasks.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                postContractPendingTasksUrl = "{{ route('company.user.post.contract.pending.tasks.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                siteModulePendingTasksUrl = "{{ route('company.user.post.site.module.tasks.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                letterOfAwardUserPermissionsUrl = "{{ route('company.user.letterOfAward.user.permissions.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                requestForVariationPermissionsUrl = "{{ route('company.user.requestForVariation.user.permissions.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                constractManagementUserPermissionsUrl = "{{ route('company.user.contractManagement.user.permission.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                siteManagementUserPermissionsUrl = "{{ route('company.user.siteManagement.user.permission.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                requestForInspectionUserPermissionsUrl = "{{ route('company.user.request.for.inspection.user.permission.get') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
                vendorPerformanceEvaluationApprovalsUrl = "{{ route('company.user.getVendorPerformanceEvaluationApprovals') }}".replace('%7BcompanyId%7D', "{{ $company->id }}").replace('%7BuserId%7D', userId);
            
                app_progressBar.show();
                app_progressBar.maxOut(200, function() {
                    $.ajax({
                        url: validateUrl,
                        method: 'GET',
                        success: function(response) {
                            if(response.companyUserCanBeRemoved) {
                                $('#yesNoForm').attr('action', self.data('delete_route'));
                                $('#deleteUserConfirmationModal').modal('show');
                            } else {
                                $('#pendingTaskListModal').modal('show');
                            }

                            app_progressBar.hide();
                        },
                    });
                });
            });

            $('#pendingTaskUsersModal').on('hidden.bs.modal', function() {
				pendingTasksUsersTable = null;
			});

			$('#pendingTaskListModal').on('show.bs.modal', function() {
                // select the first tab
                $('#tabPanes a[href="#tenderingPendingTasksTab"]').tab('show');
            });

            $('#pendingTaskListModal').on('shown.bs.modal', function() {
                // render the table in the first tab
                renderTenderingPendingTasksTable();
            });

            $('#pendingTaskListModal').on('hidden.bs.modal', function() {
                if(tenderingPendingTasksTable instanceof Tabulator) {
                    tenderingPendingTasksTable.destroy();
                    tenderingPendingTasksTable = null;
                }

                if(postContractPendingTasksTable instanceof Tabulator) {
                    postContractPendingTasksTable.destroy();
                    postContractPendingTasksTable = null;
                }

                if(siteModulePendingTasksTable instanceof Tabulator) {
                    siteModulePendingTasksTable.destroy();
                    siteModulePendingTasksTable = null;
                }

                if(letterOfAwardUserPermissionsTable instanceof Tabulator) {
                    letterOfAwardUserPermissionsTable.destroy();
                    letterOfAwardUserPermissionsTable = null;
                }

                if(requestForVariationUserPermissionsTable instanceof Tabulator) {
                    requestForVariationUserPermissionsTable.destroy();
                    requestForVariationUserPermissionsTable = null;
                }

                if(contractManagementUserPermissionsTable instanceof Tabulator) {
                    contractManagementUserPermissionsTable.destroy();
                    contractManagementUserPermissionsTable = null;
                }

                if(siteManagementUserPermissionsTable instanceof Tabulator) {
                    siteManagementUserPermissionsTable.destroy();
                    siteManagementUserPermissionsTable = null;
                }

                if(requestForInspectionUserPermissionsTable instanceof Tabulator) {
                    requestForInspectionUserPermissionsTable.destroy();
                    requestForInspectionUserPermissionsTable = null;
                }

                if(vendorPerformanceEvaluationApprovalsTable instanceof Tabulator) {
                    vendorPerformanceEvaluationApprovalsTable.destroy();
                    vendorPerformanceEvaluationApprovalsTable = null;
                }
            });

			$('#tabPanes').on('shown.bs.tab', function(e) {
                var activeTabId = e.target.id;

                switch(activeTabId)
                {
                    case 'tenderingPendingTasksTarget':
                        if(!(tenderingPendingTasksTable instanceof Tabulator)) {
                            renderTenderingPendingTasksTable();
                        }
                        break;
                    case 'postContractPendingTasksTarget':
                        if(!(postContractPendingTasksTable instanceof Tabulator)) {
                            renderPostContractPendingTasksTable();
                        }
                        break;
                    case 'siteModulePendingTasksTarget':
                        if(!(siteModulePendingTasksTable instanceof Tabulator)) {
                            renderSiteModulePendingTasksTable();
                        }
                        break;
                    case 'letterOfAwardUserPermissionsTarget':
                        if(!(letterOfAwardUserPermissionsTable instanceof Tabulator)) {
                            renderLetterOfAwardUserPermissionsTable();
                        }
                        break;
                    case 'requestForVariationUserPermissionsTarget':
                        if(!(requestForVariationUserPermissionsTable instanceof Tabulator)) {
                            renderRequestForVariationUserPermissionsTable();
                        }
                        break;
                    case 'contractManagementUserPermissionsTarget':
                        if(!(contractManagementUserPermissionsTable instanceof Tabulator)) {
                            renderContractManagementUserPermissionsTable();
                        }
                        break;
                    case 'siteManagementUserPermissionsTarget':
                        if(!(siteManagementUserPermissionsTable instanceof Tabulator)) {
                            renderSiteManagementUserPermissionsTable();
                        }
                        break;
                    case 'requestForInspectionUserPermissionsTarget':
                        if(!(requestForInspectionUserPermissionsTable instanceof Tabulator)) {
                            renderRequestForInspectionUserPermissionsTable();
                        }
                        break;
                    case 'vendorPerformanceEvaluationApprovalsTarget':
                        if(!(vendorPerformanceEvaluationApprovalsTable instanceof Tabulator)) {
                            renderVendorPerformanceEvaluationApprovalsTable();
                        }
                        break;
                    default:
                        // nothing here
                }
            });

            function renderTenderingPendingTasksTable() {
                tenderingPendingTasksTable = new Tabulator("#tenderingPendingTasksTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: tenderingPendingTasksUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('general.module') }}", field: 'module', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderPostContractPendingTasksTable() {
                postContractPendingTasksTable = new Tabulator("#postContractPendingTasksTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: postContractPendingTasksUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('general.module') }}", field: 'module', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderSiteModulePendingTasksTable() {
                siteModulePendingTasksTable = new Tabulator("#siteModulePendingTasksTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: siteModulePendingTasksUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('general.module') }}", field: 'module', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderLetterOfAwardUserPermissionsTable() {
                letterOfAwardUserPermissionsTable = new Tabulator("#letterOfAwardUserPermissionsTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: letterOfAwardUserPermissionsUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                        {title:"{{ trans('general.role') }}", field: 'role', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderRequestForVariationUserPermissionsTable() {
                requestForVariationUserPermissionsTable = new Tabulator("#requestForVariationUserPermissionsTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: requestForVariationPermissionsUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                        {title:"{{ trans('requestForVariation.permissionGroup') }}", field: 'permission_group', align:"left", width: 120, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('general.role') }}", field: 'role', align:"left", width: 220, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderContractManagementUserPermissionsTable() {
                contractManagementUserPermissionsTable = new Tabulator("#contractManagementUserPermissionsTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: constractManagementUserPermissionsUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                        {title:"{{ trans('general.module') }}", field: 'module', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderSiteManagementUserPermissionsTable() {
                siteManagementUserPermissionsTable = new Tabulator("#siteManagementUserPermissionsTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: siteManagementUserPermissionsUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                        {title:"{{ trans('general.module') }}", field: 'module', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderRequestForInspectionUserPermissionsTable() {
                requestForInspectionUserPermissionsTable = new Tabulator("#requestForInspectionUserPermissionsTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: requestForInspectionUserPermissionsUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                        {title:"{{ trans('general.module') }}", field: 'module', align:"left", width: 150, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('inspection.group') }}", field: 'group', align:"left", width: 150, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('inspection.role') }}", field: 'role', align:"left", width: 150, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                    ],
                });
            }

            function renderVendorPerformanceEvaluationApprovalsTable() {
                vendorPerformanceEvaluationApprovalsTable = new Tabulator("#vendorPerformanceEvaluationApprovalsTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: vendorPerformanceEvaluationApprovalsUrl,
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('projects.reference') }}", field: 'project_reference', align:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                        {title:"{{ trans('projects.project') }}", field: 'project_title', align:"left", headerFilter: "input", headerSort:false },
                        {title:"{{ trans('companies.company') }}", field: 'evaluated_company', align:"left", width: 250, frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
                    ],
                });
            }
        });

    </script>

    <!-- Import Users -->
    @if($currentUser->canImportUsers())
        @include('users.partials.select_users_modal', array(
            'dataSource'=>$selectUserDataSource,
            'submitUrl' => $importUsersUrl))
    @endif

@endsection