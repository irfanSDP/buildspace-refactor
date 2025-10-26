<?php use PCK\ObjectField\ObjectField; ?>
<div class="row">
    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
        <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
            <li class="nav-item active">
                <a class="nav-link" href="#company-personnel-director" data-toggle="tab">{{{ trans('vendorManagement.directors') }}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#company-personnel-shareholder" data-toggle="tab">{{{ trans('vendorManagement.shareholders') }}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#company-personnel-owner" data-toggle="tab">{{{ trans('vendorManagement.headOfCompany') }}}</a>
            </li>
        </ul>
    </div>
    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
        <div class="tab-content">
            <div class="tab-pane fade in active" id="company-personnel-director">
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <button type="button" class="btn btn-info btn-xs" data-action="view-attachments" data-url="{{ route('vendorProfile.companyPersonnel.attachments.get', [$company->finalVendorRegistration->id, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR]) }}">
                                <?php 
                                    $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR);
                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                ?>
                            <i class="fas fa-paperclip fa-md"></i> (<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                        </button>
                    </section>
                </div>
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div style="padding-top:6px;" id="company-personnel-directors-table"></div>
                    </section>
                </div>
            </div>
            <div class="tab-pane fade" id="company-personnel-shareholder">
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <button type="button" class="btn btn-info btn-xs" data-action="view-attachments" data-url="{{ route('vendorProfile.companyPersonnel.attachments.get', [$company->finalVendorRegistration->id, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER]) }}">
                                <?php 
                                    $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER);
                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                ?>
                            <i class="fas fa-paperclip fa-md"></i> (<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                        </button>
                    </section>
                </div>
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div style="padding-top:6px;" id="company-personnel-shareholders-table"></div>
                    </section>
                </div>
            </div>
            <div class="tab-pane fade" id="company-personnel-owner">
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <button type="button" class="btn btn-info btn-xs" data-action="view-attachments" data-url="{{ route('vendorProfile.companyPersonnel.attachments.get', [$company->finalVendorRegistration->id, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD]) }}">
                                <?php 
                                    $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD);
                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                ?>
                            <i class="fas fa-paperclip fa-md"></i> (<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                        </button>
                    </section>
                </div>
                <div class="row">
                    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div style="padding-top:6px;" id="company-personnel-head-of-company-table"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@if ($isInternalVendor)
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <button type="button" class="btn btn-info pull-left" data-action="view-attachments" data-url="{{ route('vendorProfile.processor.attachments.list', [$company->finalVendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS]) }}">
                <?php
                    $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS);
                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                ?>
                <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
            </button>
        </div>
    </div>
@endif