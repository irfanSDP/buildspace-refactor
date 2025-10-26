<?php $modalId = isset($modalId) ? $modalId : 'genericBlankModal'; ?>
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="yesNoModalLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" data-control="title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div data-control="contents"></div>
                @if(! empty($previewContents['footerLogo']['src']))
                    <div style="text-align: {{ $previewContents['footerLogo']['alignment'] }};">
                        <img src="{{ $previewContents['footerLogo']['src'] }}" class="logo">
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-info btn-lg" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>