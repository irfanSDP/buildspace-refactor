<?php $modalId = isset($modalId) ? $modalId : 'exportToAccountingModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{ trans('accountCodes.exportToAccounting') }}
                </h4>
            </div>
            <input id="approvedPhaseSubsidiariesURL" type="hidden" name="getApprovedPhaseSubsidiariesURL" value="" />
            <input id="apportionmentTypeName" type="hidden" name="apportionmentTypeName" value="" />
            <input id="exportAccountingRoute" type="hidden" name="exportAccountingRoute" value="" />
            <input id="projectId" type="hidden" name="projectId" value="" />
            <input id="claimCertificateId" type="hidden" name="claimCertificateId" value="" />
            <input id="exportToAccountLatesLogCountURL" type="hidden" name="projectId" value="" />
            <div class="modal-body">
                <form class="smart-form">
                    <div id="approvedPhaseSubsidiariesTable"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
                <button id="btnExportToAccounting" class="btn btn-primary" disabled>{{ trans('accountCodes.exportToAccounting') }}</button>
            </div>
        </div>
    </div>
</div>