<?php $modalId = isset($modalId) ? $modalId : 'logsTableModal'; ?>
<?php $modalTitleId = isset($modalTitleId) ? $modalTitleId : 'logsTableModalTitle'; ?>
<?php $tableId = isset($tableId) ? $tableId : 'logsTable'; ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="{{{ $modalTitleId }}}"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body no-padding">
                <div id="{{{ $tableId }}}"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>