@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        
        <li>{{ link_to_route('requestForVariation.user.permissions.index', trans('requestForVariation.requestForVariation').' '.trans('requestForVariation.userPermissions'), [$project->id]) }}</li>
        @if($userPermissionGroup)
        <li>{{ link_to_route('requestForVariation.user.permissions.show', str_limit($userPermissionGroup->name, 50), [$project->id, $userPermissionGroup->id]) }}</li>
        <li> {{ trans('requestForVariation.editUserPermission') }}</li>
        @else
        <li>{{ trans('requestForVariation.createUserPermission') }}</li>
        @endif
	</ol>

	@include('projects.partials.project_status')
@endsection

@section('css')
<style>
.companyLabel {
    display: inline;
    padding: .2em .6em .3em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: .25em;
}
</style>
@endsection

<?php use PCK\RequestForVariation\RequestForVariationUserPermission; ?>
@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-edit"></i> @if($userPermissionGroup) {{{ trans('requestForVariation.editUserPermission') }}} @else {{{ trans('requestForVariation.createUserPermission') }}} @endif
        </h1>
    </div>
</div>

<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

            <h2>@if($userPermissionGroup) {{{trans('requestForVariation.editUserPermissionGroup')}}} @else {{{trans('requestForVariation.createUserPermissionGroup')}}} @endif</h2>
        </header>

        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                {{ Form::open(['route' => $formRoute, 'class' => 'smart-form', 'method' => 'POST']) }}
                <fieldset>
                    <section>
                        <label class="label">{{{ trans('requestForVariation.groupName') }}}<span class="required">*</span>:</label>
                        <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                            {{ Form::text('name', (!empty($userPermissionGroup)) ? $userPermissionGroup->name : Input::old('name'), array('required' => 'required', 'maxlength'=>100, 'autofocus')) }}
                        </label>
                        {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                    </section>
                </fieldset>

                <fieldset>

                    <ul id="rolesListTab" class="nav nav-tabs bordered">
                        @foreach($roles as $roleId => $roleTxt)
                        <li @if($roleId==$selectedRole) class="active" @endif>
                            <a href="#userListRole-{{{$roleId}}}" data-toggle="tab">
                                <i class="fa fa-fw fa-lg fa-users"></i> {{{ $roleTxt }}}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    
                    <div id="roleListTabContentPane" class="tab-content padding-10" style="height: 100%;">
                        @foreach($roles as $roleId => $roleTxt)
                        <div class="tab-pane fade in @if($roleId==$selectedRole) active @endif" id="userListRole-{{{$roleId}}}">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <a class="btn btn-info pull-right" style="padding:6px 12px;margin-bottom:5px;" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers"  data-role-id="{{{ $roleId }}}">
                                    <i class="fa fa-plus" aria-hidden="true"></i> {{{ trans('requestForVariation.addUsers') }}}
                                </a>
                            </div>
                            
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
                                        <th class="text-middle text-center" style="width:60px;">{{ trans('requestForVariation.remove') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $userPermissions = ($userPermissionGroup) ? $userPermissionGroup->userPermissions()->where('module_id', $roleId)->get() : [];
                                    ?>
                                    @if(!$userPermissionGroup or count($userPermissions)==0)
                                    <tr id="assignedUser-{{{$roleId}}}-empty">
                                        <td class="text-middle text-center text-nowrap" colspan="@if($roleId == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL) 7 @else 6 @endif">{{{trans('requestForVariation.noUserAssignedToThisRole')}}}</td>
                                    </tr>
                                    @else
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
                                        <tr id="assignedUser-{{{$roleId}}}-{{{$user->id}}}">
                                            <td class="text-middle text-center text-nowrap">{{{($idx+1)}}}</td>
                                            <td class="text-middle text-left">{{{$user->name}}}<br /><div class="companyLabel bg-color-teal">{{{$companyName}}}</div>
                                            <input type="hidden" name="user_role[{{{$roleId}}}][{{{$idx}}}][uid]" value={{{$user->id}}}></td>
                                            <td class="text-middle text-center text-nowrap">{{{$user->email}}}</td>
                                            <td class="text-middle text-center text-nowrap"><input type="checkbox" name="user_role[{{{$roleId}}}][{{{$idx}}}][vce]" value=1 @if($userPermission->can_view_cost_estimate) checked @endif></td>
                                            <td class="text-middle text-center text-nowrap"><input type="checkbox" name="user_role[{{{$roleId}}}][{{{$idx}}}][vvor]" value=1 @if($userPermission->can_view_vo_report) checked @endif></td>
                                            @if($roleId == RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL)
                                            <td class="text-middle text-center text-nowrap"><input type="checkbox" name="user_role[{{{$roleId}}}][{{{$idx}}}][ie]" value=1 @if($userPermission->is_editor) checked @endif></td>
                                            @endif
                                            <td class="text-middle text-center text-nowrap">
                                                @if($userPermission->canDelete())
                                                <a href="{{route('requestForVariation.user.permissions.delete', [$project->id, $userPermission->id])}}" data-user-permission-id="{{{$userPermission->id}}}" data-role-id="{{{$roleId}}}" data-user-id="{{{$user->id}}}" class="user_permission-delete btn btn-xs btn-danger">
                                                    <i class="fa fa-fw fa-lg fa-times"></i>
                                                </a>
                                                @else
                                                &nbsp;
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                    </div>
                    <!-- end tabs -->
                    <br />

                </fieldset>
                @if($userPermissionGroup)
                <input type="hidden" name="id" value="{{{$userPermissionGroup->id}}}">
                @endif
                <footer>
                    <a href="@if($userPermissionGroup) {{ route('requestForVariation.user.permissions.show', [$project->id, $userPermissionGroup->id]) }} @else {{ route('requestForVariation.user.permissions.index', [$project->id]) }} @endif" class="btn btn-default">{{ trans('forms.back') }}</a>
                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                </footer>
                {{ Form::close() }}
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
</article>


@include('form_partials.assign_users_modal', [
    'saveButtonLabel' => trans('requestForVariation.addUsers')
])

@endsection

@section('js')
<script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
<script>
$(document).ready(function() {
    'use strict';
    
    var roleIdentifier = 0;
    var users = [];

    var assignUsersTable = $('#assign-users-table').DataTable({
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
        "t"+
        "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth" : false,
        scrollCollapse: true,
        "iDisplayLength":10,
        bServerSide:true,
        "sAjaxSource":"{{ route('requestForVariation.user.permissions.assignable', [$project->id]) }}",
        "drawCallback": function( settings ) {
            var rows = $('#userListRoleTable-'+roleIdentifier+' > tbody > tr')
            $.each(rows, function(idx, row){
                var idStr = $(row).attr('id');
                var res = idStr.split("-");
                var uid = parseInt(res[2]);
                if(!isNaN(uid)){
                    users.push(res[2]);
                }
            });
            checkboxFx.enable('.assign-user');
            checkboxFx.checkSelected('.assign-user', users);
        },
        "fnServerParams": function ( aoData ) {
            @if($userPermissionGroup)
            aoData.push({ name: 'gid', value: {{{$userPermissionGroup->id}}} });
            @endif
            aoData.push({ name: 'role', value: roleIdentifier });
        },
        "aoColumnDefs": [{
            "aTargets": [ 0 ],
            "mData": function ( source, type, val ) {
                return source['indexNo'];
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        },{
            "aTargets": [ 1 ],
            "mData": function ( source, type, val ) {
                return source['name'];
            },
            "sClass": "text-middle text-left text-nowrap"
        },{
            "aTargets": [ 2 ],
            "mData": function ( source, type, val ) {
                return source['email'];
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        },{
            "aTargets": [ 3 ],
            "mData": function ( source, type, val ) {
                return source['companyName'];
            },
            "sClass": "text-middle text-left text-nowrap squeeze"
        },{
            "aTargets": [ 4 ],
            "mData": function ( source, type, val ) {
                return '<input type="checkbox" class="assign-user" value="' + source['id'] + '">';
            },
            "sClass": "text-middle text-center squeeze"
        }]
    });

    $("#assign-users-table thead th input[type=text]").on('keyup change', function () {
        assignUsersTable
        .column( $(this).parent().index()+':visible' )
        .search( this.value )
        .draw();
    });

    $('[data-action=assignUsers]').on('click', function(){
        users = [];
        checkboxFx.disable('.assign-user');
        roleIdentifier = $(this).data('role-id');
        assignUsersTable.draw();
    });

    $('#assignUsersModal [data-action=submit]').on('click', function(){
        $('#userListRoleTable-'+roleIdentifier+' > tbody').empty();
        if(users.length){
            $.ajax({
                url: "{{ route('requestForVariation.user.permissions.user.info', [$project->id]) }}",
                method: 'GET',
                data: {
                    'ids[]': users,
                    gid: @if($userPermissionGroup) {{{$userPermissionGroup->id}}} @else null @endif,
                    role: roleIdentifier
                },
                traditional: true,
                success: function (data) {
                    if(data.rows.length){
                        $.each(data.rows, function(idx, row){
                            $('#userListRoleTable-'+roleIdentifier+' > tbody').append(row);
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        }else{
            $('#userListRoleTable-'+roleIdentifier+' > tbody').append('<tr id="assignedUser-'+roleIdentifier+'-empty"><td class="text-middle text-center text-nowrap" colspan="5">{{{trans('requestForVariation.noUserAssignedToThisRole')}}}</td></tr>');
        }
        
        $('#assignUsersModal').modal('hide');
    });

    $(document).on('change', '.assign-user', function(){
        var uid = $(this).val();
        if($(this).prop('checked')){
            if($.inArray(uid, users) == -1){
                users.push(uid);
            }
        }else{
            if($.inArray(uid, users) > -1){
                users = $.grep(users, function(value) {
                    return value != uid;
                });
            }
        }
    });

    $(document).on("click","a.user_permission-delete", function(e){
        e.preventDefault();
        var id = parseInt($(this).data("userPermissionId"));
        var uid = parseInt($(this).data("userId"));
        var roleId = parseInt($(this).data("roleId"));

        $("#assignedUser-"+roleId+"-"+uid).remove();

        var rowCount = $('#userListRoleTable-'+roleId+' >tbody >tr').length;

        if(rowCount==0){
            var colspan = (roleId=={{{RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL}}}) ? 7 : 6;
            $('#userListRoleTable-'+roleId+' > tbody').append('<tr id="assignedUser-'+roleId+'-empty"><td class="text-middle text-center text-nowrap" colspan="'+colspan+'">{{{trans('requestForVariation.noUserAssignedToThisRole')}}}</td></tr>');
        }

        if(id>0){
            app_progressBar.toggle();
            $.ajax({
                url: $(this).attr('href'),
                type: 'DELETE',
                data: {
                    "_token": '{{{csrf_token()}}}'
                },
                success: function(response) {
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                }
            });
        }
        return false;
    });
});
</script>
@endsection