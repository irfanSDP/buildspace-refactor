@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('companies.companies') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-building"></i> {{{ trans('companies.companies') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{route('companies.create')}}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('companies.addNewCompany') }}}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('companies.companies') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover" id="dt_basic">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                    </th>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="width: 40px;">{{{ trans('companies.number') }}}</th>
                                    <th style="width: auto;min-width:240px;">{{{ trans('companies.name') }}}</th>
                                    <th style="width: 80px;" class="text-center">&nbsp;</th>
                                    <th style="width: 120px;" class="text-center">{{{ trans('companies.referenceNumber') }}}</th>
                                    <th style="width: 180px;" class="text-center">{{{ trans('companies.contractGroupCategory') }}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            $(document).on('draw.dt', '#dt_basic', function(){
                $('[type=tooltip]').tooltip();
            });

            $("#dt_basic thead th input[type=text]").on( 'keyup change', function () {
                table
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            });

            var table = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false,
                scrollCollapse: true,
                "iDisplayLength":10,
                bServerSide:true,
                "sAjaxSource":"{{ $datasource }}",
                "aoColumnDefs": [{
                    "aTargets": [ 0 ],
                    orderable: false,
                    "mData": function ( source, type, val ) {
                        return source.indexNo;
                    },
                    "sClass": "text-middle text-center"
                },{
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var displayData =
                            '<div class="row">'
                            + '<a href="' + source['route:companies.edit'] + '" class="plain">'
                            + source.companyName
                            +'</a>'
                            + '<p>'
                            + '<span class="label label-info">'
                            + source.mainContact
                            + '</span>'
                            + '&nbsp;'
                            + '<span class="label label-info">'
                            + source.email
                            + '</span>'
                            + '&nbsp;'
                            + '<span class="label label-success">'
                            + source.createdAt
                            + '</span>'
                            + '</p>'
                            + '</div>';
                        return displayData;
                    },
                    "sClass": "text-middle"
                },{
                    "aTargets": [ 2 ],
                    orderable: false,
                    "mData": function ( source, type, val ) {
                        var displayData = '<div class="action">'
                            + '<a href="' + source['route:companies.users'] + '" type="tooltip" title="View Users" class="btn btn-xs btn-default">'
                            + '<i class="fa fa-users"></i>'
                            + '</a>&nbsp;'
                            + '<div class="btn-group btn-group-xs">'
                            + '<a href="' + source['route:companies.delete'] + '" type="tooltip" title="Delete Company" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">'
                            + '<i class="fa fa-times"></i>'
                            + '</a>'
                            + '</div>';
                        return displayData;
                    },
                    "sClass": "text-middle text-center"
                },{
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source.referenceNo;
                    },
                    "sClass": "text-middle text-center"
                },{
                    "aTargets": [ 4 ],
                    "mData": function ( source, type, val ) {
                        return source.contractGroupCategory;
                    },
                    "sClass": "text-middle text-center"
                }]
            });

        });
    </script>
@endsection

@section('css')
    <style>
        .reference-no {
            max-width:150px;
            overflow: hidden;
            font-family: monospace;
        }
        div.tooltip-inner {
            max-width: 500px;
            background-color: white;
            color: black;
            font-family: monospace;
        }
    </style>
@endsection