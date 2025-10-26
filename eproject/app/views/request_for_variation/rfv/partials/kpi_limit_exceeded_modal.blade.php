<?php $modalId = isset($modalId) ? $modalId : 'kpiLimitExceededModal' ?>

<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-color-redLight txt-color-white">
                <h4 class="modal-title" id="{{{ $modalId }}}-title">
                    <i class="fa fa-exclamation-triangle"></i>
                    {{ trans('general.warning') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body" style="margin: 0 20px;">
                <div class="row">
                    <p class="well">
                        {{ trans('requestForVariation.kpiLimitExceededMessage') }}
                    </p>
                    <button type="button" class="btn btn-default col col-md-offset-4 col-md-4 text-center" data-dismiss="modal" data-action="abort">
                        {{ trans('forms.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>