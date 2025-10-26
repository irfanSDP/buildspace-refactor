<?php $modalId = isset($modalId) ? $modalId : 'subsidiariesHierarchyModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{ trans('accountCodes.assignSubsidiaries') }}
                </h4>
            </div>
            <div class="modal-body">
                <form class="smart-form">
                    <div id="subsidiariesHierarchySelectionTable"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
                <button id="btnSaveSelectedSubsidiaries" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
            </div>
        </div>
    </div>
</div>