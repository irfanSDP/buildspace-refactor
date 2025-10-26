<?php $modalId   = isset($modalId) ? $modalId : 'genericBlankModal'; ?>
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="yesNoModalLavel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
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
                <?php
                use PCK\EmailSettings\EmailSetting;

                $emailSetting = EmailSetting::first();
                if(!$emailSetting)
                {
                    $emailSetting = EmailSetting::createDefault();
                }

                $alignment = EmailSetting::getCompanyLogoAlignmentValue($emailSetting->company_logo_alignment_identifier);
                ?>
                @if(strlen($emailSetting->footer_logo_image) > 0)
                <div style="text-align: {{ $alignment }};">
                    <img src="{{{ asset(EmailSetting::LOGO_FILE_DIRECTORY.DIRECTORY_SEPARATOR.$emailSetting->footer_logo_image) }}}" class="logo">
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-info btn-lg" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>