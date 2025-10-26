<?php use PCK\Companies\Company; ?>
<h5>{{ trans('companies.generalInformation') }}</h5>
<hr class="simple"/>

<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.companyName') }}:</dt>
            <dd>{{{ $company->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd><hr/></dd>
            <dt>{{ trans('companies.address') }}:</dt>
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
            <dt>{{ trans('companies.state') }}:</dt>
            <dd>{{{ $company->state->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.country') }}:</dt>
            <dd>{{{ $company->country->country }}}</dd>
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
            <dt>{{ trans('companies.contractGroupCategory') }}:</dt>
            <dd>{{{ $company->contractGroupCategory->name }}}</dd>
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
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.referenceNumber') }}:</dt>
            <dd>{{{ $company->reference_no }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.taxRegistrationNumber') }}:</dt>
            <dd>{{{ $company->tax_registration_no }}}</dd>
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
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.telephone') }}:</dt>
            <dd>{{{ $company->telephone_number }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.fax') }}:</dt>
            <dd>{{{ $company->fax_number }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<hr class="simple"/>

<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.bumiputera') }}:</dt>
            <dd>{{{ $company->company_status ? Company::getCompanyStatusDescriptions($company->company_status) : '-' }}}</dd>
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
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.nonBumiputeraEquity') }}:</dt>
            <dd>{{{ $company->non_bumiputera_equity }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.foreignerEquity') }}:</dt>
            <dd>{{{ $company->foreigner_equity }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>