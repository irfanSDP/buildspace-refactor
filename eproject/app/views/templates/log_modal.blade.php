<?php $modalId = isset($modalId) ? $modalId : 'logModal' ?>
<?php $title = isset($title) ? $title : trans('general.log') ?>
<?php $logAction = isset($logAction) ? $logAction : null ?>

<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{{ $title }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <form class="smart-form">
                    @include('templates.log_list', array('logAction' => $logAction))
                </form>
            </div>

        </div>
    </div>
</div>