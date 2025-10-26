@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('companyVerification.companyVerification') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-building"></i> {{{ trans('companyVerification.companyVerification') }}}
            </h1>
        </div>
        @if($user->canGrantPrivilegeToVerifyCompanies())
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <a href="{{route('users.companies.verification.delegate')}}" class="btn btn-primary btn-md pull-right header-btn" type="tooltip" data-toggle="tooltip" data-placement="bottom" title="{{{ trans('companyVerification.delegateHelp') }}}">
                    <i class="fa fa-key"></i>
                    {{{ trans('companyVerification.delegateVerification') }}}
                </a>
            </div>
        @endif
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
                                    <th style="width:40px;">&nbsp;</th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                    </th>
                                    <th style="width:180px;">
                                        <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                    </th>
                                    <th style="width:120px;">
                                        <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('companies.number') }}}</th>
                                    <th class="text-middle text-left text-nowrap">{{{ trans('companies.name') }}}</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('companies.referenceNumber') }}}</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('companies.contractGroupCategory') }}}</th>
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
                        "mData": function ( source, type, val ) {
                            var displayData = source['indexNo']
                            return displayData;
                        },
                        "sClass": "text-middle text-center"
                },{
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        var verifyTitle = '{{{ trans('companyVerification.confirm') }}}';
                        var deleteTitle = '{{{ trans('companyVerification.delete') }}}';
                        var csrf_token = '{{ csrf_token() }}';
                        var displayData =
                                '<div class="row">'
                                + '<div class="col-md-9 col-lg-9 col-xs-12">'
                                + '<a href="' + source['route:companies.verification.show'] + '" class="plain">'
                                + source['companyName']
                                +'</a>'
                                + '<p>'
                                + '<span class="label label-info">'
                                + source['mainContact']
                                + '</span>'
                                + '&nbsp;'
                                + '<span class="label label-info">'
                                + source['email']
                                + '</span>'
                                + '</p>'
                                + '</div>'
                                + '<div class="col-md-3 col-lg-3 col-xs-12">'
                                +'<div class="row">'
                                +'<div class="col-md-12 col-lg-12 col-xs-12">'
                                + '<span class="label label-success pull-right">'
                                + source['createdAt']
                                + '</span>'
                                +'</div>'
                                +'</div>'
                                +'<br/>'
                                +'<div class="row">'
                                +'<div class="col-md-12 col-lg-12 col-xs-12">'
                                + '<div class="btn-group btn-group-xs pull-right">'
                                + '<a href="' + source['route:companies.verify'] + '" type="tooltip" title="' + verifyTitle + '" class="btn btn-xs btn-success">'
                                + '<i class="fa fa-check"></i>'
                                + '</a>'
                                + '<a href="' + source['route:companies.verification.delete'] + '" type="tooltip" title="' + deleteTitle + '" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="' + csrf_token + '">'
                                + '<i class="fa fa-times"></i>'
                                + '</a>'
                                + '</div>'
                                + '</div>'
                                +'</div>'
                                +'</div>'
                                + '</div>'
                                + '</div>';
                        return displayData;
                    },
                    "sClass": "text-middle"
                },{
                    "aTargets": [ 2 ],
                    "mData": function ( source, type, val ) {
                        var displayData =
                                '<span class="monospace" type="tooltip" title="' + source['referenceNo'] + '" data-placement="top">'
                                + source['referenceNo']
                                + '</span>';
                        return displayData;
                    },
                    "sClass": "text-middle text-center squeeze"
                },{
                    "aTargets": [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['contractGroupCategory'];
                    },
                    "sClass": "text-middle text-center squeeze text-nowrap"
                }]
            });

        });
    </script>
@endsection