<?php use PCK\ObjectField\ObjectField; ?>

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
            <dt>{{{ trans('general.grossAcreage') }}}:</dt>
            <dd>{{{ number_format($consultantManagementSubsidiary->gross_acreage, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-xs-12 col-md-12 col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>Project Brief:</dt>
            <dd>{{ nl2br($consultantManagementSubsidiary->business_case) }}</dd>
            <dt>
            @if($consultantManagementContract->editableByUser(Confide::user()))
                <button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments"
                    data-route-get-attachments-list="{{ route('consultant.management.contracts.phase.general.attachment.list', [$consultantManagementSubsidiary->id, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF]) }}"
                    data-route-update-attachments="{{ route('consultant.management.contracts.phase.general.attachment.store', [$consultantManagementSubsidiary->id, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF]) }}"
                    data-route-get-attachments-count="{{ route('consultant.management.contracts.phase.general.attachment.count', [$consultantManagementSubsidiary->id, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF]) }}"
                    data-field="{{ ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF }}"
                    data-phase-id="{{ $consultantManagementSubsidiary->id }}">
                    <?php 
                        $record = ObjectField::findRecord($consultantManagementSubsidiary, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF);
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-md"></i> (<span data-component="{{ $consultantManagementSubsidiary->id }}_{{ ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF }}_count">{{ $attachmentCount }}</span>)
                </button>
            @else
                <button type="button" class="btn btn-xs btn-info" data-action="list-item-attachments" 
                    data-route-get-attachments-list="{{ route('consultant.management.contracts.phase.general.attachment.list', [$consultantManagementSubsidiary->id, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF]) }}">
                    <?php 
                        $record = ObjectField::findRecord($consultantManagementSubsidiary, ObjectField::CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF);
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-md"></i>&nbsp;({{ $attachmentCount }})
                </button>
            @endif
            </dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<hr class="simple">
<div class="row">
    <div class="col col-xs-6 col-md-6 col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.projectBudget') }}}:</dt>
            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->project_budget, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-6 col-md-6 col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.totalConstructionCost') }}}:</dt>
            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->total_construction_cost, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-6 col-md-6 col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.totalLandscapeCost') }}}:</dt>
            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->total_landscape_cost, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-xs-6 col-md-6 col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.costPerSquareFeet') }}}:</dt>
            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->cost_per_square_feet, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
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