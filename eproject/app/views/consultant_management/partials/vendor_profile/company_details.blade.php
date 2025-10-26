<h5>{{ trans('companies.generalInformation') }}</h5>
<hr class="simple"/>
<?php use PCK\ObjectField\ObjectField; ?>
<?php use PCK\Companies\Company; ?>
<div class="row">

    <div class="col col-lg-8">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.companyName') }}:</dt>
            <dd id="vp-company_name"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.vendorCode') }}:</dt>
            <dd id="vp-vendor_code"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.activationDate') }}:</dt>
            <dd id="vp-activation_date"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.expiryDate') }}:</dt>
            <dd id="vp-expiry_date"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.deactivationDate') }}:</dt>
            <dd id="vp-deactivation_date"></dd>
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
                <div id="vp-company_address" style="white-space:pre-wrap;"></div>
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
            <dd id="vp-company_state"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.country') }}:</dt>
            <dd id="vp-company_country"></dd>
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
            <dd id="vp-vendor_group"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-6">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.vendorCategory') }}:</dt>
            <dd>
                <select class="fill-horizontal" id="vp-vendor_categories" multiple disabled style="width:100%;"></select>
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
            <dd id="vp-main_contact"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.referenceNumber') }}:</dt>
            <dd id="vp-reference_number"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.taxRegistrationNumber') }}:</dt>
            <dd id="vp-tax_registration_no"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<div class="row">
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.email') }}:</dt>
            <dd id="vp-email"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.telephone') }}:</dt>
            <dd id="vp-telephone_number"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.fax') }}:</dt>
            <dd id="vp-fax_number"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<div id="contractor-section" style="display:none;">
    <hr class="simple"/>

    <div class="row">
        <div class="col col-lg-6">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('companies.cidbGrade') }}:</dt>
                <dd id="vp-cidb_grade"></dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-lg-6">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('companies.cidbCode') }}:</dt>
                <dd>
                    <select class="fill-horizontal" id="vp-cidb_codes" multiple disabled style="width:100%;"></select>
                </dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
</div>

<div id="consultant-section" style="display:none;">
    <hr class="simple"/>

    <div class="row">
        <div class="col col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('companies.bimLevel') }}:</dt>
                <dd id="vp-bim_level"></dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
</div>

<hr class="simple"/>

<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.companyStatus') }}:</dt>
            <dd id="vp-company_status"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<div class="row">
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.bumiputeraEquity') }}:</dt>
            <dd id="vp-bumiputera_equity"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.nonBumiputeraEquity') }}:</dt>
            <dd id="vp-non_bumiputera_equity"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('vendorManagement.foreignerEquity') }}:</dt>
            <dd id="vp-foreigner_equity"></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>