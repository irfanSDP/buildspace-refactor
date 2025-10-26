<?php $modalId = $modalId ?? 'pendingTaskListModal'; ?>
<input id="txtSelectedObjectIds" type="hidden">
<div class="modal fade fullscreen" id="{{ $modalId }}" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">{{ trans('general.pendingTasks') . ' & ' . trans('general.assignedModulePermissions') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <ul id="tabPanes" class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#tenderingPendingTasksTab" aria-controls="home" role="tab" data-toggle="tab" id="tenderingPendingTasksTarget"><i class="fa fa-lg fa-fw fa-gavel"" aria-hidden="true"></i>&nbsp;{{ trans('tenders.tendering') }}</a></li>
                    <li role="presentation"><a href="#postContractPendingTasksTab" aria-controls="profile" role="tab" data-toggle="tab" id="postContractPendingTasksTarget"><i class="fa fa-lg fa-fw fa-handshake" aria-hidden="true"></i>&nbsp;{{ trans('projects.postContract') }}</a></li>
                    <li role="presentation"><a href="#siteModulePendingTasksTab" aria-controls="profile" role="tab" data-toggle="tab" id="siteModulePendingTasksTarget"><i class="fa fa-lg fa-fw fa-handshake" aria-hidden="true"></i>&nbsp;{{ trans('contractManagement.siteModule') }}</a></li>
                    <li role="presentation"><a href="#letterOfAwardUserPermissionsTab" aria-controls="profile" role="tab" data-toggle="tab" id="letterOfAwardUserPermissionsTarget"><i class="fa fa-file-alt" aria-hidden="true"></i>&nbsp;{{ trans('modules.letterOfAward') }}</a></li>
                    <li role="presentation"><a href="#requestForVariationUserPermissionsTab" aria-controls="profile" role="tab" data-toggle="tab" id="requestForVariationUserPermissionsTarget"><i class="fa fa-lg fa-fw fa-table" aria-hidden="true"></i>&nbsp;{{ trans('modules.requestForVariation') }}</a></li>
                    <li role="presentation"><a href="#contractManagementUserPermissionsTab" aria-controls="profile" role="tab" data-toggle="tab" id="contractManagementUserPermissionsTarget"><i class="fa fa-key" aria-hidden="true"></i>&nbsp;{{ trans('modules.contractManagement') }}</a></li>
                    <li role="presentation"><a href="#siteManagementUserPermissionsTab" aria-controls="profile" role="tab" data-toggle="tab" id="siteManagementUserPermissionsTarget"><i class="fa fa-key" aria-hidden="true"></i>&nbsp;{{ trans('modules.siteManagement') }}</a></li>
                    <li role="presentation"><a href="#requestForInspectionUserPermissionsTab" aria-controls="profile" role="tab" data-toggle="tab" id="requestForInspectionUserPermissionsTarget"><i class="fa fa-key" aria-hidden="true"></i>&nbsp;{{ trans('inspection.requestForInspection') }}</a></li>
                    @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
                    <li role="presentation"><a href="#vendorPerformanceEvaluationApprovalsTab" aria-controls="profile" role="tab" data-toggle="tab" id="vendorPerformanceEvaluationApprovalsTarget"><i class="fa fa-key" aria-hidden="true"></i>&nbsp;{{ trans('vendorManagement.vendorPerformanceEvaluation') }}</a></li>
                    @endif
                </ul>
                <div id="navigationTabs" class="tab-content padding-10">
                    <div role="tabpanel" class="tab-pane fade in active" id="tenderingPendingTasksTab">
                        <div id="tenderingPendingTasksTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="postContractPendingTasksTab">
                        <div id="postContractPendingTasksTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="siteModulePendingTasksTab">
                        <div id="siteModulePendingTasksTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="letterOfAwardUserPermissionsTab">
                        <div id="letterOfAwardUserPermissionsTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="requestForVariationUserPermissionsTab">
                        <div id="requestForVariationUserPermissionsTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="contractManagementUserPermissionsTab">
                        <div id="contractManagementUserPermissionsTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="siteManagementUserPermissionsTab">
                        <div id="siteManagementUserPermissionsTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="requestForInspectionUserPermissionsTab">
                        <div id="requestForInspectionUserPermissionsTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="vendorPerformanceEvaluationApprovalsTab">
                        <div id="vendorPerformanceEvaluationApprovalsTable"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>