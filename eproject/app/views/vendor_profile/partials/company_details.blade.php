<h5>{{ trans('companies.generalInformation') }}</h5>
<hr class="simple"/>
<?php use PCK\ObjectField\ObjectField; ?>
<?php use PCK\Companies\Company; ?>
<?php use PCK\CIDBGrades\CIDBGrade; ?>

<div class="row">

    <div class="col col-lg-8">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.companyName') }}:</dt>
            <dd>{{{ $company->name }}}</dd>
            @if($vendorDetailsAttachmentSetting->name_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyName']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyName');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.vendorCode') }}:</dt>
            <dd>{{{ $company->getVendorCode() }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.activationDate') }}:</dt>
            <dd>{{{ ($company->activation_date) ? date('d/m/Y', strtotime($company->activation_date)) : '-'}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.expiryDate') }}:</dt>
            <dd>{{{ ($company->expiry_date) ? date('d/m/Y', strtotime($company->expiry_date)) : '-'}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.deactivationDate') }}:</dt>
            <dd>{{{ ($company->deactivation_date) ? date('d/m/Y', strtotime($company->deactivation_date)) : '-'}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.address') }}:</dt>
            <dd>
                @if(!empty($company->address))
                {{ nl2br($company->address) }}
                @endif
            </dd>
            @if($vendorDetailsAttachmentSetting->address_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyAddress']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyAddress');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.state') }}:</dt>
            <dd>{{{ $company->state->name }}}</dd>
            @if($vendorDetailsAttachmentSetting->state_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyState']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyState');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.country') }}:</dt>
            <dd>{{{ $company->country->country }}}</dd>
            @if($vendorDetailsAttachmentSetting->country_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyCountry']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyCountry');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
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
            <dt>{{ trans('vendorManagement.vendorGroup') }}:</dt>
            <dd>{{{ $company->contractGroupCategory->name }}}</dd>
            @if($vendorDetailsAttachmentSetting->contract_group_category_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsUserType']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsUserType');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-6">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.vendorCategory') }}:</dt>
            <dd>
                <select class="fill-horizontal" id="company-vendor-categories-details" multiple disabled>
                    @foreach($company->vendorCategories as $companyVendorCategory)
                    <option selected>{{ $companyVendorCategory->name }}</option>
                    @endforeach
                </select>
            </dd>
            @if($vendorDetailsAttachmentSetting->vendor_category_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsVendorCategory']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsVendorCategory');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<div class="row">
    <div class="col col-lg-6">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('businessEntityTypes.businessEntityType') }}:</dt>
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
            <dt>{{ trans('companies.mainContact') }}:</dt>
            <dd>{{{ $company->main_contact }}}</dd>
            @if($vendorDetailsAttachmentSetting->main_contact_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyMainContact']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyMainContact');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.referenceNumber') }}:</dt>
            <dd>{{{ $company->reference_no }}}</dd>
            @if($vendorDetailsAttachmentSetting->reference_number_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyRocNumber']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyRocNumber');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.taxRegistrationNumber') }}:</dt>
            <dd>{{{ $company->tax_registration_no }}}</dd>
            @if($vendorDetailsAttachmentSetting->tax_registration_number_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyTaxRegistrationNumber');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<div class="row">
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.email') }}:</dt>
            <dd>{{{ $company->email }}}</dd>
            @if($vendorDetailsAttachmentSetting->email_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyEmail']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyEmail');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.telephone') }}:</dt>
            <dd>{{{ $company->telephone_number }}}</dd>
            @if($vendorDetailsAttachmentSetting->telephone_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyTelephone']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyTelephone');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.fax') }}:</dt>
            <dd>{{{ $company->fax_number }}}</dd>
            @if($vendorDetailsAttachmentSetting->fax_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyFax']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyFax');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
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
            <dt>{{ trans('companies.cidbGrade') }}:</dt>
            <?php 
                $cidbGrade = NULL;

                if($company->cidb_grade)
                {
                    if(CIDBGrade::find($company->cidb_grade))
                    {
                        $cidbGrade = CIDBGrade::find($company->cidb_grade)->grade;
                    }
                }
            
                $cidbGrade = is_null($company->cidb_grade) ? '-' : $cidbGrade;
            ?>
                <dd>{{$cidbGrade}}</dd>
            @if($vendorDetailsAttachmentSetting->cidb_grade_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCIDBGrade']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCIDBGrade');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
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
            <dt>{{ trans('companies.bimLevel') }}:</dt>
            <?php $bimLevel = is_null($company->bimLevel) ? '-' : $company->bimLevel->name; ?>
            <dd>{{ $bimLevel }}</dd>
            @if($vendorDetailsAttachmentSetting->cidb_grade_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsBIMLevel']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsBIMLevel');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
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
            <dt>{{ trans('vendorManagement.companyStatus') }}:</dt>
            <dd>{{{ $company->company_status ? Company::getCompanyStatusDescriptions($company->company_status) : '-' }}}</dd>
            @if($vendorDetailsAttachmentSetting->company_status_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyStatus']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyStatus');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<div class="row">
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.bumiputeraEquity') }}:</dt>
            <dd>{{{ $company->bumiputera_equity }}}</dd>
            @if($vendorDetailsAttachmentSetting->bumiputera_equity_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyBumiputeraEquity']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyBumiputeraEquity');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.nonBumiputeraEquity') }}:</dt>
            <dd>{{{ $company->non_bumiputera_equity }}}</dd>
            @if($vendorDetailsAttachmentSetting->non_bumiputera_equity_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyNonBumiputeraEquity');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.foreignerEquity') }}:</dt>
            <dd>{{{ $company->foreigner_equity }}}</dd>
            @if($vendorDetailsAttachmentSetting->foreigner_equity_attachments)
            <dt>&nbsp;</dt>
            <dd>
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('vendor.registration.details.attachements.get', [$company->id, 'vendorRegistrationDetailsCompanyForeignerEquity']) }}">
                    <?php 
                        $record = ObjectField::findRecord($company, 'vendorRegistrationDetailsCompanyForeignerEquity');
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;({{ $attachmentCount }})
                </button>
            </dd>
            @endif
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
@if ($isInternalVendor)
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <button type="button" class="btn btn-info pull-left" data-action="view-attachments" data-url="{{ route('vendor.registration.details.attachements.get', [$company->id, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_DETAILS]) }}">
                <?php
                    $record = ObjectField::findRecord($company, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_DETAILS);
                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                ?>
                <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
            </button>
        </div>
    </div>
@endif