<div class="row">
    <div class="col col-lg-12">
        <h4>Section A - {{{ trans('projects.projectInformation')}}}</h4>
    </div>
</div>
<hr class="simple">
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('projects.title') }}:</dt>
            <dd><div class="well">{{ nl2br($consultantManagementContract->title) }}</div></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.referenceNo') }}:</dt>
            <dd>{{{ $consultantManagementContract->reference_no }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-9">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.subsidiaryTownship') }}:</dt>
            <dd>{{{ $consultantManagementContract->subsidiary->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.description') }}:</dt>
            <dd>{{ nl2br($consultantManagementContract->description) }}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>

<table class="table table-hover">
    <thead>
        <tr>
            <th>{{{ trans('general.subsidiaryTownship') }}}/{{{trans('general.phase') }}}</th>
            <th style="width:200px;" class="text-center text-middle">{{{ trans('general.developmentType') }}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
        <tr>
            <td>
                <div class="row" style="padding-bottom:6px;">
                    <div class="col col-xs-12 col-md-12 col-lg-12">
                        <div class="well">{{{ $consultantManagementSubsidiary->subsidiary->full_name }}}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-xs-12 col-md-12 col-lg-12">
                        <dl class="dl-horizontal no-margin">
                    @if($vendorCategoryRfp->cost_type == PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST)
                            <dt>{{{ trans('general.totalLandscapeCost') }}}:</dt>
                            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->total_landscape_cost, 2, '.', ',')}}}</dd>
                    @else
                            <dt>{{{ trans('general.totalConstructionCost') }}}:</dt>
                            <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->total_construction_cost, 2, '.', ',')}}}</dd>
                    @endif
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
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
            </td>
            <td class="text-center text-middle">{{{ $consultantManagementSubsidiary->developmentType->title }}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr class="simple">
@if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
{{ Form::open(['route' => ['consultant.management.approval.document.section.a.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
<div class="row">
    <div class="col col-xs-12 col-md-12 col-lg-12s">
        <?php $selectedApprovingAuthority =  $approvalDocument->sectionA->approving_authority; ?>
        <label class="label">Approving Authority <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('approving_authority') ? 'state-error' : null }}}">
            <select class="select2 fill-horizontal" name="approving_authority" id="approving_authority-select">
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_EMPTY }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_EMPTY == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_EMPTY_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_G }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_G == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_G_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_A }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_A == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_A_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_F }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_F == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_F_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_B }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_B == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_B_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_C }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_C == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_C_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_D }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_D == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_D_TEXT }}}</option>
                <option value="{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_E }}" @if(PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_E == Input::old('approving_authority', $selectedApprovingAuthority)) selected @endif>{{{ PCK\ConsultantManagement\ApprovalDocumentSectionA::APPROVING_AUTHORITY_E_TEXT }}}</option>
            </select>
        </label>
        {{ $errors->first('approving_authority', '<em class="invalid">:message</em>') }}
    </div>
</div>
<footer>
    {{ Form::hidden('id', $approvalDocument->id) }}
    {{ Form::hidden('open_rfp_id', $openRfp->id) }}
    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
</footer>
{{ Form::close() }}
@else
<div class="row">
    <div class="col col-xs-12 col-md-12 col-lg-12s">
        <dl class="dl-horizontal no-margin">
            <dt>Approving Authority:</dt>
            <dd>{{{ $approvalDocument->sectionA->getApprovingAuthorityText() }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
@endif