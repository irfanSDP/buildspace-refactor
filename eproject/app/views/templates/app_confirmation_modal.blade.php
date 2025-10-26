<!-- For app.confirmation.js -->
<?php $modalId = isset($modalId) ? $modalId : 'confirmationModal' ?>

<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-color-redLight txt-color-white">
                <h4 class="modal-title" id="{{{ $modalId }}}-title">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span data-confirmation-title="confirmation-title">{{ trans('general.confirmation') }}</span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body" style="margin: 0 20px;">
                <div class="row">
                    <p class="well @{{ confirmationMessageClass }}" data-category="confirmation-message">
                        {{ trans('general.areYourSureYouWantToDoThis') }}
                    </p>
                    <div data-input="remarks-input" data-type="hidden-by-default" class="padded-bottom">
                        <textarea class="form-control" name="remarks" rows="3" placeholder="{{ trans('general.remarks') }}"></textarea>
                    </div>
                    <hr/>
                    <button type="button" class="btn btn-default col col-md-4 pull-left" data-dismiss="modal" data-action="abort">
                        {{ trans('forms.no') }}
                    </button>
                        <div class="col col-md-4"></div>
                    <button type="button" class="btn btn-warning col col-md-4 pull-right" data-dismiss="modal" data-action="proceed">
                        {{ trans('forms.yes') }} @{{ countdownMessage }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>