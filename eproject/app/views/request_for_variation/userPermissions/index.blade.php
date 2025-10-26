@extends('layout.main')
@section('breadcrumb')
        <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ trans('requestForVariation.requestForVariation') }}</li>
        <li>{{ trans('requestForVariation.userPermissions') }}</li>
        </ol>
        @include('projects.partials.project_status')
@endsection
<?php use PCK\RequestForVariation\RequestForVariationUserPermission; ?>
@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
        <i class="fa fa-key green"></i> {{{ trans('requestForVariation.userPermissions') }}}
        </h1>
    </div>
    
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{route('requestForVariation.user.permissions.create', [$project->id])}}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('requestForVariation.createUserPermission') }}}
        </a>
    </div>
</div>
<div class="row" data-type="widget">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header class="rounded-less-nw">
                <h2>{{{ trans('requestForVariation.userPermissionGroups') }}}</h2>
            </header>
            <div class="widget-body">
                <div class="table-responsive">
                    <table class="table  table-hover" data-type="table" id="user_permission_group-table">
                        <thead>
                            <tr>
                                <th style="width:80px;">&nbsp;</th>
                                <th class="hasinput">
                                    <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                </th>
                                <th colspan="2">&nbsp;</th>
                            </tr>
                            <tr>
                                <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                <th class="text-middle text-left text-nowrap">{{{ trans('requestForVariation.groupName') }}}</th>
                                <th class="text-middle text-center" style="width:60px;">{{ trans('requestForVariation.remove') }}</th>
                                <th class="text-middle text-center text-nowrap" style="width:120px;">{{{ trans('requestForVariation.updatedAt') }}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $userPermissionGroups = $project->requestForVariationUserPermissionGroups;
                            ?>
                            @if($userPermissionGroups->count())
                                @foreach($project->requestForVariationUserPermissionGroups as $idx => $userPermissionGroup)
                                <tr>
                                    <td class="text-middle text-center text-nowrap">{{{($idx+1)}}}</td>
                                    <td class="text-middle text-left" style="width:auto;"><a href="{{route('requestForVariation.user.permissions.show', [$project->id, $userPermissionGroup->id])}}">{{{$userPermissionGroup->name}}}</a></td>
                                    <td class="text-middle text-center text-nowrap">
                                    @if($userPermissionGroup->canDelete())
                                        <a href="{{route('requestForVariation.user.permissions.group.delete', [$project->id, $userPermissionGroup->id])}}" class="user_permission_group-delete btn btn-xs btn-danger">
                                            <i class="fa fa-fw fa-lg fa-times"></i>
                                        </a>
                                    @else
                                        &nbsp;
                                    @endif
                                    </td>
                                    <td class="text-middle text-center text-nowrap">{{{date('d-M-Y', strtotime($project->getProjectTimeZoneTime($userPermissionGroup->created_at)))}}}</td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
<script>
$(document).ready(function() {
    $('#user_permission_group-table').DataTable({
        "autoWidth" : false,
        scrollCollapse: true,
        paging: true
    });

    $("#user_permission_group-table thead th.hasinput input[type=text]").on( 'keyup change', function () {
        $('#user_permission_group-table').DataTable()
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();
    });

    $("a.user_permission_group-delete").on("click", function(e){
        e.preventDefault();
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
                location.reload();
            }
        });

        return false;
    });
});
</script>
@endsection