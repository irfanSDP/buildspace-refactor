@extends('layout.main')
<?php use PCK\BusinessEntityType\BusinessEntityType; ?>
@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		@if ( $user->isSuperAdmin() )
		<li>{{ link_to_route('companies', 'Companies', array()) }}</li>
		@else
		<li>{{ trans('companies.myCompany') }}</li>
		@endif
		<li>{{{ $company->name }}}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> {{ trans('companies.editCompanyDetails') }}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('forms.edit') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					{{ Form::model($company, array('route' => array('companies.update', $company->id), 'id'=> 'company-form', 'class' => 'smart-form', 'method' => 'put')) }}
						@include('companies.partials.companyForm')

						<footer class="pull-right" style="padding:6px;">
							@if ( $user->isSuperAdmin() )
								{{ link_to_route('companies', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
							@else
								{{ link_to_route('companies.profile', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
							@endif

							<a href="{{{ route('companies.users', array($company->id)) }}}" class="btn btn-success"><i class="fa fa-fw fa-users"></i>&nbsp;{{ trans('users.assignUsers') }}</a>
							{{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}

							@if($company->contractGroupCategory->includesContractGroups(\PCK\ContractGroups\Types\Role::CONTRACTOR))
								@if($company->contractor)
									<a href="{{ route('companies.contractors.edit', array( $company->id, $company->contractor->id )) }}" class="btn btn-info"><i class="fa fa-fw fa-list-alt"></i> {{ trans('companies.contractorDetails') }}</a>
								@else
									<a href="{{ route('companies.contractors.create', array( $company->id )) }}" class="btn btn-info"><i class="fa fa-fw fa-list-alt"></i> {{ trans('companies.contractorDetails') }}</a>
								@endif
							@endif
						</footer>
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
</div>
@include('companies.partials.pendingTaskUsersModal', [
	'tableId' => 'pendingTasksUsersTable',
	'title'	  => trans('users.users') . ' ' . trans('general.with') . ' ' . trans('general.pendingTasks') . ' & ' . trans('general.assignedModulePermissions')
])
@include('users.pendingTasks.pendingTaskListModal')
@endsection

@section('js')
	<script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
    <script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
    <script>
        $(document).ready(function() {
			var pendingTaskUsersModal = null;

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

            $('#company-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });

			$('#company-form').submit(function(e) {
				e.preventDefault();
				app_progressBar.toggle();
				
				var self = $(this);
				var selectedContractGroupCategoryId = $('select[name="contract_group_category_id"]').val();
				var initialContractGroupCategoryId = $('input[name="initial_contract_group_category_id"]').val();
				
				if(selectedContractGroupCategoryId !== initialContractGroupCategoryId) {
					$.ajax({
						url: "{{ route('company.users.pending.tasks.check', [$company->id]) }}",
						method: 'GET',
						success: function (response) {
							app_progressBar.maxOut();
							app_progressBar.hide();

							if(response.hasUsersWithPendingTasks) {
								$('#pendingTaskUsersModal').modal('show');
							} else {
								self[0].submit();
							}
						},
						error: function (jqXHR, textStatus, errorThrown) {
							// error
						}
					});

					return false;
				}

				self[0].submit();
			});

			var actionsColumnFormatter = function(cell, formatterParams, onRendered) {
                var viewUserPendingTasksButton = document.createElement('button');
                viewUserPendingTasksButton.id = 'btnViewUserPendingTasks_' + cell.getRow().getData().id;
                viewUserPendingTasksButton.className = 'btn btn-xs btn-warning';
                viewUserPendingTasksButton.innerHTML = "{{ trans('general.view') }}";
                viewUserPendingTasksButton.style.width = '90%';

                viewUserPendingTasksButton.addEventListener('click', function(e) {
                    e.preventDefault();
			
					tenderingPendingTasksUrl                = "{{ route('user.tendering.pending.tasks.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
					postContractPendingTasksUrl             = "{{ route('user.postContract.pending.tasks.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
                    siteModulePendingTasksUrl               = "{{ route('user.site.module.pending.tasks.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
					letterOfAwardUserPermissionsUrl         = "{{ route('user.letterOfAward.user.permissions.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
					requestForVariationPermissionsUrl       = "{{ route('user.requestForVariation.user.permissions.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
                    constractManagementUserPermissionsUrl   = "{{ route('user.contractManagement.user.permission.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
                    siteManagementUserPermissionsUrl        = "{{ route('user.siteManagement.user.permission.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
                    requestForInspectionUserPermissionsUrl  = "{{ route('user.request.for.inspection.user.permission.get') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
                    vendorPerformanceEvaluationApprovalsUrl = "{{ route('user.getVendorPerformanceEvaluationApprovals') }}".replace('%7BuserId%7D', cell.getRow().getData().id);
					
					$('#pendingTaskListModal').modal('show');
                });

                return viewUserPendingTasksButton;
            };

			$('#pendingTaskUsersModal').on('shown.bs.modal', function() {
				pendingTaskUsersModal = new Tabulator("#pendingTasksUsersTable", {
                    layout:"fitColumns",
                    height: 350,
                    ajaxURL: "{{ route('company.users.with.pending.tasks.get', [$company->id]) }}",
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        {title:"{{ trans('users.name') }}", field: 'name', align:"left", frozen: true, headerSort:false, headerFilter: "input", headerSort:false },
						{title:"{{ trans('users.email') }}", field: 'email', align:"left", width: 250, headerFilter: "input", headerSort:false },
						{title:"{{ trans('general.actions') }}", align: 'center', cssClass:"text-center", width: 70, headerFilter: "input", headerSort:false, formatter: actionsColumnFormatter },
                    ],
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

            var dependentSelection = $.extend({}, DependentSelection);
            dependentSelection.setUrls({first: webClaim.urlContractGroupCategories, second: webClaim.urlVendorCategories});
            dependentSelection.setForms({first: $('form [name=contract_group_category_id]'), second: $('form [name="vendor_category_id[]"]')});
            dependentSelection.setSelectedIds({first: webClaim.contractGroupCategoryId, second: webClaim.vendorCategoryId});
            dependentSelection.setPreSelectOnLoad({first: true, second: false});
            dependentSelection.init();

            $('select[name="business_entity_type_id"]').on('change', function(e) {
                e.preventDefault();

                var selectedValue = $(this).val();

                if(selectedValue == 'other') {
                    $('#business_entity_type_other_section').show();
                } else {
                    $('#business_entity_type_other_section').hide();
                }
            });

            if(webClaim.businessEntityTypeId != null) {
                $('select[name="business_entity_type_id"]').val(webClaim.businessEntityTypeId).trigger('change');
            }

            if(webClaim.businessEntityTypeId != null && webClaim.allowOtherBusinessEntityTypes && webClaim.businessEntityTypeId == "{{ BusinessEntityType::OTHER }}") {
                $('select[name="business_entity_type_id"]').val('other').trigger('change');
                $('[name="business_entity_type_other"]').val(webClaim.businessEntityTypeName);
            }
        });
    </script>
@endsection