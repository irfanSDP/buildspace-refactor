<?php $modalId = isset($modalId) ? $modalId : 'deleteUserConfirmationModal'; ?>
<?php $title   = isset($title) ? $title : trans('users.deleteUser'); ?>
<?php $message = isset($message) ? $message : trans('users.areYouSureDeleteThisUser'); ?>
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="yesNoModalLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h4 class="modal-title">
                    {{ $title }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-6">
                    <label class="control-label"><h3>{{ $message }} ?</h3></label>
                </div>
            </div>
            <form id="yesNoForm" method="POST" action="">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-lg" data-action="actionYes">{{{ trans('forms.yes') }}}</button>
                    <button class="btn btn-info btn-lg" data-dismiss="modal">{{{ trans('forms.no') }}}</button>
                </div>
            </form>
        </div>
    </div>
</div>