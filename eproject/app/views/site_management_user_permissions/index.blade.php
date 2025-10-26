@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('siteManagement.site_management') }}}</li>
        <li>{{{ trans('siteManagement.user_management') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-key green" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('siteManagement.userManagementHelp') }}}"></i> {{{ trans('siteManagement.user_management') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <!-- Site Management Defect User Permissions -->

    <div class="well no-padding-bottom">
        <form class="form-horizontal">
            <div class="form-group">

                <label class="col-sm-2 control-label">
                    <i class="fa fa-search"></i>
                    {{ trans('general.search') }}
                </label>

                <div class="col-sm-9">
                    <select class="select2 fill-horizontal" data-type="module_filter">
                        @foreach($modules as $moduleId => $moduleName)
                            <option value="{{{ $moduleId }}}">{{{ $moduleName }}}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </form>
    </div>

<div class="container-fluid">
    <div class="row" data-type="widget" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}" hidden>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ PCK\SiteManagement\SiteManagementUserPermission::getModuleNames(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT) }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table  table-hover" data-type="table" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterName') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterEmail') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterCompany') }}}"/>
                                    </th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.site') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.others') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.pm') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.qs') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-info" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('siteManagement.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')

    <!-- Site Management Defect User Permissions End-->

    <!-- Site Management Daily Labour Report User Permissions -->

    <div class="row" data-type="widget" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}" hidden>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ PCK\SiteManagement\SiteManagementUserPermission::getModuleNames(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS) }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table  table-hover" data-type="table" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterName') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterEmail') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterCompany') }}}"/>
                                    </th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.editor') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.viewer') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-info" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('siteManagement.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')

    <!-- Site Management Daily Labour Report User Permissions End-->

    <!-- Site Management Update Site Progress User Permissions -->

    <div class="row" data-type="widget" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}" hidden>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ PCK\SiteManagement\SiteManagementUserPermission::getModuleNames(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS) }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table  table-hover" data-type="table" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterName') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterEmail') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterCompany') }}}"/>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-info" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('siteManagement.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')

    <!-- Site Management Update Site Progress User Permissions End-->

    <!-- Site Management Site Diary User Permissions -->

    <div class="row" data-type="widget" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}" hidden>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ PCK\SiteManagement\SiteManagementUserPermission::getModuleNames(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY) }}} </h2>
                </header>
                <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table  table-hover" data-type="table" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterName') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterEmail') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterCompany') }}}"/>
                                    </th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.submitter') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.verifier') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.viewer') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-info" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('siteManagement.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')

    <!-- Site Management Site Diary User Permissions End-->

     <!-- Site Management Instruction To Contractor User Permissions -->

     <div class="row" data-type="widget" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}" hidden>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ PCK\SiteManagement\SiteManagementUserPermission::getModuleNames(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR) }}} </h2>
                </header>
                <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table  table-hover" data-type="table" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterName') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterEmail') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterCompany') }}}"/>
                                    </th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.submitter') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.verifier') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.viewer') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-info" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('siteManagement.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')

    <!-- Site Management Instruction To Contractor User Permissions End-->

    <!-- Site Management Daily Report User Permissions -->

    <div class="row" data-type="widget" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}" hidden>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ PCK\SiteManagement\SiteManagementUserPermission::getModuleNames(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT) }}} </h2>
                </header>
                <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table  table-hover" data-type="table" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterName') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterEmail') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('users.filterCompany') }}}"/>
                                    </th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.submitter') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.verifier') }}}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{{ trans('siteManagement.viewer') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-info" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('siteManagement.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')
    <!-- Site Management Daily Report User Permissions End-->
</div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>

        $('select[data-type=module_filter]').on('change', function(){
            $('[data-type=widget][data-module-id]').hide();
            $('[data-type=widget][data-module-id='+$(this).val()+']').show();
        });

        $('select[data-type=module_filter]').val({{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}).trigger('change');

        var moduleIdentifier = 0;

        var modules = [];

        $('#assignUsersModal [data-action=submit]').on('click', function(){
            app_progressBar.toggle();
            $.ajax({
                url: '{{{ route('site-management.permissions.assign', array($project->id)) }}}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    users: modules[moduleIdentifier],
                    module_id: moduleIdentifier
                },
                success: function (data) {
                    if (data['success']) {
                        modules[moduleIdentifier] = [];
                        $('#assignUsersModal').modal('hide');
                        $('[data-type=table][data-module-id=' + moduleIdentifier + ']').DataTable().draw();
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        $(document).on('click', '[data-action=revoke]', function(){
            var self = this;
            app_progressBar.toggle();
            $.ajax({
                url: $(this).data('url'),
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{{ csrf_token() }}}'
                },
                success: function (data) {
                    if (data['success']) {
                        $('[data-type=table][data-module-id=' + $(self).data('module-id') + ']').DataTable().draw();
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        $('[data-action=assignUsers]').on('click', function(){
            checkboxFx.disable('.assign-user');
            moduleIdentifier = $(this).data('module-id');
            assignUsersTable.draw();
        });

        $(document).on('change', '.assign-user', function(){
            if($(this).prop('checked'))
            {
                arrayFx.push(modules[moduleIdentifier], $(this).val());
            }
            else{
                arrayFx.remove(modules[moduleIdentifier], $(this).val());
            }
        });

        var assignUsersTable = $('#assign-users-table').DataTable({
            "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
            "t"+
            "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.assignable', array($project->id)) }}",
            "drawCallback": function( settings ) {
                checkboxFx.enable('.assign-user');
                arrayFx.init(modules, moduleIdentifier);
                checkboxFx.checkSelected('.assign-user', modules[moduleIdentifier]);
            },
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: moduleIdentifier } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        return source['name'];
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        return '<input type="checkbox" class="assign-user" value="' + source['id'] + '">';
                    },
                    "sClass": "text-middle text-center squeeze"
                }
            ]
        });

        $("#assign-users-table thead th input[type=text]").on( 'keyup change', function () {
            assignUsersTable
                    .column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();
        });

        // Site Management Defect Module 

        $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}]').DataTable({
            "sDom": "tpi",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.defectAssignedUsers', array($project->id)) }}",
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: "{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}" } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}';
                        var title = '{{{ trans('modulePermissions.unassignUser') }}}';
                        var displayData = source['name']
                                    + '<div class="pull-right">'
                                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="'+moduleId+'" type="tooltip" title="'+title+'" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}';
                        var checked = source['site'] ? 'checked' : '';
                        return '<input name="defect-permission-'+source['id']+'" type="radio" data-action="toggle-site-status" data-url="'+source['route:toggleSiteStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 5 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}';
                        var checked = source['qa_qs_client'] ? 'checked' : '';
                        return '<input name="defect-permission-'+source['id']+'" type="radio" data-action="toggle-client-status" data-url="'+source['route:toggleClientStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 6 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}';
                        var checked = source['pm'] ? 'checked' : '';
                        return '<input name="defect-permission-'+source['id']+'" type="radio" data-action="toggle-pm-status" data-url="'+source['route:togglePmStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 7 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}';
                        var checked = source['qs'] ? 'checked' : '';
                        return '<input name="defect-permission-'+source['id']+'" type="radio" data-action="toggle-qs-status" data-url="'+source['route:toggleQsStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                }
            ]
        });

        $("[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT }}}]').DataTable()
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });


        // Site Management Daily Labour Reports Module

        $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}]').DataTable({
            "sDom": "tpi",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.dailyLabourReportAssignedUsers', array($project->id)) }}",
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: "{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}" } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}';
                        var title = '{{{ trans('modulePermissions.unassignUser') }}}';
                        var displayData = source['name']
                                    + '<div class="pull-right">'
                                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="'+moduleId+'" type="tooltip" title="'+title+'" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}';
                        var checked = source['editor'] ? 'checked' : '';
                        return '<input name="daily-labour-report-permission-'+source['id']+'" type="radio" data-action="toggle-editor-status" data-url="'+source['route:toggleEditorStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 5 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}';
                        var checked = source['viewer'] ? 'checked' : '';
                        return '<input name="daily-labour-report-permission-'+source['id']+'" type="radio" data-action="toggle-viewer-status" data-url="'+source['route:toggleViewerStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                }
            ]
        });

        $("[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS }}}]').DataTable()
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        // Site Management Update Site Progress Module

        $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}]').DataTable({
            "sDom": "tpi",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.updateSiteProgressAssignedUsers', array($project->id)) }}",
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: "{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}" } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}';
                        var title = '{{{ trans('modulePermissions.unassignUser') }}}';
                        var displayData = source['name']
                                    + '<div class="pull-right">'
                                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="'+moduleId+'" type="tooltip" title="'+title+'" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                }
            ]
        });

        $("[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS }}}]').DataTable()
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        // Site Management Site Diary Module

        $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}]').DataTable({
            "sDom": "tpi",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.siteDiaryAssignedUsers', array($project->id)) }}",
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: "{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}" } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}';
                        var title = '{{{ trans('modulePermissions.unassignUser') }}}';
                        var displayData = source['name']
                                    + '<div class="pull-right">'
                                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="'+moduleId+'" type="tooltip" title="'+title+'" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}';
                        var checked = source['submitter'] ? 'checked' : '';
                        return '<input name="site-diary-permission-'+source['id']+'" type="checkbox" data-action="toggle-submitter-status" data-url="'+source['route:toggleSubmitterStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 5 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}';
                        var checked = source['verifier'] ? 'checked' : '';
                        return '<input name="site-diary-permission-'+source['id']+'" type="checkbox" data-action="toggle-verifier-status" data-url="'+source['route:toggleVerifierStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 6 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}';
                        var checked = source['viewer'] ? 'checked' : '';
                        return '<input name="site-diary-permission-'+source['id']+'" type="checkbox" data-action="toggle-viewer-status" data-url="'+source['route:toggleViewerStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                }
            ]
        });

        $("[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY }}}]').DataTable()
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        // Site Management Instruction To Contractor Module

        $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}]').DataTable({
            "sDom": "tpi",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.instructionToContractorAssignedUsers', array($project->id)) }}",
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: "{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}" } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}';
                        var title = '{{{ trans('modulePermissions.unassignUser') }}}';
                        var displayData = source['name']
                                    + '<div class="pull-right">'
                                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="'+moduleId+'" type="tooltip" title="'+title+'" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}';
                        var checked = source['submitter'] ? 'checked' : '';
                        return '<input name="instruction-to-contractor-permission-'+source['id']+'" type="checkbox" data-action="toggle-submitter-status" data-url="'+source['route:toggleSubmitterStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 5 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}';
                        var checked = source['verifier'] ? 'checked' : '';
                        return '<input name="instruction-to-contractor-permission-'+source['id']+'" type="checkbox" data-action="toggle-verifier-status" data-url="'+source['route:toggleVerifierStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 6 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}';
                        var checked = source['viewer'] ? 'checked' : '';
                        return '<input name="instruction-to-contractor-permission-'+source['id']+'" type="checkbox" data-action="toggle-viewer-status" data-url="'+source['route:toggleViewerStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                }
            ]
        });

        $("[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR }}}]').DataTable()
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        // Site Management Daily Report Module

        $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}]').DataTable({
            "sDom": "tpi",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ route('site-management.permissions.dailyReportAssignedUsers', array($project->id)) }}",
            "fnServerParams": function ( aoData ) {
                aoData.push( { name: 'module_identifier', value: "{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}" } );
            },
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}';
                        var title = '{{{ trans('modulePermissions.unassignUser') }}}';
                        var displayData = source['name']
                                    + '<div class="pull-right">'
                                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="'+moduleId+'" type="tooltip" title="'+title+'" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['companyName'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}';
                        var checked = source['submitter'] ? 'checked' : '';
                        return '<input name="daily-report-permission-'+source['id']+'" type="checkbox" data-action="toggle-submitter-status" data-url="'+source['route:toggleSubmitterStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 5 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}';
                        var checked = source['verifier'] ? 'checked' : '';
                        return '<input name="daily-report-permission-'+source['id']+'" type="checkbox" data-action="toggle-verifier-status" data-url="'+source['route:toggleVerifierStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 6 ],
                    "mData": function ( source, type, val ) {
                        var moduleId = '{{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}';
                        var checked = source['viewer'] ? 'checked' : '';
                        return '<input name="daily-report-permission-'+source['id']+'" type="checkbox" data-action="toggle-viewer-status" data-url="'+source['route:toggleViewerStatus']+'" data-module-id="'+moduleId+'" value="" '+checked+'>';
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                }
            ]
        });

        $("[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=table][data-module-id={{{ PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT }}}]').DataTable()
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        $('[data-type=table]').on('change', '[data-action=toggle-site-status],[data-action=toggle-client-status],[data-action=toggle-pm-status],[data-action=toggle-qs-status],[data-action=toggle-editor-status],[data-action=toggle-viewer-status],[data-action=toggle-submitter-status],[data-action=toggle-verifier-status]', function(){
            var self = this;
            app_progressBar.toggle();
            $.ajax({
                url: $(this).data('url'),
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}'
                },
                success: function (data) {
                    if (data['success']) {
                        $('[data-type=table][data-module-id=' + $(self).data('module-id') + ']').DataTable().draw();
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

    </script>
@endsection