@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('tenders.contractors') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-building"></i> {{ trans('tenders.contractors') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                <h2>{{ trans('tenders.contractorListing') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover" id="contractorsTable">
                                <thead>
                                <tr>
                                    <th class="squeeze">&nbsp;</th>
                                    <th class="squeeze">
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control" placeholder="{{ trans('tables.filter') }}"/>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="squeeze">{{ trans('general.no') }}</th>
                                    <th class="squeeze">{{ trans('companies.companyName') }}</th>
                                    <th>{{ trans('companies.address') }}</th>
                                    <th>{{ trans('companies.mainContact') }}</th>
                                    <th>{{ trans('companies.email') }}</th>
                                    <th>{{ trans('companies.telephone') }}</th>
                                    <th>{{ trans('companies.fax') }}</th>
                                    <th>{{ trans('companies.country') }}</th>
                                    <th>{{ trans('companies.state') }}</th>
                                    <th>{{ trans('tenders.typeOfWork') }}</th>
                                    <th>{{ trans('tenders.subCategory') }}</th>
                                    <th>{{ trans('tenders.registrationStatus') }}</th>
                                    <th>{{ trans('tenders.previousCPE') }}</th>
                                    <th>{{ trans('tenders.currentCPE') }}</th>
                                    <th>{{ trans('tenders.jobLimit') }}</th>
                                    <th>CIDB Category</th>
                                    <th>{{ trans('general.remarks') }}</th>
                                    <th>{{ trans('general.registeredDate') }}</th>
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
    <script src="{{ asset('js/plugin/datatables/FixedColumns-master/js/dataTables.fixedColumns.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#contractorsTable").parent().on( 'keyup change', "th input[type=text]", function () {
                table
                    .column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();
            } );

            var table = $('#contractorsTable').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : true,
                scrollY:"auto",
                scrollX:true,
                scrollCollapse: true,
                "iDisplayLength":10,
                bServerSide:true,
                "sAjaxSource":"{{ route('contractorsData') }}",
                "aoColumnDefs": [{
                    "sWidth": "40px", 
                        "aTargets": [ 0 ],
                        "mData": function ( source, type, val ) {
                            return source['indexNo'];
                        },
                        "sClass": "text-middle text-center squeeze"
                },{
                    "aTargets": [ 1 ],
                    "mData": function ( source, type, val ) {
                        return '<a href="' + source['route:contractors.show'] + '">' + source['name'] + '</a>';
                    },
                    "sClass": "text-middle text-left text-nowrap"
                },{
                    "aTargets": [ 2 ],
                    "mData": function (source, type, val) {
                        return source['address'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 3 ],
                    "mData": function (source, type, val) {
                        return source['mainContact'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 4 ],
                    "mData": function (source, type, val) {
                        return source['email'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 5 ],
                    "mData": function (source, type, val) {
                        return source['telephoneNo'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 6 ],
                    "mData": function (source, type, val) {
                        return source['faxNo'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 7 ],
                    "mData": function (source, type, val) {
                        return source['country'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 8 ],
                    "mData": function (source, type, val) {
                        return source['state'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 9 ],
                    "mData": function (source, type, val) {
                        var displaySubData = "";
                        for(subDataIndex in source['workCategories'])
                        {
                            displayIndex = parseInt(subDataIndex) + 1;
                            displaySubData += displayIndex + ".&nbsp" + source['workCategories'][subDataIndex] + "<br/>";
                        }
                        var displayData = displaySubData;

                        if( !displayData ) displayData = '-';

                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 10 ],
                    "mData": function (source, type, val) {
                        var displaySubData = "";
                        for(subDataIndex in source['workSubcategories'])
                        {
                            displayIndex = parseInt(subDataIndex) + 1;
                            displaySubData += displayIndex + ".&nbsp" + source['workSubcategories'][subDataIndex] + "<br/>";
                        }
                        var displayData = displaySubData;

                        if( !displayData ) displayData = '-';

                        return displayData;
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 11 ],
                    "mData": function (source, type, val) {
                        return source['registrationStatus'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 12 ],
                    "mData": function (source, type, val) {
                        return source['previousCPE'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },
                {
                    "aTargets": [ 13 ],
                    "mData": function (source, type, val) {
                        return source['currentCPE'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 14 ],
                    "mData": function ( source, type, val ) {
                        return source[ 'jobLimitSymbol' ] + '&nbsp' + source[ 'jobLimit' ];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 15 ],
                    "mData": function (source, type, val) {
                        return source['cidbCategory'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 16 ],
                    "mData": function (source, type, val) {
                        return source['remarks'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                },{
                    "aTargets": [ 17 ],
                    "mData": function (source, type, val) {
                        return source['registeredDate'];
                    },
                    "sClass": "text-middle text-left text-nowrap squeeze"
                }]
            });
        });
    </script>
@endsection

@section('css')
    <!--style>
        /* HACK
        Alignment issue with FixedColumn plugin.
         */
        #contractorsTable_wrapper .DTFC_LeftWrapper {
            margin: -6px;
        }
    </style-->
@endsection