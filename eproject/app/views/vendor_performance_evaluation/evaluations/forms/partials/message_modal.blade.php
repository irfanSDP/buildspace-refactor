<?php $title             = isset($title) ? $title : trans('forms.remarks'); ?>
<?php $modalId           = isset($modalId) ? $modalId : 'additionalRemarksModal'; ?>
<?php $textareaId        = isset($textareaId) ? $textareaId : 'txtRemarks'; ?>
<?php $actionButtonText  = isset($actionButtonText) ? $actionButtonText : trans('forms.send'); ?>
<?php $actionButtonClass = isset($actionButtonClass) ? $actionButtonClass : 'primary'; ?>
<div class="modal fade warning" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content  panel-warning">
            <div class="modal-header bg-color-blue txt-color-white">
                <h4 class="modal-title">{{ $title }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <form class="smart-form">
                    <fieldset>
                        <section>
                            <label class="textarea">
                                <textarea rows="5" name="message" id="{{ $textareaId }}"></textarea>
                            </label>
                        </section>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-{{{ $actionButtonClass }}} btn-lg" data-action="sendEmailWithAdditionalRemarks">{{{ $actionButtonText }}}</button>
                <button class="btn btn-default btn-lg" data-dismiss="modal">{{{ trans('forms.cancel') }}}</button>
            </div>
        </div>
    </div>
</div>