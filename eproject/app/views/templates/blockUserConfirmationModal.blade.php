<?php $modalId = isset($modalId) ? $modalId : 'blockUserConfirmationModal'; ?>
<?php $title   = isset($title) ? $title : trans('users.blockAccount'); ?>
<?php $message = isset($message) ? $message : trans('users.userHasPendingTasks') . '. ' . trans('users.areYouSureBlockThisUser'); ?>
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="yesNoModalLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h3 class="modal-title">
                    {{ $title }}
                </h3>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-6">
                    <div class="alert alert-warning fade in"><i class="fa-fw fa fa-exclamation-triangle"></i> <strong>{{ $message }}?</strong></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning btn-lg" data-action="actionViewPendingTasks"><i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;{{{ trans('general.pendingTasks') }}}</button>
                <button class="btn btn-danger btn-lg" data-action="actionYes">{{{ trans('forms.yes') }}}</button>
                <button class="btn btn-info btn-lg" data-action="actionNo">{{{ trans('forms.no') }}}</button>
            </div>
        </div>
    </div>
</div>