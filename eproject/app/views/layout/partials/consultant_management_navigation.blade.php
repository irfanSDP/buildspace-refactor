<?php
$isContractMenu = (Request::is('consultant-management/*') && !Request::is('consultant-management/user-management/*')
&& !Request::is('consultant-management/company-role-assignment/*')
&& !Request::is('consultant-management/attachment-settings/*')
&& !Request::is('consultant-management/questionnaire-settings/*')
&& !Request::is('consultant-management/consultant-rfp/*'));
?>
<li class="{{ $isContractMenu ? 'active' : null }}">
    <a href="{{route('consultant.management.contracts.contract.show', [$consultantManagementContract->id])}}" title="{{{ trans('general.developmentPlanning') }}}" class="text-truncate">
        <i class="fa fa-lg fa-fw fa-table"></i>
        <span class="menu-item-parent">{{{ trans("general.developmentPlanning") }}}</span>
    </a>
</li>

@if($user->isSuperAdmin() or ($user->isGroupAdmin() && ($user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) or $user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))))
<li class="{{ Request::is('consultant-management/company-role-assignment/*') ? 'active' : null }}">
    <a href="{{route('consultant.management.company.role.assignment.index', [$consultantManagementContract->id])}}" title="{{{ trans('general.groupManagement') }}}" class="text-truncate">
        <i class="fa fa-lg fa-fw fa-university"></i>
        <span class="menu-item-parent">{{{ trans("general.groupManagement") }}}</span>
    </a>
</li>

<li class="{{ Request::is('consultant-management/user-management/*') ? 'active' : null }}">
    <a href="{{route('consultant.management.user.management.index', [$consultantManagementContract->id])}}" title="{{{ trans('contractManagement.userManagement') }}}" class="text-truncate">
        <i class="fa fa-lg fa-fw fa-users"></i>
        <span class="menu-item-parent">{{{ trans("contractManagement.userManagement") }}}</span>
    </a>
</li>

<li class="{{ Request::is('consultant-management/attachment-settings/*') ? 'active' : null }}">
    <a href="{{route('consultant.management.attachment.settings.index', [$consultantManagementContract->id])}}" title="{{{ trans('general.attachmentSettings') }}}" class="text-truncate">
        <i class="fa fa-lg fa-fw fa-cogs"></i>
        <span class="menu-item-parent">{{{ trans("general.attachmentSettings") }}}</span>
    </a>
</li>

<li class="{{ Request::is('consultant-management/questionnaire-settings/*') ? 'active' : null }}">
    <a href="{{route('consultant.management.questionnaire.settings.index', [$consultantManagementContract->id])}}" title="{{{ trans('general.questionnaireSettings') }}}" class="text-truncate">
        <i class="fa fa-lg fa-fw fa-tasks"></i>
        <span class="menu-item-parent">{{{ trans("general.questionnaireSettings") }}}</span>
    </a>
</li>
@endif

@foreach($consultantManagementContract->consultantManagementVendorCategories()->orderBy('id', 'asc')->get() as $vendorCategoryRfp)
<?php $vendorCategoryRfpStatusTxt = $vendorCategoryRfp->getStatusText(); ?>
<li class="{{ Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/*') ? 'active' : null }}">
    <a href="javascript:void(0);" class="text-truncate" title='{{{ $vendorCategoryRfp->vendorCategory->name }}}'>
        <i class="fa fa-lg fa-fw fa-building"
        style="
        @if($vendorCategoryRfpStatusTxt == trans('general.awarded'))
        color:#1dc9b7;
        @elseif($vendorCategoryRfpStatusTxt == trans('verifiers.approved'))
        color:#886ab5;
        @elseif($vendorCategoryRfpStatusTxt == trans('general.callingRFP'))
        color:#ffc241;
        @endif
        "
        ></i>
        {{{ PCK\Helpers\StringOperations::shorten(ucwords(strtolower($vendorCategoryRfp->vendorCategory->name)), 24) }}}
    </a>
    <ul>
        @if($user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) or
        $user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/attachment-settings*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.rfp.attachment.settings.index', $vendorCategoryRfp->id) }}" title="{{{trans('general.attachmentSettings')}}}" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-cogs"></i> {{{trans('general.attachmentSettings')}}}
            </a>
        </li>
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/documents*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.rfp.documents.index', $vendorCategoryRfp->id) }}" title="RFP Documents" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-folder-open"></i> RFP Documents
            </a>
        </li>
        @endif

        @if($user->hasAccessToConsultantManagementByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT))
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/rec-of-consultant*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.roc.index', $vendorCategoryRfp->id) }}" title="Rec. of Consultant" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-file-signature"></i> Rec. of Consultant
            </a>
        </li>
        @endif
        
        @if($vendorCategoryRfp->revisions && $vendorCategoryRfp->revisions()->count() && $user->hasAccessToConsultantManagementByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/list-of-consultant*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.loc.index', $vendorCategoryRfp->id) }}" title="List of Consultant" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-th-list"></i> List of Consultant
            </a>
        </li>
        @endif

        @if($vendorCategoryRfp->revisions && $vendorCategoryRfp->revisions()->count() && $user->hasAccessToConsultantManagementCallingRfp($consultantManagementContract))
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/calling-rfp*') or Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/questionnaire*') or Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/rfp-interview*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.calling.rfp.index', $vendorCategoryRfp->id) }}" title="{{{ trans('general.callingRFP')}}}" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-trophy"></i> {{{ trans('general.callingRFP')}}}
            </a>
        </li>
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/open-rfp*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.open.rfp.index', $vendorCategoryRfp->id) }}" title="{{{ trans('general.openRFP')}}}" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-star"></i> {{{ trans('general.openRFP')}}}
            </a>
        </li>

        @if($vendorCategoryRfp->approvalDocument && $vendorCategoryRfp->approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_APPROVED)
        <li class="{{ (Request::is('consultant-management/consultant-rfp/'.$vendorCategoryRfp->id.'/letter-of-award*'))? 'active' : null }}">
            <a href="{{ route('consultant.management.loa.index', $vendorCategoryRfp->id) }}" title="LOA" class="text-truncate">
                <i class="fa fa-sm fa-fw fa-file-code"></i> LOA
            </a>
        </li>
        @endif

        @endif
        
    </ul>
</li>
@endforeach