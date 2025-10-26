@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        @if($contractGroup)
		<li>{{ trans('projects.assignUserFromGroup') }} ({{{ $project->getRoleName($contractGroup->group) }}}) {{ trans('projects.toProject') }}</li>
        @endif
	</ol>

	@include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('users.assignUsers') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8 mb-4">
            <div class="btn-group pull-right header-btn">
                @include('projects.partials.assign_users_actions_menu')
            </div>
        </div>
    </div>

    <div class="well">
        @if($contractGroup)
        	{{ Form::open(array('method' => 'PUT')) }}
        		<div class="table-responsive">
                    <h2>{{ trans('projects.assignUserFromGroup') }} ({{{ $project->getRoleName($contractGroup->group) }}}) {{ trans('projects.toProject') }}</h2>
        			<table class="table" style="text-align: center;">
        				<thead>
        					<tr>
        						<th style="text-align: center;width:120px;">{{ trans('users.viewer') }} / {{ trans('users.verifier') }}</th>
        						<th style="text-align: center;width:100px;">{{ trans('users.editor') }}</th>
        						<th style="text-align: left;">{{ trans('users.name') }}</th>
        						<th style="text-align: center;width:200;">{{ trans('users.designation') }}</th>
                                <th style="text-align: center;width:100px;">{{ trans('users.admin') }}</th>
                                <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
        					</tr>
        				</thead>
        				<tbody>
        					@foreach ($users as $user)
        					<tr>
        						<td>
                                    @if( $company->isCompanyAdmin($user))
                                        {{ Form::checkbox('selected_users[]', $user->id, isset($assignedUsers[$user->id]) ? true : false, array('disabled' => true)) }}
                                    @elseif (isset($eBidding))
                                        @if($eBidding->isApproved())
                                            {{ Form::checkbox('selected_users[]', $user->id, isset($assignedUsers[$user->id]) ? true : false, array('disabled' => true)) }}
                                        @endif
                                    @else
                                        {{ Form::checkbox('selected_users[]', $user->id, isset($assignedUsers[$user->id]) ? true : false, array('data-channel' => 'verifier', 'data-validate_url' => route('projects.viewer.remove.validate', [$project->id, $user->id]), 'data-user_id' => $user->id)) }}
                                    @endif
        						</td>
        						<td>
                                    {{ Form::checkbox('is_contract_group_project_owners[]', $user->id, (isset($assignedUsers[$user->id]) AND $assignedUsers[$user->id]) ? true : false, array('data-channel' => 'editor', 'data-validate_url' => route('projects.editor.remove.validate', [$project->id, $user->id]))) }}
                                </td>
        						<td style="text-align: left;">{{{ $user->name }}}</td>
                                <td>{{{ $user->designation }}}</td>
                                <td>@if($company->isCompanyAdmin($user)) {{{ trans('forms.yes') }}} @endif</td>
                                <td>{{{ $user->email }}}</td>
        					</tr>
        					@endforeach
        				</tbody>
        			</table>

                    @if($importedUsers->count() > 0)
                        <h2 class="pull-left"><i class="fa fa-user"></i><i class="fa fa-plus"></i> {{ trans('users.importedUsers') }}</h2>
                        <table class="table " style="text-align: center;">
                            <thead>
                            <tr>
                                <th style="text-align: center;width:120px;">{{ trans('users.viewer') }} / {{ trans('users.verifier') }}</th>
                                <th style="text-align: center;width:100px;">{{ trans('users.editor') }}</th>
                                <th style="text-align: left;">{{ trans('users.name') }}</th>
                                <th style="text-align: center;width:200;">{{ trans('users.designation') }}</th>
                                <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($importedUsers as $user)
                                <tr>
                                    <td>
                                        @if(isset($eBidding))
                                            @if( $eBidding->isApproved())
                                                {{ Form::checkbox('selected_users[]', $user->id, isset($assignedUsers[$user->id]) ? true : false, array('disabled' => true)) }}
                                            @endif
                                        @else
                                            {{ Form::checkbox('selected_users[]', $user->id, isset($assignedUsers[$user->id]) ? true : false, array('data-channel' => 'imported-verifier', 'data-validate_url' => route('projects.viewer.remove.validate', [$project->id, $user->id]), 'data-user_id' => $user->id )) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ Form::checkbox('is_contract_group_project_owners[]', $user->id, (isset($assignedUsers[$user->id]) AND $assignedUsers[$user->id]) ? true : false, array('data-channel' => 'imported-editor', 'data-validate_url' => route('projects.editor.remove.validate', [$project->id, $user->id]))) }}
                                    </td>
                                    <td style="text-align: left;">{{{ $user->name }}}</td>
                                    <td>{{{ $user->designation }}}</td>
                                    <td>{{{ $user->email }}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif

        			<!--  Input -->
        			<div class="form-group">
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary pull-right'] )  }}
        			</div>
        		</div>
        	{{ Form::close() }}
            @include('users.pendingTasks.pendingTaskListModal')
            @include('templates.warning_modal', [
                'message' => trans('users.userHasPendingTenderResubmissions') . '.',
            ])

            @include('projects.partials.assign_blocked_users_modal')
        @else
            {{ trans('projects.notAssignedToProject') }}
        @endif
    </div>
@endsection

@if($contractGroup)
@section('js')
    <script type="text/javascript">
        $(document).ready(function() {
            var projectId = "{{ $project->id }}";

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

            $(document ).on('click', 'input[type=checkbox]', function(e){
                var self = $(this);
                var userId = self.data('user_id');
                var value = self.val();
                var checked = self.prop( 'checked' );
                var validateUrl = self.attr('data-validate_url');
                var siblingCheckbox = null;

                tenderingPendingTasksUrl                = "{{ route('user.project.tendering.pending.tasks.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                postContractPendingTasksUrl             = "{{ route('user.project.postContract.pending.tasks.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                siteModulePendingTasksUrl               = "{{ route('user.project.site.module.pending.tasks.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                letterOfAwardUserPermissionsUrl         = "{{ route('user.project.letterOfAward.user.permissions.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                requestForVariationPermissionsUrl       = "{{ route('user.project.requestForVariation.user.permissions.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                constractManagementUserPermissionsUrl   = "{{ route('user.project.contractManagement.user.permission.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                siteManagementUserPermissionsUrl        = "{{ route('user.project.siteManagement.user.permission.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                requestForInspectionUserPermissionsUrl  = "{{ route('user.project.request.for.inspection.user.permission.get') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);
                vendorPerformanceEvaluationApprovalsUrl = "{{ route('user.project.getVendorPerformanceEvaluationApprovals') }}".replace('%7BprojectId%7D', projectId).replace('%7BuserId%7D', userId);

                if(self.attr( 'name' ) == 'selected_users[]' ) {
                    siblingCheckbox = $( 'input[type=checkbox][value=' + value + '][name="is_contract_group_project_owners[]"]' );

                    if(!checked) {
                        app_progressBar.toggle();

                        $.ajax({
                            url: validateUrl,
                            method: 'GET',
                            success: function(response) {
                                if(response.isTransferable) {
                                    siblingCheckbox.prop( 'checked', false );
                                } else {
                                    self.prop('checked', true);

                                    $('#pendingTaskListModal').modal('show');
                                }

                                app_progressBar.maxOut();
                                app_progressBar.toggle();
                                app_progressBar.reset();
                            },
                        });
                    }
                }

                if( $( this ).attr( 'name' ) == 'is_contract_group_project_owners[]' ) {
                    siblingCheckbox = $( 'input[type=checkbox][value=' + value + '][name="selected_users[]"]' );
                    
                    if( checked ) {
                        siblingCheckbox.prop( 'checked', true );
                    } else {
                        app_progressBar.toggle();

                        $.ajax({
                            url: validateUrl,
                            method: 'GET',
                            success: function(response) {
                                if(response.isEditorRemovable) {
                                    self.prop( 'checked', false );
                                } else {
                                    self.prop('checked', true);

                                    $('#warningModal').modal('show');
                                }

                                app_progressBar.maxOut();
                                app_progressBar.toggle();
                                app_progressBar.reset();
                            },
                        });
                    }
                }
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

        $('[data-action="show-blocked-users"]').on('click', function(){
            $('#assign-blocked-users-modal').modal('show');
        });
    </script>
@endsection
@endif