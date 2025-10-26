@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>

		@if ( Confide::user()->isSuperAdmin() )
			<li>
				{{ link_to_route('companies', 'Companies', array()) }}
			</li>
		@endif

		<li>
			{{ link_to_route('companies.users', 'Users', array($company->id)) }}
		</li>
		<li>Edit User</li>
	</ol>
@endsection

@section('content')
	
<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> Edit User
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		@include('users.partials.userForm')
	</div>
</div>
	@include('templates.blockUserConfirmationModal')
	@include('users.pendingTasks.pendingTaskListModal')
@endsection

@section('js')
	<script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
@endsection

@section('inline-js')
	$(document).ready(function() {
		var tenderingPendingTasksUrl = "{{ route('user.tendering.pending.tasks.get', [$user->id]) }}";
		var tenderingPendingTasksTable = null;

		var postContractPendingTasksUrl = "{{ route('user.postContract.pending.tasks.get', [$user->id]) }}";
		var postContractPendingTasksTable = null;

		var siteModulePendingTasksUrl = "{{ route('user.site.module.pending.tasks.get', [$user->id]) }}";
		var siteModulePendingTasksTable = null;

		var letterOfAwardUserPermissionsUrl = "{{ route('user.letterOfAward.user.permissions.get', [$user->id]) }}";
		var letterOfAwardUserPermissionsTable = null;

		var requestForVariationPermissionsUrl = "{{ route('user.requestForVariation.user.permissions.get', [$user->id]) }}";
		var requestForVariationUserPermissionsTable = null;

		var constractManagementUserPermissionsUrl = "{{ route('user.contractManagement.user.permission.get', [$user->id]) }}";
		var contractManagementUserPermissionsTable = null;

		var siteManagementUserPermissionsUrl = "{{ route('user.siteManagement.user.permission.get', [$user->id]) }}";
		var siteManagementUserPermissionsTable = null;

		var requestForInspectionUserPermissionsUrl = "{{ route('user.request.for.inspection.user.permission.get', [$user->id]) }}";
		var requestForInspectionUserPermissionsTable = null;

		var vendorPerformanceEvaluationApprovalsUrl = "{{ route('user.getVendorPerformanceEvaluationApprovals', [$user->id]) }}";
		var vendorPerformanceEvaluationCompanyFormsTable = null;

		$('#company-form').validate({
            errorPlacement : function(error, element) {
                error.insertAfter(element.parent());
            }
        });

		$('#isUserBlockCheckbox').on('click', function(e) {
			var self = $(this);
			var checked = self.prop( 'checked' );

			if(checked) {
				$.ajax({
					url: "{{ route('user.block.pending.tasks.check', [$user->id]) }}",
					method: 'GET',
					success: function (response) {
						if(!response.isTransferable) {
							$('#blockUserConfirmationModal').modal({ backdrop: 'static', keyboard: false });
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						// error
					}
				});
			}
		});

		$('[data-action="actionViewPendingTasks"]').on('click', function(e) {
			$('#pendingTaskListModal').modal('show');
		});

		$('[data-action="actionYes"]').on('click', function(e) {
			$('#isUserBlockCheckbox').prop( 'checked', true );
			$('#blockUserConfirmationModal').modal('hide');
		});

		$('[data-action="actionNo"]').on('click', function(e) {
			$('#isUserBlockCheckbox').prop( 'checked', false );
			$('#blockUserConfirmationModal').modal('hide');
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
@endsection