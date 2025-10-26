<?php use PCK\ObjectField\ObjectField; ?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <ul class="nav nav-pills" style="padding-bottom:4px;">
            <li class="nav-item active">
                <a class="nav-link" href="#completed-project-track-record-content" data-toggle="tab">{{{ trans('vendorManagement.completedProjects') }}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#current-project-track-record-content" data-toggle="tab">{{{ trans('vendorManagement.currentProjects') }}}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade in active" id="completed-project-track-record-content">
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div id="completed-project-track-record-table"></div>
                    </section>
                </div>
            </div>
            <div class="tab-pane fade" id="current-project-track-record-content">
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div id="current-project-track-record-table"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@if ($isInternalVendor)
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <button type="button" class="btn btn-info pull-left" data-action="view-attachments" data-url="{{ route('vendorProfile.processor.attachments.list', [$company->finalVendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_PROJECT_TRACK_RECORDS]) }}">
                <?php
                    $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_PROJECT_TRACK_RECORDS);
                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                ?>
                <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
            </button>
        </div>
    </div>
@endif