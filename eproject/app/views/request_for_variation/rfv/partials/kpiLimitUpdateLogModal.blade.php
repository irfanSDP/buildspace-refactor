<?php $modalId = isset($modalId) ? $modalId : 'kpiLimitUpdateLogModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <input type="hidden" name="rfvCategoryId" value="">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{ trans('requestForVariation.kpiLimitUpdateLogs') }}
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col col-sm-12">
                        <div id="rfvCategoryKpiLimitUpdateLogTable"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>