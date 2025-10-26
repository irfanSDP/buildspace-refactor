@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        <li>{{{ trans('contractManagement.userManagement') }}}</li>
    </ol>
@endsection
<?php

?>
@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractManagement.assignUsers') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    @if(!$isROCUser && !$isLOCUser)
                    <div class="row pt-12">
                        <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="alert alert-warning text-center">
                                <i class="fa-fw fa fa-info"></i>
                                <strong>Info!</strong> You don't have permission to <strong>Assign Users</strong> because your company does not have any role set for this Development Planning.
                            </div>
                        </section>
                    </div>
                    @else
                        <ul id="consultant-management-users-tabs" class="nav nav-tabs">
                        @if($isROCUser && (!empty($recommendationOfConsultantUsers) or !empty($recommendationOfConsultantImportedUsers)))
                            <li class="active">
                                <a href="#consultant-management-users-tab-{{PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT}}" data-toggle="tab">{{{ PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT_TEXT }}}</a>
                            </li>
                        @endif
                        @if($isLOCUser && (!empty($listOfConsultantUsers) or !empty($listOfConsultantImportedUsers)))
                            <li @if($isLOCUser && !$isROCUser) class="active" @endif>
                                <a href="#consultant-management-users-tab-{{PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT}}" data-toggle="tab">{{{ PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT_TEXT }}}</a>
                            </li>
                        @endif
                        </ul>
                        <div id="consultant-management-users-tab-content" class="tab-content padding-10">
                        @if($isROCUser && (!empty($recommendationOfConsultantUsers) or !empty($recommendationOfConsultantImportedUsers)))
                            <div class="tab-pane fade in active" id="consultant-management-users-tab-{{PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT}}">
                            {{ Form::open(['route' => ['consultant.management.user.management.store', $consultantManagementContract->id], 'class' => 'smart-form']) }}
                                <div>
                                    @if($errors->has('roc_empty_editor'))
                                    <label class="input state-error"></label>
                                    {{ $errors->first('roc_empty_editor', '<em class="invalid">:message</em>') }}
                                    @endif
                                    <div class="pull-right">
                                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                                    </div>
                                    <ul id="consultant-management-roc-users-list-tab" class="nav nav-pills">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#consultant-management-roc-company-users-tab" data-toggle="tab"><i class="fas fa-users"></i> {{{ trans('users.users') }}}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#consultant-management-roc-imported-users-tab" data-toggle="tab"><i class="fas fa-users"></i> {{{ trans('users.importedUsers') }}}</a>
                                        </li>
                                    </ul>
                                </div>

                                <div id="consultant-management-roc-users-list-tab-content" class="tab-content" style="padding-top:1rem!important;">
                                    <div class="tab-pane fade in active" id="consultant-management-roc-company-users-tab">
                                        <table class="table table-bordered table-hover" style="text-align: center;">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;width:120px;">{{ trans('users.viewer') }} / {{ trans('users.verifier') }}</th>
                                                    <th style="text-align: center;width:78px;">{{ trans('users.editor') }}</th>
                                                    <th style="text-align: left;">{{ trans('users.name') }}</th>
                                                    <th style="text-align: center;width:72px;">{{ trans('users.admin') }}</th>
                                                    <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(empty($recommendationOfConsultantUsers))
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-warning text-center">
                                                            <i class="fa-fw fa fa-info"></i>
                                                            <strong>Info!</strong> There is no non-imported user @if($company) for {{{$company->name}}} @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @else
                                                    @foreach ($recommendationOfConsultantUsers as $user)
                                                    <tr class="@if($user['account_blocked_status']) alert alert-warning @endif">
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('viewer[]', $user['id'], $user['viewer'] ? true : false, ['id'=>'roc_user-viewer-'.$user['id'], 'data-module' => 'roc', 'data-validate_url' => route('consultant.management.user.management.roc.viewer.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('editor[]', $user['id'], $user['editor'] ? true : false, ['id'=>'roc_user-editor-'.$user['id'], 'data-module' => 'roc', 'data-validate_url' => route('consultant.management.user.management.roc.editor.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td style="text-align: left;">
                                                            {{{ mb_strtoupper($user['name']) }}}
                                                            @if($user['account_blocked_status']) <b class="badge bg-color-red bounceIn animated">{{ trans('users.blocked') }}</b>@endif
                                                            <label class="input state-error"></label><em class="invalid" id="roc_user-{{$user['id']}}-error_msg"></em>
                                                        </td>
                                                        <td>@if($user['is_admin']) {{{ trans('forms.yes') }}} @endif</td>
                                                        <td>{{{ $user['email'] }}}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="tab-pane fade in" id="consultant-management-roc-imported-users-tab">
                                        <table class="table table-bordered table-hover" style="text-align: center;">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;width:120px;">{{ trans('users.viewer') }} / {{ trans('users.verifier') }}</th>
                                                    <th style="text-align: center;width:78px;">{{ trans('users.editor') }}</th>
                                                    <th style="text-align: left;">{{ trans('users.name') }}</th>
                                                    <th style="text-align: center;width:72px;">{{ trans('users.admin') }}</th>
                                                    <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(empty($recommendationOfConsultantImportedUsers))
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-warning text-center">
                                                            <i class="fa-fw fa fa-info"></i>
                                                            <strong>Info!</strong> There is no imported user @if($company) for {{{$company->name}}} @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @else
                                                    @foreach($recommendationOfConsultantImportedUsers as $user)
                                                    <tr class="@if($user['account_blocked_status']) alert alert-warning @endif">
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('viewer[]', $user['id'], $user['viewer'] ? true : false, ['id'=>'roc_user-viewer-'.$user['id'], 'data-module' => 'roc', 'data-validate_url' => route('consultant.management.user.management.roc.viewer.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('editor[]', $user['id'], $user['editor'] ? true : false, ['id'=>'roc_user-editor-'.$user['id'], 'data-module' => 'roc', 'data-validate_url' => route('consultant.management.user.management.roc.editor.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td style="text-align: left;">
                                                            {{{ mb_strtoupper($user['name']) }}}
                                                            @if($user['account_blocked_status']) <b class="badge bg-color-red bounceIn animated">{{ trans('users.blocked') }}</b>@endif
                                                            <label class="input state-error"></label><em class="invalid" id="roc_user-{{$user['id']}}-error_msg"></em>
                                                        </td>
                                                        <td>@if($user['is_admin']) {{{ trans('forms.yes') }}} @endif</td>
                                                        <td>{{{ $user['email'] }}}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                {{ Form::hidden('role', PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) }}
                                {{ Form::close() }}
                            </div>
                        @endif

                        @if($isLOCUser && (!empty($listOfConsultantUsers) or !empty($listOfConsultantImportedUsers)))
                            <div class="tab-pane fade in @if($isLOCUser && !$isROCUser) active @endif " id="consultant-management-users-tab-{{PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT}}">
                            {{ Form::open(['route' => ['consultant.management.user.management.store', $consultantManagementContract->id], 'class' => 'smart-form']) }}
                                <div>
                                    @if($errors->has('loc_empty_editor'))
                                    <label class="input state-error"></label>
                                    {{ $errors->first('loc_empty_editor', '<em class="invalid">:message</em>') }}
                                    @endif
                                    <div class="pull-right">
                                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                                    </div>
                                    <ul id="consultant-management-loc-users-list-tab" class="nav nav-pills">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#consultant-management-loc-company-users-tab" data-toggle="tab"><i class="fas fa-users"></i> {{{ trans('users.users') }}}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#consultant-management-loc-imported-users-tab" data-toggle="tab"><i class="fas fa-users"></i> {{{ trans('users.importedUsers') }}}</a>
                                        </li>
                                    </ul>
                                </div>

                                <div id="consultant-management-loc-users-list-tab-content" class="tab-content" style="padding-top:1rem!important;">
                                    <div class="tab-pane fade in active" id="consultant-management-loc-company-users-tab">
                                        <table class="table table-bordered table-hover" style="text-align: center;">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;width:120px;">{{ trans('users.viewer') }} / {{ trans('users.verifier') }}</th>
                                                    <th style="text-align: center;width:78px;">{{ trans('users.editor') }}</th>
                                                    <th style="text-align: left;">{{ trans('users.name') }}</th>
                                                    <th style="text-align: center;width:72px;">{{ trans('users.admin') }}</th>
                                                    <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(empty($listOfConsultantUsers))
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-warning text-center">
                                                            <i class="fa-fw fa fa-info"></i>
                                                            <strong>Info!</strong> There is no non-imported user @if($company) for {{{$company->name}}} @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @else
                                                    @foreach ($listOfConsultantUsers as $user)
                                                    <tr class="@if($user['account_blocked_status']) alert alert-warning @endif">
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('viewer[]', $user['id'], $user['viewer'] ? true : false, ['data-channel' => 'verifier', 'data-validate_url' => route('consultant.management.user.management.roc.viewer.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('editor[]', $user['id'], $user['editor'] ? true : false, ['data-channel' => 'editor', 'data-validate_url' => route('consultant.management.user.management.roc.editor.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td style="text-align: left;">{{{ mb_strtoupper($user['name']) }}} @if($user['account_blocked_status']) <b class="badge bg-color-red bounceIn animated">{{ trans('users.blocked') }}</b>@endif</td>
                                                        <td>@if($user['is_admin']) {{{ trans('forms.yes') }}} @endif</td>
                                                        <td>{{{ $user['email'] }}}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="tab-pane fade in" id="consultant-management-loc-imported-users-tab">
                                        <table class="table table-bordered table-hover" style="text-align: center;">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;width:120px;">{{ trans('users.viewer') }} / {{ trans('users.verifier') }}</th>
                                                    <th style="text-align: center;width:78px;">{{ trans('users.editor') }}</th>
                                                    <th style="text-align: left;">{{ trans('users.name') }}</th>
                                                    <th style="text-align: center;width:72px;">{{ trans('users.admin') }}</th>
                                                    <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(empty($listOfConsultantImportedUsers))
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-warning text-center">
                                                            <i class="fa-fw fa fa-info"></i>
                                                            <strong>Info!</strong> There is no imported user @if($company) for {{{$company->name}}} @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @else
                                                    @foreach($listOfConsultantImportedUsers as $user)
                                                    <tr class="@if($user['account_blocked_status']) alert alert-warning @endif">
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('viewer[]', $user['id'], $user['viewer'] ? true : false, ['data-channel' => 'verifier', 'data-validate_url' => route('consultant.management.user.management.roc.viewer.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td>
                                                        @if(!$user['account_blocked_status'])
                                                            {{ Form::checkbox('editor[]', $user['id'], $user['editor'] ? true : false, ['data-channel' => 'editor', 'data-validate_url' => route('consultant.management.user.management.roc.editor.remove.validate', [$consultantManagementContract->id, $user['id']]), 'data-user_id' => $user['id']]) }}
                                                        @endif
                                                        </td>
                                                        <td style="text-align: left;">{{{ mb_strtoupper($user['name']) }}} @if($user['account_blocked_status']) <b class="badge bg-color-red bounceIn animated">{{ trans('users.blocked') }}</b>@endif</td>
                                                        <td>@if($user['is_admin']) {{{ trans('forms.yes') }}} @endif</td>
                                                        <td>{{{ $user['email'] }}}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                {{ Form::hidden('role', PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT) }}
                                {{ Form::close() }}
                            </div>
                        @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {

    $("input:checkbox").on('change', function(e){
        var $this = $(this);
        var elemName = $this.attr("name");

        if(!$this.is(":checked")){
            if(elemName.indexOf('viewer') != -1){
                var uid = $this.data("user_id");
                $('#roc_user-editor-'+uid).prop('checked', false);
            }
            $.get($this.data("validate_url"))
            .done(function(data){
                var uid = $this.data("user_id");
                var modName = $this.data("module");
                if(!data.removable){
                    $this.prop('checked', true);
                }
                $('#'+modName+'_user-'+uid+'-error_msg').html(data.msg);
            })
            .fail(function(data){
                console.error('failed');
            });
        }

        if($this.is(":checked") && elemName.indexOf('editor') != -1){
            var uid = $this.data("user_id");
            $('#roc_user-viewer-'+uid).prop('checked', true);
        }
    });

    $("input:checkbox").each(function(){
        
    });
});
</script>
@endsection