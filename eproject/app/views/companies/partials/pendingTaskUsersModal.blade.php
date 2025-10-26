<?php $modalId = isset($modalId) ? $modalId : 'pendingTaskUsersModal' ?>
<?php $title = isset($title) ? $title : trans('users.users') ?>
<?php $table = isset($tableId) ? $tableId : 'tabulator-table-1' ?>

<div class="modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="logModal" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ $title }}</h4>
            </div>
            <div class="modal-body">
                <div id="{{ $tableId }}"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>