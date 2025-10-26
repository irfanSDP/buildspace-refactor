<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.subsidiaryTownship') }}}/{{{trans('general.phase')}}}:</dt>
            <dd>{{{ $consultantManagementSubsidiary->subsidiary->full_name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.developmentType') }}}:</dt>
            <dd>{{{ $consultantManagementSubsidiary->developmentType->title }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>Project Brief:</dt>
            <dd>{{ nl2br($consultantManagementSubsidiary->business_case) }}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.grossAcreage') }}}:</dt>
            <dd>{{{ number_format($consultantManagementSubsidiary->gross_acreage, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.totalConstructionCost') }}}:</dt>
            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->total_construction_cost, 2, '.', ',')}}}</dd>
            <dt>{{ Form::hidden('hidden_total_construction_cost', $consultantManagementSubsidiary->total_construction_cost, ['id'=>'hidden_total_construction_cost-'.$consultantManagementSubsidiary->id]) }}</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.totalLandscapeCost') }}}:</dt>
            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->total_landscape_cost, 2, '.', ',')}}}</dd>
            <dt>{{ Form::hidden('hidden_total_landscape_cost', $consultantManagementSubsidiary->total_landscape_cost, ['id'=>'hidden_total_landscape_cost-'.$consultantManagementSubsidiary->id]) }}</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<hr class="simple">
<div class="row">
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.targetPlanningPermission') }}}:</dt>
            <dd>{{{ date('d/m/Y', strtotime($consultantManagementSubsidiary->planning_permission_date)) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.targetBuildingPlan') }}}:</dt>
            <dd>{{{ date('d/m/Y', strtotime($consultantManagementSubsidiary->building_plan_date)) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-12 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.targetLaunch') }}}:</dt>
            <dd>{{{ date('d/m/Y', strtotime($consultantManagementSubsidiary->launch_date)) }}}</dd>
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
                    <th style="width:100px;text-align:center;">{{{trans('general.noOfUnits')}}}</th>
                    <th style="width:150px;text-align:center;">{{{trans('general.lotSize')}}}</th>
                    <th style="width:180px;text-align:center;">{{{trans('general.proposedBuildUpArea')}}}</th>
                    <th style="width:240px;text-align:center;">{{{trans('general.proposedAverageSellingPrice')}}}</th>
                </tr>
            </thead>
            <tbody>
                <?php $productTypeCount = 1;?>
                @foreach($consultantManagementSubsidiary->productTypes as $productType)
                <tr>
                    <td style="text-align:center;">{{$productTypeCount}}</td>
                    <td>{{{ $productType->productType->title }}}</td>
                    <td style="text-align:center;">{{{ $productType->number_of_unit }}}</td>
                    <td style="text-align:center;">{{{ number_format($productType->lot_dimension_length, 2, '.', ',') }}} <strong>x</strong> {{{ number_format($productType->lot_dimension_width, 2, '.', ',') }}}</td>
                    <td style="text-align:center;">{{{ number_format($productType->proposed_built_up_area, 2, '.', ',') }}}</td>
                    <td style="text-align:center;">{{{$currencyCode}}} {{{ number_format($productType->proposed_average_selling_price, 2, '.', ',') }}}</td>
                </tr>
                <?php $productTypeCount++ ?>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
<hr class="simple">
<?php
$consultantProposedFeeBySubsidiary = (isset($consultantRfp)) ? $consultantRfp->getConsultantProposedFeeBySubsidiary($consultantManagementSubsidiary) : null;
$proposedFeePercentage = ($consultantProposedFeeBySubsidiary) ? $consultantProposedFeeBySubsidiary->proposed_fee_percentage : 0;
$proposedFeeAmount = ($consultantProposedFeeBySubsidiary) ? $consultantProposedFeeBySubsidiary->proposed_fee_amount : 0;

$totalCost = 0;
switch($vendorCategoryRfp->cost_type)
{
    case PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST:
        $totalCost = $consultantManagementSubsidiary->total_construction_cost * ($proposedFeePercentage/100);
        break;
    case PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST:
        $totalCost = $consultantManagementSubsidiary->total_landscape_cost * ($proposedFeePercentage/100);
        break;
}
?>
{{ Form::open(['route' => ['consultant.management.consultant.calling.rfp.proposed.fee.store'], 'class' => 'smart-form']) }}
<div class="row">
    <section class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
        <label class="label">{{{ trans('general.costType') }}} :</label>
        <label>
            {{{$vendorCategoryRfp->getCostTypeText()}}}
        </label>
    </section>
    @if($vendorCategoryRfp->cost_type != PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST)
    <section class="col col-xs-3 col-sm-3 col-md-3 col-lg-2">
        <label class="label">{{{ trans('general.proposedFee') }}} % <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('proposed_fee_percentage') ? 'state-error' : null }}}">
            {{ Form::number('proposed_fee_percentage', Input::old('proposed_fee_percentage', number_format($proposedFeePercentage, 2, ".", '')), ['required'=>'required', 'step' => '0.01', 'min' => '0.00', 'max' => '100.00', 'id'=>'proposed_fee_percentage-'.$consultantManagementSubsidiary->id.'-input', 'class'=>'proposed_fee_percentage-input', 'data-id'=>$consultantManagementSubsidiary->id]) }}
        </label>
        {{ $errors->first('proposed_fee_percentage', '<em class="invalid">:message</em>') }}
    </section>
    @endif
    <section class="col col-xs-3 col-sm-3 col-md-3 col-lg-2">
        <label class="label">{{{ trans('general.amount') }}} ({{{$currencyCode}}}) <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('proposed_fee_amount') ? 'state-error' : null }}}">
            {{ Form::number('proposed_fee_amount', Input::old('proposed_fee_amount', number_format($proposedFeeAmount, 2, ".", '')), ['required'=>'required', 'step' => '0.01', 'min' => '0.00', 'id'=>'proposed_fee_amount-'.$consultantManagementSubsidiary->id.'-input', 'class'=>'proposed_fee_amount-input', 'data-id'=>$consultantManagementSubsidiary->id]) }}
        </label>
        {{ $errors->first('proposed_fee_amount', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-2 col-md-2 col-lg-2">
        <label class="label">&nbsp;</label>
        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
    </section>
</div>
{{ Form::hidden('id', $callingRfp->id) }}
{{ Form::hidden('consultant_management_subsidiary_id', $consultantManagementSubsidiary->id) }}

{{ Form::close() }}