<?php $modalId   = isset($modalId) ? $modalId : 'yesNoModal'; ?>
<?php $titleId   = isset($titleId) ? $titleId : 'yesNoModalTitle'; ?>
<?php $title     = isset($title) ? $title : trans('general.areYouSure'); ?>
<?php $messageId = isset($messageId) ? $messageId : 'yesNoModalMessageId'; ?>
<?php $message   = isset($message) ? $message : trans('general.areYouSure'); ?>
<?php $isStatic  = isset($isStatic) ? $isStatic : false; ?>
<?php $size      = isset($size) ? $size : 'md'; ?>
<?php $modalHeaderClass = isset($modalHeaderClass) ? $modalHeaderClass : 'alert-danger txt-color-white'; ?>
<?php $modalTitleClass = isset($modalTitleClass) ? $modalTitleClass : 'txt-color-white'; ?>
<?php $modalHeaderIcon = isset($modalHeaderIcon) ? $modalHeaderIcon : 'fas fa-exclamation-triangle'; ?>
<?php $yesBtnClass = isset($yesBtnClass) ? $yesBtnClass : 'btn-danger'; ?>
<?php $yesBtnText = isset($yesBtnText) ? $yesBtnText : trans('forms.yes'); ?>
<?php $noBtnClass = isset($noBtnClass) ? $noBtnClass : 'btn-primary'; ?>
<?php $noBtnText = isset($noBtnText) ? $noBtnText : trans('forms.no'); ?>

<div class="modal fade warning" id="{{ $modalId }}" tabindex="-1" role="dialog" @if($isStatic) data-backdrop="static" data-keyboard="false" @endif aria-labelledby="yesNoModalLavel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content panel-warning">
            <div class="modal-header {{ $modalHeaderClass }}">
                <h4 class="modal-title {{ $modalTitleClass }}" id="{{ $titleId }}">
                <i class="{{ $modalHeaderIcon }}"></i>&nbsp;{{ $title }}
                </h4>
                @if(!$isStatic)
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                @endif
            </div>
            <div class="modal-body">
                <div class="well" style="margin-bottom:0;">
                    <span id="{{ $messageId }}">{{ $message }}</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn {{ $yesBtnClass }}" data-action="actionYes">{{ $yesBtnText }}</button>
                <button class="btn {{ $noBtnClass }}" data-dismiss="modal" data-action="actionNo">{{ $noBtnText }}</button>
            </div>
        </div>
    </div>
</div>