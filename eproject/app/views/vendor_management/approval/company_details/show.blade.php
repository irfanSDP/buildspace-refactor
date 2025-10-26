@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $company->name, array($vendorRegistration->id)) }}</li>
        <li>{{{ trans('vendorManagement.companyDetails') }}}</li>
    </ol>
@endsection
<?php use PCK\ObjectField\ObjectField; ?>
<?php use PCK\Companies\Company; ?>
<?php use PCK\BuildingInformationModelling\BuildingInformationModellingLevel; ?>
<?php use PCK\CIDBGrades\CIDBGrade; ?>

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-building"></i> {{ trans('vendorManagement.companyDetails') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{{ $company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div>
                        @if(!empty($section->amendment_remarks))
                        <div class="well @if($section->amendmentsRequired()) border-danger @elseif($section->amendmentsMade()) border-warning @endif">
                            {{ nl2br($section->amendment_remarks) }}
                        </div>
                        @endif
                        <fieldset>

                            <h5>{{ trans('companies.generalInformation') }}</h5>
                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->name_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments" 
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyName']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyName');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.companyName') }}:
                                        </dt>
                                        <dd>{{{ $company->name }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd><hr/></dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->address_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                                data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyAddress']) }}"
                                                >
                                                <?php 
                                                    $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyAddress');
                                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                                ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.address') }}:
                                        </dt>
                                        <dd>
                                            @if(!empty($company->address))
                                            {{ nl2br($company->address) }}
                                            @endif
                                        </dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-3">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->state_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                                data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyState']) }}"
                                                >
                                                <?php 
                                                    $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyState');
                                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                                ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.state') }}:
                                        </dt>
                                        <dd>{{{ $company->state->name }}}</dd>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-3">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->country_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyCountry']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyCountry');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.country') }}:
                                        </dt>
                                        <dd>{{{ $company->country->country }}}</dd>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-3"></div>
                                <div class="col col-lg-3"></div>
                            </div>

                            <hr class="simple"/>
                            
                            <div class="row">
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->contract_group_category_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsUserType']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsUserType');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('vendorManagement.vendorGroup') }}:
                                        </dt>
                                        <dd>{{{ $company->contractGroupCategory->name }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->vendor_category_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsVendorCategory']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsVendorCategory');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('vendorManagement.vendorCategory') }}:
                                        </dt>
                                        <dd>
                                            <select class="fill-horizontal" id="company-vendor-categories-details" multiple disabled>
                                                @foreach($company->vendorCategories as $companyVendorCategory)
                                                <option selected>{{ $companyVendorCategory->name }}</option>
                                                @endforeach
                                            </select>
                                        </dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        {{ trans('businessEntityTypes.businessEntityType') }}:
                                        </dt>
                                        <?php $businessEntityType = is_null($company->businessEntityType) ? trans('general.notAvailable') : $company->businessEntityType->name ?>
                                        <dd>{{{ $businessEntityType }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->main_contact_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyMainContact']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyMainContact');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.mainContact') }}:
                                        </dt>
                                        <dd>{{{ $company->main_contact }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->reference_number_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyRocNumber']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyRocNumber');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.referenceNumber') }}:
                                        </dt>
                                        <dd>{{{ $company->reference_no }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->tax_registration_number_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.taxRegistrationNumber') }}:
                                        </dt>
                                        <dd>{{{ $company->tax_registration_no }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->email_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyEmail']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyEmail');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.email') }}:
                                        </dt>
                                        <dd>{{{ $company->email }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->telephone_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyTelephone']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyTelephone');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.telephone') }}:
                                        </dt>
                                        <dd>{{{ $company->telephone_number }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->fax_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyFax']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyFax');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.fax') }}:</dt>
                                        <dd>{{{ $company->fax_number }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>
                            @if($company->isContractor())
                            <hr class="simple"/>
                            <div class="row">
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->cidb_grade_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCIDBGrade']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCIDBGrade');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.cidbGrade') }}:
                                        </dt>
                                        <?php 
                                            $cidbGrade = '-'; 
                                            
                                            if(CIDBGrade::find($company->cidb_grade))
                                            {
                                                $cidbGrade = is_null($company->cidb_grade) ? '-' : CIDBGrade::find($company->cidb_grade)->grade;
                                            }
                                        ?>
                                        <dd>{{$cidbGrade}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        {{ trans('companies.cidbCode') }}:
                                        </dt>
                                        <dd>
                                            <select class="fill-horizontal" id="company-cidb-codes" multiple disabled>
                                            @if($company->cidbCodes)
                                                @foreach($company->cidbCodes as $cidbCode)
                                                    <option selected>
                                                        {{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                    </option>
                                                @endforeach
                                            @endif
                                            </select>
                                        </dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>
                            @endif

                            @if($company->isConsultant())
                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->bim_level_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsBIMLevel']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsBIMLevel');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('companies.bimLevel') }}:
                                        </dt>
                                        <?php $bimLevel = is_null($company->bimLevel) ? '' : $company->bimLevel->name; ?>
                                        <dd>{{ $bimLevel }}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>
                            @endif

                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->company_status_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyStatus']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyStatus');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('vendorManagement.companyStatus') }}:
                                        </dt>
                                        <?php $companyStatus = is_null($company->company_status) ? '-' : Company::getCompanyStatusDescriptions($company->company_status); ?>
                                        <dd>{{ $companyStatus }}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->bumiputera_equity_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyBumiputeraEquity']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyBumiputeraEquity');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('vendorManagement.bumiputeraEquity') }}:
                                        </dt>
                                        <dd>{{{ $company->bumiputera_equity }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->non_bumiputera_equity_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('vendorManagement.nonBumiputeraEquity') }}:
                                        </dt>
                                        <dd>{{{ $company->non_bumiputera_equity }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-4">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>
                                        @if($attachmentSettings->foreigner_equity_attachments)
                                        <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                                            data-route-get-attachments-list="{{ route('vendor.approval.registration.details.attachements.get', [$vendorRegistration->id, 'vendorRegistrationDetailsCompanyForeignerEquity']) }}"
                                            >
                                            <?php 
                                                $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyForeignerEquity');
                                                $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                            ?>
                                            <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                        </button>
                                        @endif
                                        {{ trans('vendorManagement.foreignerEquity') }}:
                                        </dt>
                                        <dd>{{{ $company->foreigner_equity }}}</dd>
                                        <dt>&nbsp;</dt>
                                        <dd>&nbsp;</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col col-lg-12">
                                    <div id="company-users-table"></div>
                                </div>
                            </div>
                        </fieldset>

                        <footer class="pull-right" style="padding:6px;">
                            @if($canReject)
                                <form action="{{ route('vendorManagement.approval.companyDetails.reject', [$vendorRegistration->id])}}" method="POST">
                                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                    {{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', trans('forms.back'), array($vendorRegistration->id), array('class' => 'btn btn-default')) }}
                                    <button type="submit" class="btn btn-danger" data-intercept="confirmation" data-confirmation-with-remarks="amendment_remarks" data-confirmation-with-remarks-required="true" data-confirmation-with-remarks-required-message="{{ trans('forms.remarksRequired') }}"><i class="fa fa-times"></i> {{ trans('forms.reject') }}</button>
                                </form>
                            @else
                            {{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', trans('forms.back'), array($vendorRegistration->id), array('class' => 'btn btn-default')) }}
                            @endif
                        </footer>
                    </div>
                </div>
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
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
                e.preventDefault();

                $('#attachmentsModal').data('url', $(this).data('route-get-attachments-list'));
                $('#attachmentsModal').modal('show');
            });

            $("select#company-vendor-categories-details").select2();
            $("select#company-cidb-codes").select2();

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

            $('#attachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                var attachmentsTable = new Tabulator('#attachmentsTable', {
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

            var actionsFormatter = function(cell, formatterParams, onRendered) {
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
                    {title:"{{ trans('users.name') }}", field:"name", hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.email') }}", field:"email", width:250, hozAlign:"left", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.contactNumber') }}", field:"contact_number", width:200, hozAlign:"center", cssClass:"text-middle text-center", headerSort:false, headerFilter: 'input'},
                    {title:"{{ trans('users.admin') }}", field:"is_admin", hozAlign:"center", width:80, cssClass:"text-middle text-center", headerSort:false, headerFilter: 'select', headerFilterParams: yesNoStatus},
                    {title:"{{ trans('users.confirmed') }}", field:"confirmed", hozAlign:"center",width:80, cssClass:"text-middle text-center", headerSort:false, headerFilter: 'select', headerFilterParams: userConfirmedStatus},
                    {title:"{{ trans('users.blocked') }}", field:"account_blocked_status", hozAlign:"center",width:80,  cssClass:"text-middle text-center", headerSort:false, headerFilter: 'select', headerFilterParams: yesNoStatus},
                    {title: "{{ trans('users.actions') }}", hozAlign:"center", width: 80, cssClass:"text-middle text-center", headerSort:false, formatter: actionsFormatter},
                ],
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
        });
    </script>
@endsection