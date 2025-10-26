@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.activeVendorList') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.activeVendorList') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.activeVendorList') }}}</h2>
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
                ajaxURL: "{{ route('vendorManagement.activeVendorList.vendorGroups.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"name", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: {
                            tag: 'a',
                            rowAttributes: {href:'route:view'},
                            innerHtml: function(rowData){
                                return rowData['name'];
                            }
                        }
                    }},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 200, cssClass:"text-center text-middle", headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 90, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
                ],
            });
        });
    </script>
@endsection