<?php $modalId   = isset($modalId) ? $modalId : 'vendorPrequalifictionDetailsModal'; ?>
<div class="modal fade warning" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="yesNoModalLavel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{ trans('vendorManagement.prequalificationDetails') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="vendor-prequalification-details-table"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info btn-lg" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>