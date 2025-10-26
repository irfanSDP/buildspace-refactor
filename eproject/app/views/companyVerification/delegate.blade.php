@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('companyVerification.delegateVerification') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-key green" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('companyVerification.delegateHelp') }}}"></i> {{{ trans('companyVerification.delegateVerification') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a class="btn btn-primary btn-md pull-right header-btn" data-toggle="modal" data-target="#assignUsersModal">
                <i class="fa fa-check-square"></i>
                {{{ trans('companyVerification.assignUsers') }}}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('companyVerification.assignedUsers') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="appointed-users-table">
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
                                    <th style="width: 5%;">{{{ trans('users.number') }}}</th>
                                    <th style="width: auto;">{{{ trans('users.name') }}}</th>
                                    <th style="width: 15%;" class="text-center">{{{ trans('users.email') }}}</th>
                                    <th class="text-center">{{{ trans('users.company') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('companyVerification.partials.assign_users_modal')

@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $('[data-type=tooltip]').tooltip();
        $("#appointed-users-table thead th input[type=text]").on( 'keyup change', function () {
            table
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        var table = $('#appointed-users-table').DataTable({
            "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
            "t"+
            "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide: true,
            "sAjaxSource":"{{ $getAssigned }}",
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['indexNo'];
                        return displayData;
                    },
                    "sClass": "text-middle text-center"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['name']
                                + '<div class="pull-right">'
                                + '<a href="' + source['route:unassign'] + '" type="tooltip" title="{{{ trans('companyVerification.unassignUser') }}}" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">'
                                + '<i class="fa fa-times"></i>'
                                + '</div>'
                                ;
                        return displayData;
                    },
                    "sClass": "text-middle"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['email'];
                        return displayData;
                    },
                    "sClass": "text-middle text-center"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['companyName'];
                        return displayData;
                    },
                    "sClass": "text-middle text-left occupy-min"
                }
            ]
        });

        $("#assign-users-table thead th input[type=text]").on( 'keyup change', function () {
            assignUsersTable
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        var assignUsersTable = $('#assign-users-table').DataTable({
            "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
            "t"+
            "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
            "autoWidth" : false,
            scrollCollapse: true,
            "iDisplayLength":10,
            bServerSide:true,
            "sAjaxSource":"{{ $getAssignable }}",
            "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['indexNo'];
                        return displayData;
                    },
                    "sClass": "text-middle text-center"
                },
                {
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['name'];
                        return displayData;
                    },
                    "sClass": "text-middle"
                },
                {
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['email'];
                        return displayData;
                    },
                    "sClass": "text-middle text-center"
                },
                {
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        var displayData = source['companyName'];
                        return displayData;
                    },
                    "sClass": "text-middle text-left occupy-min"
                },
                {
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        var displayData = '<input type="checkbox" class="assign-user" value="' + source['id'] + '">';
                        return displayData;
                    },
                    "sClass": "text-middle text-center occupy-min"
                }
            ]
        });

        assignUsersTable.on( 'draw.dt', function () {
            vue.checkSelected();
        } );

        $(document).on('change', '.assign-user', function(){
            if($(this).prop('checked'))
            {
                vue.push($(this).val());
            }
            else{
                vue.remove($(this).val());
            }
        });

        var vue = new Vue({
            el: '#assignUsersModal',

            data: {
                users: []
            },

            methods: {
                checkSelected: function()
                {
                    var checkboxes = $('.assign-user');
                    checkboxes.each(function()
                    {
                        if(vue.isSelected($(this).val()))
                        {
                            $(this).prop('checked', true);
                        }
                    });
                },
                isSelected: function(userId)
                {
                    return (this.users.indexOf(userId) > -1);
                },
                push: function(userId)
                {
                    this.users.push(userId);
                },
                remove: function(userId)
                {
                    var index = this.users.indexOf(userId);
                    if(index > -1)
                    {
                        this.users.splice(index, 1);
                    }
                },
                assignUsers: function()
                {
                    $.ajax({
                        url: '{{{ route('users.companies.verification.assign') }}}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            users: vue.users
                        },
                        success: function (data) {
                            if (data['success']) {
                                location.reload();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                }
            }
        });

        $(document).on('click', '[data-action=assign-users-submit]', function(){
            vue.assignUsers();
        });
    </script>
@endsection