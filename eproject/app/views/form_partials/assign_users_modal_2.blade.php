<?php $modalId = isset($modalId) ? $modalId : 'assignUsersModal' ?>
<?php $title = isset($title) ? $title : trans('users.assignUsers') ?>
<?php $saveButtonLabel = isset($saveButtonLabel) ? $saveButtonLabel : trans('forms.save') ?>
<?php $actionLabel = isset($actionLabel) ? $actionLabel : trans('users.assign') ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="logModal" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{{ $title }}}</h4>
            </div>
            <div class="modal-body">
                <div id="{{{ $tableId }}}"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit">{{{ $actionLabel }}}</button>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>