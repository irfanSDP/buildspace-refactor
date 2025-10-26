@extends('layout.main')

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
$companyName = $company->name;
$contactPerson = $company->main_contact;
$contactNumber = $company->telephone_number;
$email = $company->email;
?>

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{ link_to_route('consultant.management.consultant.payments.index', 'Consultant Payments') }}</li>
    <li>{{ link_to_route('consultant.management.consultant.payments.show', $company->name, [$company->id]) }}</li>
    <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
        <i class="fa fa-money-check-alt"></i> Consultant Payments
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-building"></i> {{{ $company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <ul id="consultant-management-contract-tabs" class="nav nav-tabs bordered">
                        <li class="active">
                            <a href="#consultant-management-contract-tab-main-info" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> {{{ trans('projects.mainInformation') }}}</a>
                        </li>
                        @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                        <li>
                            <a href="#consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}" data-toggle="tab"><i class="fa fa-fw fa-lg fa-file-contract"></i> {{{ $consultantManagementSubsidiary->subsidiary->short_name}}}</a>
                        </li>
                        @endforeach
                    </ul>
                    <div id="consultant-management-contract-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-contract-tab-main-info">
                            @include('consultant_management.contracts.partials.main_info')
                        </div>
                        @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                        <div class="tab-pane fade in " id="consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}">
                            @include('consultant_management.consultant_payments.partials.phase_info')
                        </div>
                        @endforeach
                    </div>
                    <footer>
                        <div class="pull-right" style="padding:13px;">
                            {{ link_to_route('consultant.management.consultant.payments.show', trans('forms.back'), [$company->id], ['class' => 'btn btn-default']) }}
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection