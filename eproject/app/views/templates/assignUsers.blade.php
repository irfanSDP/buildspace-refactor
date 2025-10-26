@extends('layout.main')

@section('assign-users-table-html')
<?php $hasEditorOption = isset($hasEditorOption) ? $hasEditorOption : true ?>

<table class="table  table-hover" data-type="assigned-users-table">
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
        @if($hasEditorOption)
            <th>&nbsp;</th>
        @endif
    </tr>
    <tr>
        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
        <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
        <th class="text-middle text-left text-nowrap squeeze">{{{ trans('users.company') }}}</th>
        @if($hasEditorOption)
            <th class="text-middle text-center text-nowrap squeeze">{{{ trans('forms.editor') }}}</th>
        @endif
    </tr>
    </thead>
</table>
@endsection

@section('assign-users-table-js')
$('[data-type=assigned-users-table]').DataTable({
    "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
    "t"+
    "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
    "autoWidth" : false,
    scrollCollapse: true,
    "iDisplayLength":10,
    bServerSide:true,
    "sAjaxSource":"{{{ $assignedUsersRoute }}}",
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
                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" type="tooltip" title="{{{ trans('users.unAssignUsers') }}}" class="btn btn-xs btn-danger">'
                    + '<i class="fa fa-times"></i>'
                    + '</div>';
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
        @if($hasEditorOption)
        , {
            "aTargets": [ 4 ],
            "mData": function ( source, type, val ) {
                var checked = source['isEditor'] ? 'checked' : '';
                return '<input type="checkbox" data-action="set-as-verifier" data-url="' + source['route:setEditor'] + '" ' + checked + '/>';
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        }
        @endif
    ]
});
@endsection

@section('content')

    <?php $hasEditorOption = isset($hasEditorOption) ? $hasEditorOption : true ?>

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-key green" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('modulePermissions.delegateHelp') }}}"></i> {{{ trans('users.assignUsers') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row" data-type="widget">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header class="rounded-less-nw">
                    <h2> {{{ trans('users.assignUsers') }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div class="table-responsive">
                            @section('assign-users-table-html')
                            @show
                        </div>
                    </div>
                    <div class="widget-footer">
                        <a class="btn btn-warning" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers">
                            <i class="fa fa-check-square"></i>
                            {{{ trans('users.assignUsers') }}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('form_partials.assign_users_modal')

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>

        var userIds = [];

        $('#assignUsersModal [data-action=submit]').on('click', function(){
            app_progressBar.toggle();
            $.ajax({
                url: '{{{ $assignRoute }}}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    users: userIds
                },
                success: function (data) {
                    if (data['success']) {
                        userIds = [];
                        $('#assignUsersModal').modal('hide');
                        $('[data-type=assigned-users-table]').DataTable().draw();
                        app_progressBar.maxOut(0, function(){
                            app_progressBar.toggle();
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        $(document).on('click', '[data-action=revoke]', function(){
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
                        $('[data-type=assigned-users-table]').DataTable().draw();
                        app_progressBar.maxOut(0, function(){
                            app_progressBar.toggle();
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        $('[data-action=assignUsers]').on('click', function(){
            checkboxFx.disable('.assign-user');
            assignUsersTable.draw();
        });

        $(document).on('change', '.assign-user', function(){
            if($(this).prop('checked'))
            {
                arrayFx.push(userIds, $(this).val());
            }
            else{
                arrayFx.remove(userIds, $(this).val());
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
            "sAjaxSource":"{{{ $assignableUsersRoute }}}",
            "drawCallback": function( settings ) {
                checkboxFx.enable('.assign-user');
                checkboxFx.checkSelected('.assign-user', userIds);
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
        var assignUsersTable = $('#assign-users-table').DataTable();

        $(document).on('change', 'input[type=checkbox][data-action=set-as-verifier]', function(){
            var self = this;
            app_progressBar.toggle();
            $.ajax({
                url: $(this).data('url'),
                method: 'POST',
                data: {
                    _method: 'POST',
                    _token: '{{{ csrf_token() }}}'
                },
                success: function (data) {
                    if (data['success']) {
                        $('[data-type=table]').DataTable().draw();
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        $("#assign-users-table thead th input[type=text]").on( 'keyup change', function () {
            assignUsersTable
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        @section('assign-users-table-js')
        @show

        $("[data-type=assigned-users-table] thead th.hasinput input[type=text]").on( 'keyup change', function () {
            $('[data-type=assigned-users-table]').DataTable()
                  .column( $(this).parent().index()+':visible' )
                  .search( this.value )
                  .draw();
        });

    </script>
@endsection