@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{{ trans('forms.approval') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('forms.approval') }}}</h2>
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
                ajaxURL: "{{ route('vendorPerformanceEvaluation.companyForms.approval.list') }}",
                layout:"fitColumns",
                pagination: 'remote',
                ajaxFiltering:true,
                columns:[
                    {title:"{{ trans('general.no') }}", field: 'counter', width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"project", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.company') }}", field:"company", width:250, cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", width:200, cssClass:"text-center text-middle", headerSort:false, headerFilter:true, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions)}} },
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_category", width:300, cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", width:200, cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {
                        title: "{{ trans('vendorManagement.currentVPEScore') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('vendorManagement.score') }}", field:"score_0", width: 120, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorManagement.grade') }}", field:"grade_0", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    },{
                        title: "{{ trans('vendorManagement.lastVPEScore') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('vendorManagement.score') }}", field:"score_1", width: 120, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorManagement.grade') }}", field:"grade_1", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    },{
                        title: "{{ trans('vendorManagement.secondLastVPEScore') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('vendorManagement.score') }}", field:"score_2", width: 120, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorManagement.grade') }}", field:"grade_2", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    },
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width:100, cssClass:"text-center text-middle", headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($statuses) }},},
                    {title:"{{ trans('general.actions') }}", width: 80, cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection