@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.unsuccessfulVendorList') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.unsuccessfulVendorList') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.unsuccessfulVendorList') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.unsuccessfulVendorList.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: {
                            tag: 'a',
                            rowAttributes: {href:'route:view'},
                            innerHtml: function(rowData){
                                return rowData['name'];
                            }
                        }
                    }},
                    {title:"{{ trans('vendorManagement.unsuccessfulDate') }}", field:"unsuccessfulDate", width: 150, cssClass:"text-center text-middle", headerSort:true},
                    {title:"{{ trans('vendorManagement.purgeDate') }}", field:"purgeDate", width: 150, cssClass:"text-center text-middle", headerSort:true},
                ],
            });
        });
    </script>
@endsection