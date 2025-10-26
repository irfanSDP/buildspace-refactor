@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('modulePermissions.userPermissions') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-key green" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('modulePermissions.delegateHelp') }}}"></i> {{{ trans('modulePermissions.maintenanceModules') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

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

    @foreach($modules as $moduleId => $moduleName)
        <div class="row" data-type="widget" data-module-id="{{{ $moduleId }}}" hidden>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget ">
                    <header class="rounded-less-nw">
                        <h2> {{{ $moduleName }}} </h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <div class="table-responsive">
                                <table class="table  table-hover" data-type="table" data-module-id="{{{ $moduleId }}}">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th class="hasinput">
                                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                        </th>
                                        <th class="hasinput">
                                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                        </th>
                                        <th class="hasinput">
                                            <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                                        <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                                        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                                        <th class="text-middle text-left text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="widget-footer">
                            <a class="btn btn-warning" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" data-module-id="{{{ $moduleId }}}">
                                <i class="fa fa-check-square"></i>
                                {{{ trans('modulePermissions.assignUsers') }}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @include('form_partials.assign_users_modal')

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>

        var moduleIdentifier = 0;

        var modules = [];

        $('#assignUsersModal [data-action=submit]').on('click', function(){
            app_progressBar.toggle();
            $.ajax({
                url: '{{{ route('indonesiaCivilContract.permissions.assign', array($project->id)) }}}',
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
            "sAjaxSource":"{{ route('indonesiaCivilContract.permissions.assignable', array($project->id)) }}",
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

        @foreach($modules as $moduleId => $moduleName)
            $('[data-type=table][data-module-id={{{ $moduleId }}}]').DataTable({
                "sDom": "t",
                "autoWidth" : false,
                scrollCollapse: true,
                "iDisplayLength":10,
                bServerSide:true,
                "sAjaxSource":"{{ route('indonesiaCivilContract.permissions.assigned', array($project->id)) }}",
                "fnServerParams": function ( aoData ) {
                    aoData.push( { name: 'module_identifier', value: "{{{ $moduleId }}}" } );
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
                            var displayData = source['name']
                                        + '<div class="pull-right">'
                                        + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" data-module-id="{{{ $moduleId }}}" type="tooltip" title="{{{ trans('modulePermissions.unassignUser') }}}" class="btn btn-xs btn-danger">'
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

            $("[data-type=table][data-module-id={{{ $moduleId }}}] thead th.hasinput input[type=text]").on( 'keyup change', function () {
                $('[data-type=table][data-module-id={{{ $moduleId }}}]').DataTable()
                    .column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();
        });
        @endforeach

        $('select[data-type=module_filter]').on('change', function(){
            $('[data-type=widget][data-module-id]').hide();
            $('[data-type=widget][data-module-id='+$(this).val()+']').show();
        });

        <?php
            reset($modules);
            $firstKey = key($modules);
        ?>
        $('select[data-type=module_filter]').val({{{ $firstKey }}}).trigger('change');
    </script>
@endsection