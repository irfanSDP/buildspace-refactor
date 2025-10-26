@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ link_to_route('requestForVariation.user.permissions.index', trans('requestForVariation.requestForVariation').' '.trans('requestForVariation.userPermissions'), [$project->id]) }}</li>
        <li>{{{ str_limit($userPermissionGroup->name, 50) }}}</li>
	</ol>

	@include('projects.partials.project_status')
@endsection

<?php use PCK\RequestForVariation\RequestForVariationUserPermission; ?>
@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            {{{ trans('requestForVariation.userPermission') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('requestForVariation.user.permissions.index', [$project->id]) }}" class="btn btn-default btn-md pull-right header-btn">{{ trans('forms.back') }}</a>
        <a href="{{route('requestForVariation.user.permissions.edit', [$project->id, $userPermissionGroup->id])}}" class="btn btn-primary btn-md pull-right header-btn" style="margin-right:5px;">
            <i class="fa fa-edit"></i> {{trans('requestForVariation.editUserPermission')}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-lg-12">
        <div class="jarviswidget well">

            <div class="row">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('requestForVariation.groupName') }}}:</dt>
                        <dd>{{{ $userPermissionGroup->name }}}</dd>
                    </dl>
                    <hr class="simple">

                    <ul id="rolesListTab" class="nav nav-tabs bordered">
                        @foreach($roles as $roleId => $roleTxt)
                        <li @if($roleId==RequestForVariationUserPermission::ROLE_SUBMIT_RFV) class="active" @endif>
                            <a href="#userListRole-{{{$roleId}}}" data-toggle="tab">
                                <i class="fa fa-fw fa-lg fa-users"></i> {{{ $roleTxt }}}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                        
                    <div id="roleListTabContentPane" class="tab-content padding-10" style="height: 100%;">
                        @foreach($roles as $roleId => $roleTxt)
                        <div class="tab-pane fade in @if($roleId==RequestForVariationUserPermission::ROLE_SUBMIT_RFV) active @endif" id="userListRole-{{{$roleId}}}">
                            <table id="userListRoleTable-{{{$roleId}}}" class="table  table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-middle text-center text-nowrap" style="width:60px;">{{ trans('general.no') }}</th>
                                        <th class="text-middle text-left" style="width:auto;">{{ trans('general.name') }}</th>
                                        <th class="text-middle text-center text-nowrap" style="width:180px;">{{ trans('requestForVariation.email') }}</th>
                                        <th class="text-middle text-center" style="width:120px;">{{ trans('requestForVariation.viewCostEstimate') }}</th>
                                        <th class="text-middle text-center" style="width:100px;">{{ trans('requestForVariation.viewVariationOrderReport') }}</th>
                                        @if($roleId == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL)
                                        <th class="text-middle text-center" style="width:80px;">{{ trans('requestForVariation.isEditor') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $userPermissions = $userPermissionGroup->userPermissions()->where('module_id', $roleId)->get();
                                    ?>
                                    @if($userPermissions->count())
                                        @foreach($userPermissions as $idx => $userPermission)
                                        <?php
                                        $user = $userPermission->user;
                                        if($user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER))
                                        {
                                            $companyName = $project->subsidiary->name;
                                        }
                                        else
                                        {
                                            $companyName = ($company = $user->getAssignedCompany($project)) ? $company->name : "-";
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-middle text-center text-nowrap">{{{($idx+1)}}}</td>
                                            <td class="text-middle text-left" style="width:auto;">{{{ $user->name }}}<br /><div class="label bg-color-teal">{{{$companyName}}}</div></td>
                                            <td class="text-middle text-center text-nowrap" style="width:180px;">{{{ $user->email }}}</td>
                                            <td class="text-middle text-center" style="width:120px;">
                                                @if($userPermission->can_view_cost_estimate)
                                                <i class="fa fa-lg fa-check-square"></i>
                                                @else
                                                <i class="fa fa-lg fa-square-o"></i>
                                                @endif
                                            </td>
                                            <td class="text-middle text-center" style="width:100px;">
                                                @if($userPermission->can_view_vo_report)
                                                <i class="fa fa-lg fa-check-square"></i>
                                                @else
                                                <i class="fa fa-lg fa-square-o"></i>
                                                @endif
                                            </td>
                                            @if($roleId == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL)
                                            <td class="text-middle text-center" style="width:80px;">
                                                @if($userPermission->is_editor)
                                                <i class="fa fa-lg fa-check-square"></i>
                                                @else
                                                <i class="fa fa-lg fa-square-o"></i>
                                                @endif
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    @else
                                    <tr>
                                        <td class="text-middle text-center text-nowrap" colspan="@if($roleId == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL) 6 @else 5 @endif">
                                            {{{trans('requestForVariation.noUserAssignedToThisRole')}}}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                    </div>
                    <!-- end tabs -->
                    <br />
            </div>
        
        </div>
    </div>
</div>

@endsection