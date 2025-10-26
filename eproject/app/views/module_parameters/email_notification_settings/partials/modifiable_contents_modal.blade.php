<?php $title   = isset($title) ? $title : trans('emailNotificationSettings.contents'); ?>
<?php $modalId = isset($modalId) ? $modalId : 'modifiableContentsModal'; ?>
<?php $textareaId = isset($textareaId) ? $textareaId : 'email_contents'; ?>
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
                <button class="btn btn-primary btn-lg" data-action="saveContent">{{{ trans('forms.save') }}}</button>
                <button class="btn btn-danger btn-lg" data-dismiss="modal">{{{ trans('forms.cancel') }}}</button>
            </div>
        </div>
    </div>
</div>