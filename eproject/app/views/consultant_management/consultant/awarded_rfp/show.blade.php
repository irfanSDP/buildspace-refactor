@extends('layout.main')

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
?>

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('consultant.management.consultant.awarded.rfp.index', trans('general.consultantManagement').' Awarded RFP') }}</li>
        <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-trophy"></i> Awarded RFP
        </h1>
    </div>
    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
        <!-- Header buttons -->
        <div class="pull-right">
            {{ HTML::decode(link_to_route('consultant.management.consultant.loa.print', '<i class="fa fa-print"></i> '.trans('general.print').' LOA', [$consultantRfp->id], ['class' => 'btn btn-success', 'target'=>"_blank"])) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-contract-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#consultant-management-contract-tab-main-info" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> {{{ trans('projects.mainInformation') }}}</a>
                        </li>
                        <li>
                            <a href="#consultant-management-contract-tab-phases" data-toggle="tab"><i class="fa fa-fw fa-lg fa-file-contract"></i> {{{ trans('general.phases') }}}</a>
                        </li>
                    </ul>
                    <div id="consultant-management-contract-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-contract-tab-main-info">
                            @include('consultant_management.contracts.partials.main_info')
                        </div>
                        <div class="tab-pane fade in " id="consultant-management-contract-tab-phases">
                            @if($consultantManagementContract->consultantManagementSubsidiaries->count())
                            <ul id="consultant-management-subsidiaries-tabs" class="nav nav-pills">
                                @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                                <li class="nav-item @if($key == 0) active @endif">
                                    <a href="#consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}" title="{{{ $consultantManagementSubsidiary->subsidiary->name}}}" data-toggle="tab">{{{ $consultantManagementSubsidiary->subsidiary->short_name}}}</a>
                                </li>
                                @endforeach
                            </ul>
                            <div id="consultant-management-subsidiaries-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                                <div class="tab-pane fade in @if($key==0) active @endif" id="consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}">
                                    @include('consultant_management.contracts.partials.phase_info')
                                </div>
                            @endforeach
                            </div>
                            @else
                            <div class="row">
                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fa-fw fa fa-info"></i>
                                        <strong>Info!</strong> There is no Phase for this Development Plan.
                                    </div>
                                </section>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-building"></i> Consultant Submission Details</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>RFP:</dt>
                                <dd>{{{ $vendorCategoryRfp->vendorCategory->name }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('vendorManagement.consultant') }}:</dt>
                                <dd><div class="well">{{ nl2br($consultantRfp->company->name) }}</div></dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-3">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.referenceNumber') }}:</dt>
                                <dd>{{{ $consultantRfp->company->reference_no }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col col-lg-9">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('tenders.submittedDate') }}:</dt>
                                <dd>{{{ $consultantManagementContract->getAppTimeZoneTime(\Carbon\Carbon::parse($consultantRfp->commonInformation->updated_at)->format(\Config::get('dates.created_and_updated_at_formatting'))) }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <hr class="simple">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <table class="table table-bordered table-condensed table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:48px;text-align:center;">No.</th>
                                        <th style="width:auto;">{{{trans('general.productTypes')}}}</th>
                                        <th style="width:180px;text-align:center;">{{{trans('tenders.amount')}}} ({{{ $currencyCode }}})</th>
                                        @if($vendorCategoryRfp->cost_type != PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST)
                                        <th style="width:120px;text-align:center;">{{{ trans('general.proposedFee') }}} %</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $subsidiaryCounter = 1;?>
                                    @foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
                                    <?php
                                        $proposedFee = $consultantRfp->getConsultantProposedFeeBySubsidiary($consultantManagementSubsidiary);
                                    ?>
                                    <tr>
                                        <td style="text-align:center;">{{$subsidiaryCounter}}</td>
                                        <td>{{{ $consultantManagementSubsidiary->subsidiary->name }}}</td>
                                        <td style="text-align:center;">@if($proposedFee) {{ number_format($proposedFee->proposed_fee_amount, 2, '.', ',') }} @else 0.00 @endif</td>
                                        @if($vendorCategoryRfp->cost_type != PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST)
                                        <td style="text-align:center;">@if($proposedFee) {{ number_format($proposedFee->proposed_fee_percentage, 2, '.', ',') }} @else 0.00 @endif</td>
                                        @endif
                                    </tr>
                                    <?php $subsidiaryCounter++ ?>
                                    @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('consultant_management.partials.general_attachment')

@endsection

@section('js')

@include('consultant_management.partials.general_attachment_javascript')

@endsection