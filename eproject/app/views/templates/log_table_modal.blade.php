<?php $modalId = isset($modalId) ? $modalId : 'logModal' ?>
<?php $title = isset($title) ? $title : trans('general.log') ?>

<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ $title }}}
                </h4>
            </div>

            <div class="modal-body">
                <div id="{{{ $modalId }}}-table"></div>
            </div>

        </div>
    </div>
</div>