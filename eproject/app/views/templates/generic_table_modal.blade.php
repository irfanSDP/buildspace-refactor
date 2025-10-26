<?php $modalId    = isset($modalId) ? $modalId : 'logModal'; ?>
<?php $title      = isset($title) ? $title : trans('general.log'); ?>
<?php $tableId    = isset($tableId) ? $tableId : 'tableId'; ?>
<?php $isStatic   = isset($isStatic) ? $isStatic : false; ?>
<?php $showSubmit = isset($showSubmit) ? $showSubmit : false; ?>
<?php $showCancel = isset($showCancel) ? $showCancel : false; ?>
<?php $showInfo = isset($showInfo) ? $showInfo : false; ?>
<?php $submitText = isset($submitText) ? $submitText : trans('forms.save'); ?>
<?php $cancelText = isset($cancelText) ? $cancelText : trans('forms.cancel'); ?>
<?php $infoText = isset($infoText) ? $infoText : trans('general.info'); ?>
<?php $modalDialogClass = isset($modalDialogClass) ? $modalDialogClass : 'modal-lg'; ?>
<?php $modalHeaderClass = isset($modalHeaderClass) ? $modalHeaderClass : ''; ?>
<?php $modalTitleClass = isset($modalTitleClass) ? $modalTitleClass : ''; ?>
<?php $titleIcon = isset($titleIcon) ? $titleIcon : ''; ?>
<?php $tablePadding = isset($tablePadding) ? $tablePadding : false; ?>

<div class="modal fade" id="{{{ $modalId }}}" tabindex="-1" role="dialog" @if($isStatic) data-backdrop="static" data-keyboard="false" @endif aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog {{ $modalDialogClass }}">
        <div class="modal-content">
            <div class="modal-header {{ $modalHeaderClass }}">
                <h4 class="modal-title {{ $modalTitleClass }}">
                    <i class="{{ $titleIcon }}"></i>
                    {{{ $title }}}
                </h4>
                @if(!$isStatic)
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                @endif
            </div>
            <div class="modal-body @if(!$tablePadding) no-padding @endif">
                <span data-id="info-div"></span>
                <div id="{{{ $tableId }}}"></div>
            </div>
            <div class="modal-footer" @if(!($showSubmit || $showCancel || $showInfo)) hidden @endif>
                @if($showSubmit)
                <button class="btn btn-primary btn-md" data-action="actionSave">{{{ $submitText }}}</button>
                @endif
                @if($showCancel)
                <button class="btn btn-default btn-md" data-dismiss="modal">{{{ $cancelText }}}</button>
                @endif
                @if($showInfo)
                <button class="btn btn-info btn-md" data-action="info">{{{ $infoText }}}</button>
                @endif
            </div>
        </div>
    </div>
</div>