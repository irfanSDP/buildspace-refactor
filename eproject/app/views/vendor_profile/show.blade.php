<?php use PCK\ObjectField\ObjectField; ?>

@extends('layout.main')

@section('css')
    <style>
        .registration-details ul li {
            margin-left: 20px
        }
    </style>
    
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorProfile', trans('vendorProfile.vendorProfiles'), array()) }}</li>
        <li>{{{ $company->name }}}</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorProfile.vendorProfiles') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            @include('vendor_profile.partials.show_index_action_menu', [
                'canViewLogs'               => !$isVendor,
                'canEditVendorProfile'      => $isVendorProfileUser,
                'canPrintVendorCertificate' => true,
            ])
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <?php
                switch($company->getStatus())
                {
                    case \PCK\Companies\Company::STATUS_DEACTIVATED:
                        $badgeColor = 'bg-color-red';
                        break;
                    case \PCK\Companies\Company::STATUS_EXPIRED:
                        $badgeColor = 'bg-color-yellow';
                        break;
                    default:
                        $badgeColor = 'bg-color-green';
                }
                ?>
                <h2>{{ $company->name }} <span class="label {{$badgeColor}}">{{{ $company->getStatusText() }}}</span></h2> 
            </header>
            <div class="widget-body">
                <ul id="myTab1" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#company-details" data-toggle="tab">{{ trans('vendorProfile.companyDetails') }}</a>
                    </li>
                    <li>
                        <a href="#registration-details" data-toggle="tab">{{ trans('vendorManagement.registrationDetails') }}</a>
                    </li>
                    <li>
                        <a href="#vendor-work-category" data-toggle="tab">{{ trans('vendorManagement.vendorWorkCategories') }}</a>
                    </li>
                    @if(!$isVendor)
                        <li>
                            <a href="#vendor-performance-evaluation" data-toggle="tab">{{ trans('vendorProfile.vendorPerformanceEvaluation') }}</a>
                        </li>
                        @include('digital_star.star_rating.partials.tab_header')
                    @endif
                    <li>
                        <a href="#vendor-projects" data-toggle="tab">{{ trans('projects.projects') }}</a>
                    </li>
                    <li>
                        <a href="#vendor-uploads" data-toggle="tab">{{ trans('general.attachments') }}</a>
                    </li>
                    @if($isVendorProfileUser)
                    <li>
                        <a href="#remarks" data-toggle="tab">{{ trans('vendorProfile.remarks') }}</a>
                    </li>
                    @endif
                </ul>
                <div id="myTabContent1" class="tab-content padding-10">
                    <div class="tab-pane fade in active" id="company-details">
                        <div>
                            <ul id="vendor-company-details-tab" class="nav nav-pills">
                                <li class="nav-item active">
                                    <a class="nav-link" href="#vendor-company-info" data-toggle="tab"><i class="fa fa-info-circle"></i> {{ trans('companies.details') }}</a>
                                </li>
                                @if(!$isVendor)
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-users" data-toggle="tab"><i class="fa fa-users"></i> {{ trans('companies.companyUsers') }}</a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-personnel" data-toggle="tab"><i class="fa fa-users"></i> {{ trans('vendorManagement.companyPersonnel') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-project-track-record" data-toggle="tab"><i class="fa fa-file-contract"></i> {{ trans('vendorManagement.projectTrackRecord') }}</a>
                                </li>
                                @if(!$isVendor && !getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-prequalification" data-toggle="tab"><i class="fas fa-list-alt"></i> {{ trans('vendorManagement.preQualification') }}</a>
                                </li>
                                @endif
                                @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_SUPPLIER_CREDIT_FACILITIES'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-supplier-credit-facilities" data-toggle="tab"><i class="fa fa-credit-card"></i> {{ trans('vendorManagement.supplierCreditFacilities') }}</a>
                                </li>
                                @endif
                                @if(!$isVendor && \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_LMS_Elearning))
                                <li class="nav-item">
                                    <a class="nav-link" href="#lms-company-users" data-toggle="tab"><i class="fa fa-users"></i> {{ trans('vendorManagement.lmsUsers') }}</a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div id="vendor-company-details-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            <div class="tab-pane fade in active" id="vendor-company-info">
                                <div class="well">
                                    @if($isVendorProfileUser)
                                    <div>
                                        <i class="fa fa-tags"></i> <strong>{{ trans('tags.tags') }}</strong>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-11">
                                            @include('templates.tag_selector', ['id' => 'tags-input'])
                                        </div>
                                        <div class="col col-lg-1">
                                            <div class="pull-right">
                                                <button class="btn btn-info" data-action="sync-tags">
                                                    <i class="fa fa-save"></i> {{ trans('forms.save') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @include('vendor_profile.partials.company_details')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="vendor-company-personnel">
                                <div class="well">
                                @include('vendor_profile.partials.company_personnel')
                                </div>
                            </div>
                            @if(!$isVendor)
                            <div class="tab-pane fade" id="vendor-company-users">
                                <div class="well">
                                @include('vendor_profile.partials.company_users')
                                </div>
                            </div>
                            @endif
                            <div class="tab-pane fade" id="vendor-company-project-track-record">
                                <div class="well">
                                @include('vendor_profile.partials.project_track_record')
                                </div>
                            </div>
                            @if(!$isVendor)
                            <div class="tab-pane fade" id="vendor-company-prequalification">
                                <div class="well">
                                @include('vendor_profile.partials.vendor_prequalification')
                                </div>
                            </div>
                            @endif
                            <div class="tab-pane fade" id="vendor-company-supplier-credit-facilities">
                                <div class="well">
                                @include('vendor_profile.partials.supplier_credit_facilities')
                                </div>
                            </div>
                            @if(!$isVendor)
                            <div class="tab-pane fade" id="lms-company-users">
                                <div class="well">
                                @include('vendor_profile.partials.lms_company_users')
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade" id="registration-details">
                        <div>
                        @if(empty($vendorRegistrationDetails))
                            <div class="well">
                                {{ trans('general.noRecordsToDisplay') }}
                            </div>
                        @else
                        @foreach($vendorRegistrationDetails as $registrationDetails)
                            <div class="row padded smart-form registration-details">
                                <section class="col col-6">
                                    <label class="label">{{ $registrationDetails['label'] }}</label>
                                    <label>
                                        @if(isset($registrationDetails['values']))
                                        <ul>
                                            @foreach($registrationDetails['values'] as $value)
                                            <li>{{ $value }}</li>
                                            @endforeach
                                        </ul>
                                        @endif
                                        @if(isset($registrationDetails['attachments']))
                                            <button type="button" class="btn btn-primary pull-right" data-action="view-attachments" data-url="{{ $registrationDetails['attachments']['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $registrationDetails['attachments']['attachments_count'] }}</span>) {{ trans('formBuilder.filesUploaded') }}</button>
                                        @endif
                                    </label>
                                </section>
                                @if(isset($registrationDetails['route_attachments']))
                                <section class="col col-2">
                                    <label>
                                        <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $registrationDetails['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $registrationDetails['attachments_count'] }}</span>)</button>
                                    </label>
                                </section>
                                @endif
                            </div>
                        @endforeach
                        @endif
                        </div>
                        <div class="row">&nbsp;</div>
                        @if ($isInternalVendor)
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <button type="button" class="btn btn-info pull-left" data-action="view-attachments" data-url="{{ route('vendorProfile.processor.attachments.list', [$company->finalVendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM]) }}">
                                        <?php
                                            $record = ObjectField::findRecord($company->finalVendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM);
                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                        ?>
                                        <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="vendor-work-category">
                        <p>
                            @include('vendor_profile.partials.vendor_work_categories')
                        </p>
                    </div>
                    @if(!$isVendor)
                        <div class="tab-pane fade" id="vendor-performance-evaluation">
                            <p>
                                @include('vendor_profile.partials.vendor_performance_evaluations')
                            </p>
                        </div>
                        @include('digital_star.star_rating.partials.tab_contents')
                    @endif
                    <div class="tab-pane fade" id="vendor-projects">
                        <div>
                            <ul id="vendor-awarded-projects-tab" class="nav nav-pills">
                                <li class="nav-item active">
                                    <a class="nav-link" href="#vendor-awarded-projects-content" data-toggle="tab"><i class="fa fa-award"></i> {{ trans('vendorProfile.awardedProjects') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-completed-projects-content" data-toggle="tab"><i class="far fa-check-circle"></i> {{ trans('vendorProfile.completedProjects') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div id="vendor-projects-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            <div class="tab-pane fade in active" id="vendor-awarded-projects-content">
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <strong>{{ trans('vendorProfile.totalAwardedContractSum') }}</strong><strong data-label="total-contract-sum" class="text-success"></strong>
                                            </div>
                                            <div class="card-body" id="awarded-projects">
                                                <table data-type="total-contract-sums-table">
                                                    <tr data-id="template-row">
                                                        <td class="padded-less text-right"><span data-label="amount">{{ trans('general.noRecordsFound') }}</span></td>
                                                        <td class="padded-less text-left"><strong><em><span data-label="currency" class="text-success"></span></em></strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                                <hr class="simple">
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <div id="awarded-projects-table"></div>
                                    </section>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="vendor-completed-projects-content">
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <strong>{{ trans('vendorProfile.totalAwardedContractSum') }}</strong><strong data-label="total-contract-sum" class="text-success"></strong>
                                            </div>
                                            <div class="card-body" id="completed-projects">
                                                <table data-type="total-contract-sums-table">
                                                    <tr data-id="template-row">
                                                        <td class="padded-less text-right"><span data-label="amount">{{ trans('general.noRecordsFound') }}</span></td>
                                                        <td class="padded-less text-left"><strong><em><span data-label="currency" class="text-success"></span></em></strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                                <hr class="simple">
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <div id="completed-projects-table"></div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="vendor-uploads">
                        <div>
                            <ul id="vendor-attachment-tab" class="nav nav-pills">
                                <li class="nav-item active">
                                    <a class="nav-link" href="#vendor-attachment" data-toggle="tab"><i class="fa fa-file-alt"></i> {{ trans('general.companyDocuments') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-archived-documents" data-toggle="tab"><i class="fa fa-archive"></i> {{ trans('general.archivedDocuments') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div id="vendor-attachment-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            <div class="tab-pane fade in active" id="vendor-attachment">
                                <div class="well">
                                    <div class="row">
                                        <section class="col col-lg-6">
                                            <div class="well">
                                                <label><strong>{{{ trans('vendorProfile.vendorUploadedAttachments') }}}</strong></label>
                                                <label class="input">
                                                    <?php $dataAction = $isVendor ? 'upload-item-attachments' : 'list-item-attachments'; ?>
                                                    <button type="button" class="btn btn-info" data-action="{{ $dataAction }}"
                                                        data-route-get-attachments-list="{{ route('vendorProfile.attachments.list', [$vendorProfile->id, ObjectField::VENDOR_PROFILE_VENDOR_FILE_UPLOAD]) }}"
                                                        data-route-update-attachments="{{ route('vendorProfile.attachments.update', [$vendorProfile->id, ObjectField::VENDOR_PROFILE_VENDOR_FILE_UPLOAD]) }}"
                                                        data-route-get-attachments-count="{{ route('vendorProfile.attachments.count', [$vendorProfile->id, ObjectField::VENDOR_PROFILE_VENDOR_FILE_UPLOAD]) }}"
                                                        data-field="{{ ObjectField::VENDOR_PROFILE_VENDOR_FILE_UPLOAD }}">
                                                        <?php 
                                                            $record = ObjectField::findRecord($vendorProfile, ObjectField::VENDOR_PROFILE_VENDOR_FILE_UPLOAD);
                                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                                        ?>
                                                    <i class="fas fa-paperclip fa-md"></i> {{ trans('general.attachments') }} (<span data-component="{{ ObjectField::VENDOR_PROFILE_VENDOR_FILE_UPLOAD }}_count">{{ $attachmentCount }}</span>)</button>
                                                </label>
                                            </div>
                                        </section>
                                        <section class="col col-lg-6">
                                        @if($isVendorProfileUser)
                                            <div class="well">
                                                <label><strong>{{{ trans('vendorProfile.procUploadedAttachments') }}}</strong></label>
                                                <label class="input">
                                                    <button type="button" class="btn btn-info" data-action="upload-item-attachments"
                                                        data-route-get-attachments-list="{{ route('vendorProfile.attachments.list', [$vendorProfile->id, ObjectField::VENDOR_PROFILE_CLIENT_FILE_UPLOAD]) }}"
                                                        data-route-update-attachments="{{ route('vendorProfile.attachments.update', [$vendorProfile->id, ObjectField::VENDOR_PROFILE_CLIENT_FILE_UPLOAD]) }}"
                                                        data-route-get-attachments-count="{{ route('vendorProfile.attachments.count', [$vendorProfile->id, ObjectField::VENDOR_PROFILE_CLIENT_FILE_UPLOAD]) }}"
                                                        data-field="{{ ObjectField::VENDOR_PROFILE_CLIENT_FILE_UPLOAD }}">
                                                        <?php 
                                                            $record = ObjectField::findRecord($vendorProfile, ObjectField::VENDOR_PROFILE_CLIENT_FILE_UPLOAD);
                                                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                                        ?>
                                                    <i class="fas fa-paperclip fa-md"></i> {{ trans('general.attachments') }} (<span data-component="{{ ObjectField::VENDOR_PROFILE_CLIENT_FILE_UPLOAD }}_count">{{ $attachmentCount }}</span>)</button>
                                                </label>
                                            </div>
                                        @endif
                                        </section>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="vendor-archived-documents">
                                <div class="well" id="archived-storage">
                                    <ol class="breadcrumb" id="archived-storage-breadcrumb">
                                        <li><a href="#" id="archived-storage-breadcrumb-home" onclick="goToStorage('home', '')"><i class="fa fa-lg fa-hdd"></i></a></li>
                                    </ol>
                                    <div id="archived-storage-table"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($isVendorProfileUser)
                    <div class="tab-pane fade" id="remarks">
                        <div class="smart-form">
                            <div class="row">
                                <section class="col col-xs-11">
                                    <label class="label">{{ trans('general.remarks') }}</label>
                                    <label class="textarea">
                                        <textarea rows="5" name="clientRemarks" id="clientRemarks"></textarea>
                                    </label>
                                </section>
                                <section class="col col-xs-1">
                                    <div class="pull-right">
                                        <label class="label">&nbsp;</label>
                                        <button class="btn btn-info" id="btnSaveClientRemarks"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                                    </div>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12">
                                    <div id="vendor-profile-remarks-table"></div>
                                </section>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(array('id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', array('id' => 'invoice-upload'))
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'attachmentsModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'attachmentsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@include('templates.generic_table_modal', [
    'modalId'    => 'vendorAttachmentsModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'vendorAttachmentsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@include('module_parameters.email_notification_settings.partials.modifiable_contents_modal', [
    'title'      => trans('vendorProfile.editRemarks'),
    'modalId'    => 'editVendorProfileRemarksModal',
    'textareaId' => 'vendorProfileRemarksTextarea'
])

@include('templates/yesNoModal', [
    'modalId' => 'deleteVendorProfileRemarkYesNoModal',
    'titleId' => 'deleteVendorProfileRemarkYesNoModalTitle',
    'message' => trans('vendorProfile.areYouSureDeleteRemarks'),
])

@include('vendor_profile.partials.vendor_prequalification_details', [
    'modalId' => 'vendorPrequalifictionDetailsModal',
])

@if( ! $isVendor )
@include('templates.generic_table_modal', [
    'modalId'    => 'actionLogsModal',
    'title'      => trans('general.editLogs'),
    'tableId'    => 'actionLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@include('templates.generic_table_modal', [
    'modalId'    => 'submissionLogsModal',
    'title'      => trans('vendorManagement.submissionLogs'),
    'tableId'    => 'submissionLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@include('templates.generic_table_modal', [
    'modalId'    => 'remarkLogsModal',
    'title'      => trans('vendorManagement.remarkLogs'),
    'tableId'    => 'remarkLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@endif

@include('templates.generic_table_modal', [
    'modalId'          => 'historical-evaluation-scores-modal',
    'title'            => '',
    'tableId'          => 'historical-evaluation-scores-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'cycle-evaluations-modal',
    'title'            => '',
    'tableId'          => 'cycle-evaluations-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-forms-modal',
    'title'            => '',
    'tableId'          => 'evaluation-forms-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'consultant-contract-details-modal',
    'title'            => trans('general.consultantContractDetails'),
    'tableId'          => 'consultant-contract-details-table',
])

@include('verifiers.verifier_status_overview_modal', array(
    'verifierRecords' => $assignedVerifierRecords
))

@include('digital_star.star_rating.partials.modals')

<div class="modal fade" id="evaluation-form-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.form') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="well">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.reference') }}:</dt>
                                <dd data-name="project-reference"></dd>
                            </dl>
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.project') }}:</dt>
                                <dd data-name="project"></dd>
                            </dl>
                        </div>
                        <div class="col col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.companyName') }}:</dt>
                                <dd data-name="company"></dd>
                                <dt>{{ trans('vendorManagement.vendorWorkCategory') }}:</dt>
                                <dd data-name="vendor_work_category"></dd>
                                <dt>{{ trans('vendorManagement.evaluator') }}:</dt>
                                <dd data-name="evaluator"></dd>
                                <dt>{{ trans('vendorManagement.rating') }}:</dt>
                                <dd data-name="rating"></dd>
                            </dl>
                        </div>
                        <div class="col col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('vendorManagement.form') }}:</dt>
                                <dd data-name="form_name"></dd>
                                <dt>{{ trans('vendorManagement.status') }}:</dt>
                                <dd data-name="status"></dd>
                                <dt>&nbsp;</dt>
                                <dd></dd>
                                <dt>{{ trans('vendorManagement.score') }}:</dt>
                                <dd data-name="score"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div id="evaluation-form-table"></div>
                <div class="row">
                    <div class="col col-lg-12">
                    <dl>
                        <dt>{{ trans('general.remarks') }}:</dt>
                        <dd data-name="remarks"></dd>
                    </dl>
                    <dl>
                        <dt>{{ trans('general.attachments') }}:</dt>
                        <dd><button class="btn btn-xs btn-primary" data-action='show-attachments' data-route=''><i class="fa fa-paperclip"></i></button></dd>
                    </dl>
                    <dl>
                        <dt>{{ trans('general.logs') }}:</dt>
                        <dd>
                            <button class="btn btn-xs btn-primary" data-action='show-evaluator-log'>{{ trans('vendorPerformanceEvaluation.evaluationLogs') }}</button>
                            <button class="btn btn-xs btn-primary" data-action='show-verifier-log'>{{ trans('verifiers.verifierLog') }}</button>
                            <button class="btn btn-xs btn-primary" data-action='show-edit-log'>{{ trans('general.editLogs') }}</button>
                        </dd>
                    </dl>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'evaluation-form-attachments-modal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'evaluation-form-attachments-table',
])

@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-evaluator-log-modal',
    'title'            => trans('vendorPerformanceEvaluation.evaluationLogs'),
    'tableId'          => 'evaluation-form-evaluator-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-verifier-log-modal',
    'title'            => trans('verifiers.verifierLog'),
    'tableId'          => 'evaluation-form-verifier-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-edit-log-modal',
    'title'            => trans('general.editLogs'),
    'tableId'          => 'evaluation-form-edit-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-edit-details-log-modal',
    'title'            => trans('vendorPerformanceEvaluation.editDetails'),
    'tableId'          => 'evaluation-form-edit-details-log-table',
])

@endsection

@section('js')
    <script src="{{ asset('js/app/modalStack.js') }}"></script>
    <script>
        var archivedStorageTbl;
        $(document).ready(function () {
            var attachmentsTable = null;
            var actionLogsTable = null;

            $("select#tags-input").select2({
                tags: true
            });

            $("select#company-vendor-categories-details").select2();
            $("select#company-cidb-codes").select2();

            @if($isVendorProfileUser)
            $('button[data-action=sync-tags]').on('click', function(){
                app_progressBar.toggle();
                $.post("{{ route('vendorProfile.tags.sync', array($company->id)) }}", {
                    _token: _csrf_token,
                    tags: $("select#tags-input").select2('data').map(a => a.text)
                })
                .done(function(data){
                    if(data.success){
                        app_progressBar.maxOut();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('tags.tagsUpdated') }}");
                    }
                    else{
                        SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                    }
                    app_progressBar.toggle();
                })
                .fail(function(data){
                    app_progressBar.toggle();
                    SmallErrorBox.refreshAndRetry();
                });
            });
            @endif

            var awardedProjectsTable = new Tabulator('#awarded-projects-table', {
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.awardedProjects', array($company->id)) }}",
                ajaxConfig: "GET",
                layout:"fitColumns",
                ajaxResponse: function(url, params, response) {
                    if(response.data.length < 1) return [];

                    for(var currencyCode in response.contractSum) {
                        clone = $('#awarded-projects [data-type=total-contract-sums-table] [data-id=template-row]').clone();
                        clone.removeAttr('data-id');
                        clone.removeClass('display-none');
                        clone.find('[data-label=amount]').html(response.contractSum[currencyCode]);
                        clone.find('[data-label=currency]').html(currencyCode);
                        clone.appendTo('#awarded-projects [data-type=total-contract-sums-table]');
                    }

                    $('#awarded-projects [data-type=total-contract-sums-table] [data-id=template-row]').remove();

                    return response.data;
                },
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('projects.status') }}", field:"status", width: 120, hozAlign:"center", headerSort:false, formatter:function(cell, formatterParams, onRendered) {
                        var data = cell.getRow().getData();
                        
                        if(!data.showDetails) {
                            return data.status;
                        }

                        return '<button type="Button" class="btn btn-xs btn-primary" data-action="view_consultant_contract_details" data-url="' + data.showDetailsUrl + '">{{ trans("general.details") }}</button>';
                    }},
                    {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false},
                ],
            });

            var completedProjectsTable = new Tabulator('#completed-projects-table', {
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.completedProjects', array($company->id)) }}",
                ajaxConfig: "GET",
                layout:"fitColumns",
                ajaxResponse: function(url, params, response) {
                    if(response.data.length < 1) return [];

                    for(var currencyCode in response.contractSum) {
                        clone = $('#completed-projects [data-type=total-contract-sums-table] [data-id=template-row]').clone();
                        clone.removeAttr('data-id');
                        clone.removeClass('display-none');
                        clone.find('[data-label=amount]').html(response.contractSum[currencyCode]);
                        clone.find('[data-label=currency]').html(currencyCode);
                        clone.appendTo('#completed-projects [data-type=total-contract-sums-table]');
                    }

                    $('#completed-projects [data-type=total-contract-sums-table] [data-id=template-row]').remove();

                    return response.data;
                },
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('projects.status') }}", field:"status", width: 120, hozAlign:"center", headerSort:false, formatter:function(cell, formatterParams, onRendered) {
                        var data = cell.getRow().getData();
                        
                        if(!data.showDetails) {
                            return data.status;
                        }

                        return '<button type="Button" class="btn btn-xs btn-primary" data-action="view_consultant_contract_details" data-url="' + data.showDetailsUrl + '">{{ trans("general.details") }}</button>';
                    }},
                    {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false},
                ],
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
                ],
            });

            var attachmentDownloadButtonFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var downloadButton = document.createElement('a');
                downloadButton.dataset.toggle = 'tooltip';
                downloadButton.className = 'btn btn-xs btn-primary';
                downloadButton.innerHTML = '<i class="fas fa-download"></i>';
                downloadButton.style['margin-right'] = '5px';
                downloadButton.href = data.download_url;
                downloadButton.download = data.filename;

                return downloadButton;
            }

            $(document).on('click', '[data-action="view_consultant_contract_details"]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $('#consultant-contract-details-modal').data('url', $(this).data('url'));
                $('#consultant-contract-details-modal').modal('show');
            });

            var consultantContractDetailsTable = null;

            $('#consultant-contract-details-modal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                new Tabulator("#consultant-contract-details-table", {
                    columnHeaderVertAlign:"middle",
                    ajaxURL: url,
                    layout:"fitColumns",
                    height: 400,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.vendorCategory')}}", field:'vendor_category', hozAlign: 'center', cssClass: 'text-middle text-center', headerFilter: "input"},
                        {title:"{{ trans('currencies.currency') }}", field:"currency_code", width: 100, hozAlign: 'center', cssClass: 'text-middle text-center', headerFilter: "input"},
                        {title:"{{ trans('projects.contractSum') }}", field:"proposed_fee", width: 150, hozAlign: 'center', cssClass: 'text-middle text-right'},
                    ],
                    ajaxResponse:function(url, params, response){
                        $('#consultant-contract-details-modal').find("div.modal-footer div.custom-div").remove();
                        $('#consultant-contract-details-modal').find("div.modal-footer").append('<div class="custom-div"><strong>' + '{{ trans("general.total") }} : ' + response.sum + ' ' + response.currency_code + '</strong></div>');

                        return response.data;
                    },
                });
            });

            $(document).on('click', '[data-action="view-attachments"]', function(e) {
                e.preventDefault();

                $('#attachmentsModal').data('url', $(this).data('url'));
                $('#attachmentsModal').modal('show');
            });

            $('#attachmentsModal').on('show.bs.modal', function(e) {
                $(this).css('z-index', 1051);
            });

            $('#attachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                attachmentsTable = new Tabulator('#attachmentsTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.download') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: attachmentDownloadButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });

            function actionsFormatter(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var attachmentsButton = document.createElement('button');
				attachmentsButton.innerHTML = `<i class="fa fa-paperclip fa-md"></i> (${data.attachmentsCount})`;
				attachmentsButton.className = 'btn btn-xs btn-info';
				attachmentsButton.style['margin-right'] = '5px';
                attachmentsButton.dataset.action = "view-attachments"
                attachmentsButton.dataset.url = data['route:getDownloads'];

                return attachmentsButton;
            }

            new Tabulator('#completed-project-track-record-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.track.record.list', [$company->id, \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", width: 480, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
                    {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.shassicScore') }}", field:"shassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.qlassicOrConquasScore') }}", field:"has_qlassic_or_conquas_score", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', headerFilter: true},
                    {title:"{{ trans('vendorManagement.qlassicScore') }}", field:"qlassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.qlassicYearOfAchievement') }}", field:"qlassic_year_of_achievement", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.conquasScore') }}", field:"conquas_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.conquasYearOfAchievement') }}", field:"conquas_year_of_achievement", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.awardsReceived') }}", field:"awards_received", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfAwardsReceived') }}", field:"year_of_recognition_awards", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true},
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter},
                ],
            });

            new Tabulator('#current-project-track-record-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.track.record.list', [$company->id, \PCK\TrackRecordProject\TrackRecordProject::TYPE_CURRENT]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth: 480, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
                    {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true},
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter},
                ],
            });

            var companyPersonnelDefaultColumns = [
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            ];
            var companyPersonnelShareholdersColumns = [
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.designation') }}", field:"designation", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.amountOfShare') }}", field:"amount_of_share", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.holdingPercentage') }}", field:"holding_percentage", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            ];
            new Tabulator('#company-personnel-directors-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.company.personnel.list', [$company->id, \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_DIRECTOR]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:companyPersonnelDefaultColumns,
            });
            new Tabulator('#company-personnel-shareholders-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.company.personnel.list', [$company->id, \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_SHAREHOLDERS]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:companyPersonnelShareholdersColumns,
            });
            new Tabulator('#company-personnel-head-of-company-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.company.personnel.list', [$company->id, \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_HEAD_OF_COMPANY]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:companyPersonnelDefaultColumns,
            });

            @if(!$isVendor)
            new Tabulator('#vendor-prequalification-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.vendor.prequalification.list', [$company->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.form') }}", field:"form", minWidth: 200, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 250, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('general.view') }}", 'data-action': 'show-form'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: function(rowData){
                                    return "{{ trans('general.view') }}";
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                attributes: {target: '_blank', class:'btn btn-xs btn-warning', title: "{{ trans('general.download') }}", 'data-action': 'download'},
                                rowAttributes: {href: 'route:download'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-download'},
                                }
                            }
                        ]
                    }}
                ],
            });

            $('#vendor-prequalification-table').on('click', '[data-action=show-form]', function(){
                var row = Tabulator.prototype.findTable("#vendor-prequalification-table")[0].getRow($(this).data('id'));
                $('#vendorPrequalifictionDetailsModal').data('url', row.getData()['route:details']);
                $('#vendorPrequalifictionDetailsModal').modal('show');
            });

            $('#vendorPrequalifictionDetailsModal').on('shown.bs.modal', function() {
                var url = $(this).data('url');

                new Tabulator('#vendor-prequalification-details-table', {
                    dataTree: true,
                    dataTreeStartExpanded:true,
                    height:450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: url,
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                            var cellData = cell.getData();

                            var cssClass = '';
                            if(cell.getData()['remarks'])
                            {
                                if(cell.getData()['amendmentsRequired']){
                                    cssClass = 'text-danger';
                                }
                                else{
                                    cssClass = "text-success";
                                }
                            }

                            var description = cellData['description'];

                            if(cell.getData()['type'] == 'node'){
                                description = '<strong>'+description+'</strong>';
                            }
                            else if(cell.getData()['type'] == 'score' && cell.getData()['selected']){
                                description = '<strong>'+description+'</strong>';
                            }
                            
                            return '<span class="'+cssClass+'">'+description+'</span>';
                        }},
                        {title:"{{ trans('forms.notApplicable') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: function(rowData){
                                if(rowData.hasOwnProperty('id')){
                                    if(rowData['type'] == 'node' && rowData['depth'] > 0 && rowData['hasScores'])
                                    {
                                        var checked = rowData['is_excluded'] ? 'checked' : '';
                                        return '<input type="checkbox" '+checked+' disabled>';
                                    }
                                }
                            }
                        }},
                        {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                        {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' },
                        {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: {
                                show: function(cell){
                                    return cell.getData()['route:getDownloads'];
                                },
                                tag:'button',
                                attributes: {type:'button', 'class':'btn btn-xs btn-info', 'data-toggle':'modal', 'data-target': '#downloadModal', 'data-action':'view-attachments'},
                                rowAttributes: {'data-url':'route:getDownloads'},
                                innerHtml:{
                                    tag:'i',
                                    attributes:{class:'fa fa-paperclip'}
                                }
                            }
                        }},
                    ],
                });
            });
            @endif

            @if(!$isVendor)
            var companyUsersActionsFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var container = document.createElement('div');

                if(data['route:resend_validation_email'] == null) return null;

                var resendValidationEmailButton = document.createElement('a');
                resendValidationEmailButton.id = 'btnResendValidationEmail_' + data.id;
                resendValidationEmailButton.title = "{{ trans('users.resendValidationEmail') }}";
                resendValidationEmailButton.className = 'btn btn-xs btn-warning';
                resendValidationEmailButton.innerHTML = '<i class="fa fa-envelope"></i>';
                resendValidationEmailButton.dataset.action = 'resendValidationEmail';
                resendValidationEmailButton.dataset.url = data['route:resend_validation_email'];

                container.appendChild(resendValidationEmailButton);

                return container;
			}

            var userConfirmedStatus = {
                0:"{{ trans('general.all') }}",
                1:"{{ trans('users.confirmed') }}",
                2:"{{ trans('users.pending') }}",
            };

            var yesNoStatus = {
                0:"{{ trans('general.all') }}",
                1:"{{ trans('general.yes') }}",
                2:"{{ trans('general.no') }}",
            };

            var mainTable = new Tabulator('#company-users-table', {
                height:300,
                layout:"fitColumns",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('company.users.list', [$company->id]) }}",
                paginationSize: 20,
                pagination: "remote",
                ajaxFiltering:true,
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('users.name') }}", field:"name", hozAlign:"left", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.email') }}", field:"email", width:250, hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.contactNumber') }}", field:"contact_number", width:200, hozAlign:"center", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.admin') }}", field:"is_admin", hozAlign:"center", width:80, cssClass:"text-middle text-center", headerSort:false, headerFilter: 'select', headerFilterParams: yesNoStatus},
                    {title:"{{ trans('users.confirmed') }}", field:"confirmed", hozAlign:"center",width:100, cssClass:"text-middle text-center", headerSort:false, headerFilter: 'select', headerFilterParams: userConfirmedStatus},
                    {title:"{{ trans('users.blocked') }}", field:"account_blocked_status", hozAlign:"center",width:80,  cssClass:"text-middle text-center", headerSort:false, headerFilter: 'select', headerFilterParams: yesNoStatus},
                    {title: "{{ trans('users.actions') }}", hozAlign:"center", width: 120, cssClass:"text-middle text-center", headerSort:false, formatter: companyUsersActionsFormatter},
                ]
            });

            var mainTable = new Tabulator('#lms-users-table', {
                height:300,
                layout:"fitColumns",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('lms.users.list', [$company->id]) }}",
                paginationSize: 20,
                pagination: "remote",
                ajaxFiltering:true,
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('users.name') }}", field:"name", hozAlign:"left", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.email') }}", field:"email", width:250, hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.contactNumber') }}", field:"contact_number", width:200, hozAlign:"center", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.courseName') }}", field:"course_name", width:250, hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.courseCompleted') }}", field:"course_completed", width:250, hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.courseCompletedAt') }}", field:"course_completed_at", width:250, hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title: "{{ trans('users.actions') }}", hozAlign:"center", width: 120, cssClass:"text-middle text-center", headerSort:false, formatter: companyUsersActionsFormatter},
                ]
            });

            $(document).on('click', '[data-action="resendValidationEmail"]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        if (response.success) {
                            $.smallBox({
                                title : "{{ trans('general.notification') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('users.reSentValidationEmail') }}.</i>",
                                color : "#739E73",
                                sound: false,
                                timeout : 5000
                            });
                        } else {
                            $.smallBox({
                                title : "{{ trans('general.anErrorHasOccured') }}",
                                content : "<i class='fa fa-close'></i> <i>" + response.error + "</i>",
                                color : "#C46A69",
                                sound: false,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });
            @endif

            new Tabulator('#supplier-credit-facilities-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.supplier.credit.facility.list', [$company->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.supplierName') }}", field:"name", minWidth: 350, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.creditFacilities') }}", field:"facilities", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter},
                ],
            });

            var vendorWorkCategoriesTable = new Tabulator('#vendor_work_categories-table', {
                height:480,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorProfile.vendor.list', [$company->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    { title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var c = '<div class="well">';
                            $.each(rowData.vendor_categories, function( key, value ) {
                                c+='<p>'+value+'</p>';
                            });
                            c+='</div>';
                            return c;
                        }
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var c = '<div class="well">';
                            c+='<p>'+rowData.vendor_work_category_name+'</p>';
                            c+='</div>';
                            return c;
                        }
                    }},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategories') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(Array.isArray(rowData.vendor_work_subcategories) && (rowData.vendor_work_subcategories.length == 0)) return null;

                            var c = '<div class="well">';
                            $.each(rowData.vendor_work_subcategories, function( key, value ) {
                                c+='<p>'+value+'</p>';
                            });
                            c+='</div>';
                            return c;
                        }
                    }},
                    {title:"{{ trans('vendorManagement.qualified') }}", field:"qualified", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.status') }}", field:"status", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
                ],
            });

            function addRowToUploadModal(fileAttributes){
                var clone = $('[data-type=template] tr.template-download').clone();
                var target = $('#uploadFileTable tbody.files');

                $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
                $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
                $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
                if(fileAttributes['imgSrc'] !== undefined && fileAttributes['imgSrc'].length){
                    $(clone).find("span.preview img[data-category=img]").attr('src', fileAttributes['imgSrc']);
                }
                $(clone).find("p.name a[data-category=link]").html(fileAttributes['filename']);
                $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
                $(clone).find("[data-category=size]").html(fileAttributes['size']);
                $(clone).find("button[data-action=delete]").prop('data-route', fileAttributes['deleteRoute']);
                $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);

                target.append(clone);
            }

            $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
                e.preventDefault();

                var attachmentsListUrl   = $(this).data('route-get-attachments-list');
                var attachmentsUpdateUrl = $(this).data('route-update-attachments');
                var attachmentsCountUrl  = $(this).data('route-get-attachments-count');

                var target = $('#uploadFileTable tbody.files').empty();
                var data   = $.get(attachmentsListUrl, function(data) {
                    for(var i in data){
                        addRowToUploadModal({
                            download_url: data[i]['download_url'],
                            filename: data[i]['filename'],
                            imgSrc: data[i]['imgSrc'],
                            id: data[i]['id'],
                            size: data[i]['size'],
                            deleteRoute: data[i]['deleteRoute'],
                            createdAt: data[i]['createdAt'],
                        });
                    }
                });

                $('[data-action=submit-attachments]').data('updated-attachment-count-url', $(this).data('route-get-attachments-count'));
                $('#uploadAttachmentModal').modal('show');
                $('#attachment-upload-form').prop('action', attachmentsUpdateUrl);
            });

            $(document).on('click', '[data-action=submit-attachments]', function(){
                var updatedAttachmentCountUrl = $(this).data('updated-attachment-count-url');
                var uploadedFilesInput = [];

                $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                    uploadedFilesInput.push($(this).val());
                });

                app_progressBar.show();

                $.post($('form#attachment-upload-form').prop('action'),{
                    _token: _csrf_token,
                    uploaded_files: uploadedFilesInput
                })
                .done(function(data){
                    if(data.success){
                        $('#uploadAttachmentModal').modal('hide');

                        $.get(updatedAttachmentCountUrl, {},function(resp) {

                            $(document).find(`[data-component="${resp.field}_count"]`).text(resp.attachmentCount);
                        });

                        app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            });

            $(document).on('click', '[data-action="list-item-attachments"]', function(e) {
                e.preventDefault();

                $('#vendorAttachmentsModal').data('url', $(this).data('route-get-attachments-list'));
                $('#vendorAttachmentsModal').modal('show');
            });

            $('#vendorAttachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                var vendorAttachmentsTable = new Tabulator('#vendorAttachmentsTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.download') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: attachmentDownloadButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });

            @if($isVendorProfileUser)
                var vendorProfileRemarksTable = new Tabulator('#vendor-profile-remarks-table', {
                    height:380,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('vendorProfile.remarks.ajax.list', [$vendorProfile->id]) }}",
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.remarks') }}", field:"content", minWidth: 300, hozAlign:"left", headerSort:false,formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: function(rowData){
                                return '<div class="well">'
                                +'<p style="white-space: pre-wrap;">'+rowData['content']+'</p>'
                                +'<br />'
                                +'<p style="color:#4d8af0">'+rowData['created_by']+' &nbsp;&nbsp;&nbsp;&nbsp; '+rowData['created_at']+'</p>'
                                +'</div>';
                            }
                        }},
                        {title:"{{ trans('general.actions') }}", field:"content", width: 80, hozAlign:"center", cssClass: 'text-center', headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                            var data = cell.getRow().getData();

                            var updateVendorProfileRemarksButton = document.createElement('button');
                            updateVendorProfileRemarksButton.innerHTML = '<i class="fas fa-edit"></i></button>';
                            updateVendorProfileRemarksButton.className = 'btn btn-xs btn-warning';
                            updateVendorProfileRemarksButton.style['margin-right'] = '5px';

                            var deleteVendorProfileRemarksButton = document.createElement('button');
                            deleteVendorProfileRemarksButton.innerHTML = '<i class="fas fa-trash"></i></button>';
                            deleteVendorProfileRemarksButton.className = 'btn btn-xs btn-danger';

                            var container = document.createElement('div');
                            container.appendChild(updateVendorProfileRemarksButton);
                            container.appendChild(deleteVendorProfileRemarksButton);

                            updateVendorProfileRemarksButton.addEventListener('click', function(e) {
                                e.preventDefault();

                                $('#editVendorProfileRemarksModal [data-action="saveContent"]').data('url', data['route:update']);

                                $('#vendorProfileRemarksTextarea').val(data.content);

                                $('#editVendorProfileRemarksModal').modal('show');
                            });

                            deleteVendorProfileRemarksButton.addEventListener('click', function(e) {
                                e.preventDefault();

                                $('#deleteVendorProfileRemarkYesNoModal [data-action="actionYes"]').data('url', data['route:delete']);
                                $('#deleteVendorProfileRemarkYesNoModal').modal('show');
                            });

                            return container;
                        }},
                    ],
                });
            @endif

            $(document).on('click', '#btnSaveClientRemarks',function(e) {
                e.preventDefault();

                var remarks = DOMPurify.sanitize($('#clientRemarks').val()).trim();

                if(remarks.length){
                    app_progressBar.toggle();

                    $.post("{{ route('vendorProfile.remarks.save', [$vendorProfile->id]) }}", {
                        _token: _csrf_token,
                        remarks: remarks,
                    })
                    .done(function(data) {
                        if(data.success) {
                            app_progressBar.maxOut();
                            SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksUpdated') }}");
                        } else {
                            SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                        }

                        if(vendorProfileRemarksTable){
                            vendorProfileRemarksTable.setData();//reload
                        }

                        $('#clientRemarks').val("");
                        app_progressBar.toggle();
                    })
                    .fail(function(data) {
                        app_progressBar.toggle();
                        SmallErrorBox.refreshAndRetry();
                    });
                }
            });

            archivedStorageTbl = new Tabulator('#archived-storage-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                height: 420,
                ajaxURL: "{{ route('vendorProfile.archivedStorage.ajax.list', $company->id) }}",
                ajaxConfig: "GET",
                layout:"fitColumns",
                selectable: 1,
                responsiveLayout:'collapse',
                columns:[
                    {title:"&nbsp;", field:"basename", minWidth: 300, hozAlign:"left", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                        var data = cell.getData();
                        if(data.type=='dir'){
                            return '<label class="text-warning" style="font-size:14px;"><i class="fa-lg fas fa-folder"></i></label>&nbsp;&nbsp;' + cell.getValue();
                        }else{
                            return '<label class="text-success" style="font-size:14px;"><i class="fa-lg far fa-file"></i></label>&nbsp;&nbsp;' + cell.getValue();
                        }
                    }},
                    {title:"{{ trans('general.remarks') }}", field:"remarks", width: 380, hozAlign:"left", visible:false, headerSort:false},
                    {title:"{{ trans('general.type') }}", field:"document_type", width: 380, hozAlign:"left", visible:false, headerSort:false},
                    {title:"{{ trans('documentManagementFolders.size') }}", field:"size", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                        var data = cell.getData();
                        if(data.type=='file'){
                            return (parseFloat(data.size)/1024).toFixed(2)+" KB";
                        }
                    }},
                    {title:"{{ trans('general.type') }}", field:"extension", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
                ],
                rowClick:function(e, row){
                    row.select();
                },
                rowDblClick:function(e, row){
                    row.select();
                    
                    var data = row.getData();
                    if(data.type == 'dir'){
                        var params,
                            path;
                        if(data.id == 'EXT_APP_ATTCH' || data.id == 'EXT_APP_COMP_ATTCH'){
                            path = data.path;
                            params = {id:data.id, path:path};
                        }else{
                            path = (data.path) ? data.path+'/'+ data.basename : data.basename;
                            params = {path:path};
                        }
                        $("#archived-storage-breadcrumb").append('<li class="archived-storage-sub"><a id="archived-storage-sub-'+data.id+'" href="#" onclick="goToStorage(\''+data.id+'\', \''+path+'\')">'+data.basename+'</a></li>');
                        row.getTable().setData("{{ route('vendorProfile.archivedStorage.ajax.list', $company->id) }}", params).then(function(){
                            switch(data.id){
                                case 'EXT_APP_ATTCH':
                                    archivedStorageTbl.showColumn('remarks');
                                    archivedStorageTbl.hideColumn('document_type');
                                    archivedStorageTbl.hideColumn('extension');
                                    break;
                                case 'EXT_APP_COMP_ATTCH':
                                    archivedStorageTbl.showColumn('document_type');
                                    archivedStorageTbl.hideColumn('remarks');
                                    archivedStorageTbl.hideColumn('extension');
                                    break;
                                default:
                                    archivedStorageTbl.hideColumn('document_type');
                                    archivedStorageTbl.hideColumn('remarks');
                                    archivedStorageTbl.showColumn('extension');
                            }
                            archivedStorageTbl.redraw();
                        });
                    }else{
                        var id = '';
                        var parts = (data.id).split("-");
                        var url = "{{route('vendorProfile.archivedStorage.download', $company->id)}}";
                        if(Array.isArray(parts) && parts.length == 2 && (parts[0] == 'EXT_APP_ATTCH' || parts[0] == 'EXT_APP_COMP_ATTCH')){
                            id = 'id='+parts[0]+'&';
                        }
                        window.open(url+"?path="+encodeURIComponent(data.path)+"&"+id+"filename="+encodeURIComponent(data.filename)+"&ext="+data.extension, '_blank');
                    }
                }
            });
            
            // remove and reconfigure textarea styles
			// bootstrap adds it's own stylings for unknown reasons
			$('#editVendorProfileRemarksModal').on('show.bs.modal', function() {
				$('#vendorProfileRemarksTextarea').removeAttr('style');
				$('#vendorProfileRemarksTextarea').css('height', '200px');
				$('#vendorProfileRemarksTextarea').css('overflow-y', 'scroll');
			});

            $('#editVendorProfileRemarksModal').on('shown.bs.modal', function() {
				$('#vendorProfileRemarksTextarea').focus();
			});

            $('#editVendorProfileRemarksModal [data-action="saveContent"]').on('click', function(e) {
				e.preventDefault();

				var url 	= $(this).data('url');
				var remarks = DOMPurify.sanitize($('#vendorProfileRemarksTextarea').val().trim());

                if(remarks == '') return;

                app_progressBar.toggle();

                $.post(url, {
                    _token: _csrf_token,
                    remarks: remarks,
                })
                .done(function(data) {
                    if(data.success) {
                        app_progressBar.maxOut();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksUpdated') }}");
                    } else {
                        SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                    }

                    if(vendorProfileRemarksTable){
                        vendorProfileRemarksTable.setData();//reload
                    }

                    $('#vendorProfileRemarksTextarea').val("");

                    $('#editVendorProfileRemarksModal').modal('hide');

                    app_progressBar.toggle();
                })
                .fail(function(data) {
                    app_progressBar.toggle();
                    SmallErrorBox.refreshAndRetry();
                });
			});

            $('#deleteVendorProfileRemarkYesNoModal [data-action="actionYes"]').on('click', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                app_progressBar.toggle();

                $.post(url, {
                    _token: _csrf_token,
                })
                .done(function(data) {
                    if(data.success) {
                        app_progressBar.maxOut();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksDeleted') }}");
                    } else {
                        SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                    }

                    if(vendorProfileRemarksTable){
                        vendorProfileRemarksTable.setData();//reload
                    }

                    $('#deleteVendorProfileRemarkYesNoModal').modal('hide');

                    app_progressBar.toggle();
                })
                .fail(function(data) {
                    app_progressBar.toggle();
                    SmallErrorBox.refreshAndRetry();
                });
            });
            @if( ! $isVendor )
            $('#viewActionLogsButton').on('click', function(e) {
                e.preventDefault();

                $('#actionLogsModal').modal('show');
            });

            $('#actionLogsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                actionLogsTable = new Tabulator('#actionLogsTable', {
                    height:400,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('users.name') }}", field: 'user', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.action') }}", field: 'action', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.date') }}", field: 'datetime', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('vendorProfile.action.logs.get', [$vendorProfile->id]) }}",
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });

            function remarksFormatter(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var remarksDiv = document.createElement('div');
                remarksDiv.innerHTML = data.remarks;

                return remarksDiv;
            }

            var remarkLogsTable = null;

            $(document).on('show.bs.modal', '#remarkLogsModal', function(e) {
                remarkLogsTable = new Tabulator('#remarkLogsTable', {
                    height:450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('vendorProfile.remark.logs.get', [$company->id]) }}",
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.section') }}", field:"section", width:180, hozAlign:"left", headerSort:false},
                        {title:"{{ trans('general.remarks') }}", field:"remarks", hozAlign:"left", headerSort:false, formatter:remarksFormatter },
                        {title:"{{ trans('general.dateAndTime') }}", field:"dateTime", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    ],
                });
            });

            var submissionLogsTable = null;
            $(document).on('show.bs.modal', '#submissionLogsModal', function(e) {
                submissionLogsTable = new Tabulator('#submissionLogsTable', {
                    height:350,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('vendorProfile.registrationAndPreQualification.submissionLogs.get', [$company->finalVendorRegistration->id]) }}",
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    pagination: "local",
                    paginationSize:10,
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.user') }}", field:"user", hozAlign:"left", headerSort:false},
                        {title:"{{ trans('general.actions') }}", field:"action", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.dateAndTime') }}", field:"dateTime", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    ],
                });
            });
            @endif

            @if(!$isVendor)
                var latestEvaluationScoresTable = new Tabulator('#latest-evaluation-scores-table', {
                    height:450,
                    ajaxURL: "{{ route('vendorProfile.vendorPerformanceEvaluation.latest', array($company->id)) }}",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxConfig: "GET",
                    paginationSize: 100,
                    pagination: "remote",
                    ajaxFiltering:true,
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.vpeCycleName') }}", field:"cycle", width: 250, hozAlign:"left", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                            var vendorCategoriesArray = cell.getData()['vendor_categories'];
                            var output = [];
                            for(var i in vendorCategoriesArray){
                                output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                            }
                            return output.join('&nbsp;', output);
                        }},
                        {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.original') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                            {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        ]},
                        {title:"{{ trans('vendorManagement.deliberated') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                            {title:"{{ trans('vendorManagement.score') }}", field:"deliberated_score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorManagement.grade') }}", field:"deliberated_grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        ]},
                        {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml:[
                                {
                                    tag: 'button',
                                    attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('vendorManagement.historical') }}", 'data-action': 'show-historical'},
                                    rowAttributes: {'data-id': 'id'},
                                    innerHtml: function(rowData){
                                        return "{{ trans('vendorManagement.historical') }}";
                                    }
                                },{
                                    innerHtml: function(){
                                        return '&nbsp;';
                                    }
                                },{
                                    tag: 'button',
                                    attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('projects.projects') }}", 'data-action': 'show-evaluations'},
                                    rowAttributes: {'data-id': 'id'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {class: 'fa fa-list'},
                                    }
                                }
                            ]
                        }}
                    ]
                });
            @endif

            var historicalEvaluationScoresTable = new Tabulator('#historical-evaluation-scores-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vpeCycleName') }}", field:"cycle", minWidth: 250, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.original') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                        {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.deliberated') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                        {title:"{{ trans('vendorManagement.score') }}", field:"deliberated_score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.grade') }}", field:"deliberated_grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('general.actions') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('projects.projects') }}", 'data-action': 'show-evaluations'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'},
                                }
                            }
                        ]
                    }}
                ]
            });

            var modalStack = new ModalStack();

            $('#latest-evaluation-scores-table').on('click', '[data-action=show-historical]', function(){
                var row = latestEvaluationScoresTable.getRow($(this).data('id'));
                historicalEvaluationScoresTable.setData(row.getData()['route:historical']);
                $('#historical-evaluation-scores-modal .modal-title').html(row.getData()['vendor_work_category']);
                modalStack.push('#historical-evaluation-scores-modal');
            });

            var cycleEvaluationsTable = new Tabulator('#cycle-evaluations-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('vendorManagement.forms') }}", 'data-action': 'show-forms'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'},
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#latest-evaluation-scores-table').on('click', '[data-action=show-evaluations]', function(){
                var row = latestEvaluationScoresTable.getRow($(this).data('id'));
                cycleEvaluationsTable.setData(row.getData()['route:evaluations']);
                $('#cycle-evaluations-modal .modal-title').html(row.getData()['cycle']);
                modalStack.push('#cycle-evaluations-modal');
            });

            $('#historical-evaluation-scores-table').on('click', '[data-action=show-evaluations]', function(){
                var row = historicalEvaluationScoresTable.getRow($(this).data('id'));
                cycleEvaluationsTable.setData(row.getData()['route:evaluations']);
                $('#cycle-evaluations-modal .modal-title').html(row.getData()['cycle']);
                modalStack.push('#cycle-evaluations-modal');
            });

            var evaluationFormsTable = new Tabulator('#evaluation-forms-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.evaluator') }}", field:"evaluator", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('general.view') }}", 'data-action': 'show-form'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: function(rowData){
                                    return "{{ trans('general.view') }}";
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                attributes: {target: '_blank', class:'btn btn-xs btn-warning', title: "{{ trans('general.download') }}", 'data-action': 'download'},
                                rowAttributes: {href: 'route:download'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-download'},
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#cycle-evaluations-table').on('click', '[data-action=show-forms]', function(){
                var row = cycleEvaluationsTable.getRow($(this).data('id'));
                evaluationFormsTable.setData(row.getData()['route:forms']);
                $('#evaluation-forms-modal .modal-title').html(row.getData()['title']);
                modalStack.push('#evaluation-forms-modal');
            });

            var evaluationFormEvaluatorLogTable = new Tabulator('#evaluation-form-evaluator-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"evaluator", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    { title:"{{ trans('general.actions') }}", field: 'action', width: 200, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('general.date') }}", field: 'created_at', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                ]
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-evaluator-log]', function(){
                modalStack.push('#evaluation-form-evaluator-log-modal');
            });

            var evaluationFormVerifierLogTable = new Tabulator('#evaluation-form-verifier-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('users.name') }}", field:"name", minWidth:250, hozAlign:"left", headerSort:false, headerFilter:true},
                    { title:"{{ trans('verifiers.status') }}", field: 'approved', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:function(rowData){
                            if(rowData['approved'] === true){
                                return "<span class='text-success'><i class='fa fa-thumbs-up'></i> <strong>{{ trans('verifiers.approved') }}</strong></span>";
                            }
                            else if(rowData['approved'] === false){
                                return "<span class='text-danger'><i class='fa fa-thumbs-down'></i> <strong>{{ trans('verifiers.rejected') }}</strong></span>";
                            }
                            else{
                                return "<span class='text-warning'><i class='fa fa-question'></i> <strong>{{ trans('verifiers.unverified') }}</strong></span>";

                            }
                        }
                    }},
                    { title:"{{ trans('verifiers.verifiedAt') }}", field: 'verified_at', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('verifiers.remarks') }}", field: 'remarks', width: 240, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                ]
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-verifier-log]', function(){
                modalStack.push('#evaluation-form-verifier-log-modal');
            });

            var evaluationFormEditLogTable = new Tabulator('#evaluation-form-edit-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    { title:"{{ trans('vendorPerformanceEvaluation.editor') }}", field: 'name', headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('general.date') }}", field: 'created_at', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-success', title: "{{ trans('general.view') }}", 'data-action': 'show-edit-details-log'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: function(rowData){
                                    return "{{ trans('general.view') }}";
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-edit-log]', function(){
                modalStack.push('#evaluation-form-edit-log-modal');
            });

            var evaluationFormEditDetailsLogTable = new Tabulator('#evaluation-form-edit-details-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.name') }}", field:"node_name", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {
                        title: "{{ trans('general.previous') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('general.name') }}", field:"previous_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"previous_score_value", width: 80, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"previous_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    },{
                        title: "{{ trans('general.current') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('general.name') }}", field:"current_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"current_score_value", width: 80, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"current_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    }
                ]
            });

            $('#evaluation-form-edit-log-table').on('click', '[data-action=show-edit-details-log]', function(){
                var row = evaluationFormEditLogTable.getRow($(this).data('id'));
                evaluationFormEditDetailsLogTable.setData(row.getData()['route:details']);
                modalStack.push('#evaluation-form-edit-details-log-modal');
            });

            var evaluationFormTable = new Tabulator('#evaluation-form-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                        var cellData = cell.getData();

                        var description = cellData['description'];

                        if(cell.getData()['type'] == 'node'){
                            description = '<strong>'+description+'</strong>';
                        }
                        else if(cell.getData()['type'] == 'score' && cell.getData()['selected']){
                            description = '<strong>'+description+'</strong>';
                        }

                        return description;
                    }},
                    {title:"{{ trans('forms.notApplicable') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                if(rowData['type'] == 'node' && rowData['depth'] > 0 && rowData['hasScores'])
                                {
                                    var checked = rowData['is_excluded'] ? 'checked' : '';
                                    return '<input type="checkbox" '+checked+' disabled>';
                                }
                            }
                        }
                    }},
                    {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' }
                ],
            });

            var evaluationFormAttachmentsTable = new Tabulator('#evaluation-form-attachments-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", cssClass:"text-center", width: 15, headerSort:false, formatter:"rownum"},
                    {title:"{{ trans('general.name') }}", cssClass:"text-left", minWidth: 400, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: function(rowData){
                                return rowData.filename;
                            },
                            tag: 'a',
                            attributes: {'download': ''},
                            rowAttributes: {'href': 'download_url'}
                        }
                    },
                    {title:"{{ trans('files.uploadedBy') }}", field:'uploaded_by', minWidth: 150, cssClass:"text-center", headerSort:false},
                    {title:"{{ trans('files.uploadedAt') }}", field:'uploaded_at', minWidth: 150, cssClass:"text-center", headerSort:false},
                ]
            });

            $('#evaluation-forms-table').on('click', '[data-action=show-form]', function(){
                var row = evaluationFormsTable.getRow($(this).data('id'));
                evaluationFormEvaluatorLogTable.setData(row.getData()['route:evaluator_log']);
                evaluationFormVerifierLogTable.setData(row.getData()['route:verifier_log']);
                evaluationFormEditLogTable.setData(row.getData()['route:edit_log']);
                $.get(row.getData()['route:form_info'], function(data){
                    evaluationFormTable.setData(data['route:grid']);
                    $('#evaluation-form-modal [data-name=project-reference]').html(data['project_reference']);
                    $('#evaluation-form-modal [data-name=project]').html(data['project']);
                    $('#evaluation-form-modal [data-name=company]').html(data['company']);
                    $('#evaluation-form-modal [data-name=vendor_work_category]').html(data['vendor_work_category']);
                    $('#evaluation-form-modal [data-name=form_name]').html(data['form_name']);
                    $('#evaluation-form-modal [data-name=status]').html(data['status']);
                    $('#evaluation-form-modal [data-name=evaluator]').html(data['evaluator']);
                    $('#evaluation-form-modal [data-name=score]').html(data['score']);
                    $('#evaluation-form-modal [data-name=rating]').html(data['rating']);
                    $('#evaluation-form-modal [data-name=remarks]').html(data['remarks']);
                    evaluationFormAttachmentsTable.setData(data['route:attachments']);
                    modalStack.push('#evaluation-form-modal');
                });
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-attachments]', function(){
                modalStack.push('#evaluation-form-attachments-modal');
            });
        });

        function goToStorage(id, path){
            if(archivedStorageTbl){
                if(id=='home'){
                    $("#archived-storage-breadcrumb-home").parent().nextAll("li.archived-storage-sub").remove();
                }else{
                    var elem = $("#archived-storage-sub-"+id);
                    if(elem){
                        elem.parent().nextAll("li.archived-storage-sub").remove();
                    }
                }
                
                switch(id){
                    case 'EXT_APP_ATTCH':
                        params = {id:id, path:path};
                        break;
                    case 'EXT_APP_COMP_ATTCH':
                        params = {id:id, path:path};
                        break;
                    default:
                        params = {path:path};
                }

                archivedStorageTbl.setData("{{ route('vendorProfile.archivedStorage.ajax.list', $company->id) }}", params).then(function(){
                    switch(id){
                        case 'EXT_APP_ATTCH':
                            archivedStorageTbl.showColumn('remarks');
                            archivedStorageTbl.hideColumn('document_type');
                            archivedStorageTbl.hideColumn('extension');
                            break;
                        case 'EXT_APP_COMP_ATTCH':
                            archivedStorageTbl.showColumn('document_type');
                            archivedStorageTbl.hideColumn('remarks');
                            archivedStorageTbl.hideColumn('extension');
                            break;
                        default:
                            archivedStorageTbl.hideColumn('document_type');
                            archivedStorageTbl.hideColumn('remarks');
                            archivedStorageTbl.showColumn('extension');
                    }

                    archivedStorageTbl.redraw();
                });
            }
        }
    </script>
    @include('digital_star.star_rating.partials.script')
@endsection