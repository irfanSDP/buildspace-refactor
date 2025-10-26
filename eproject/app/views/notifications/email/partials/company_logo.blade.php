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