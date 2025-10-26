<?php use PCK\ObjectField\ObjectField; ?>
<div class="row">
    <div class="col col-lg-12">
        <div id="supplier-credit-facilities-table"></div>
    </div>
</div>
@if ($isInternalVendor)
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <button type="button" class="btn btn-info pull-left" data-action="view-attachments" data-url="{{ route('vendorProfile.processor.attachments.list', [$company->finalVendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS]) }}">
                <?php
                    $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS);
                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                ?>
                <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
            </button>
        </div>
    </div>
@endif