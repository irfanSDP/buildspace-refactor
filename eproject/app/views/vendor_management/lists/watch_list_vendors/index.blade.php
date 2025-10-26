@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.watchList') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-user-lock"></i> {{{ trans('vendorManagement.watchList') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            <div class="dropdown {{{ $classes ?? 'pull-right' }}}">
                <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
                <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
                    <li class="dropdown-submenu left">
                        <a tabindex="-1" href="javascript:void(0);" class="text-center">{{ trans('general.summary') }}</a>
                        <ul class="dropdown-menu">
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-group-summary">
                                    {{ trans('vendorManagement.vendorGroupSummary') }}
                                </button>
                            </li>
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-category-summary">
                                    {{ trans('vendorManagement.vendorCategorySummary') }}
                                </button>
                            </li>
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-work-category-summary">
                                    {{ trans('vendorManagement.vendorWorkCategorySummary') }}
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <button type="button" class="btn btn-block btn-primary btn-mg header-btn" data-action="view-scores">
                            {{ trans('vendorManagement.scores') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-block btn-primary btn-mg header-btn" data-action="view-scores-with-sub-work-categories">
                            {{ trans('vendorManagement.categories') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.watchList') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.generic_table_modal', [
    'modalId'    => 'vendorProfileRemarksModal',
    'title'      => trans('general.remarks'),
    'tableId'    => 'vendorProfileRemarksTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-group-summary-modal',
    'title'            => trans('vendorManagement.vendorGroupSummary'),
    'tableId'          => 'vendor-group-summary-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-category-summary-modal',
    'title'            => trans('vendorManagement.vendorCategorySummary'),
    'tableId'          => 'vendor-category-summary-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-work-category-summary-modal',
    'title'            => trans('vendorManagement.vendorWorkCategorySummary'),
    'tableId'          => 'vendor-work-category-summary-table',
    'modalDialogClass' => 'modal-xl',
])
<div class="modal fade" id="scores-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.scores') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="scores-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-action="export-scores"><i class="fa fa-download"></i> {{ trans('general.download') }}</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="scores-with-sub-work-categories-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.categories') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="scores-with-sub-work-categories-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-action="export-scores-with-sub-work-categories"><i class="fa fa-download"></i> {{ trans('general.download') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            <?php $canViewVendorProfile = $currentUser->canViewVendorProfile(); ?>
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.watchList.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", width: 320, hozAlign:'left', headerSort:true, headerFilter: true, frozen:true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:view']+'">'+cell.getData()['name']+'</a>';
                        @else
                            return cell.getData()['name'];
                        @endif
                    }},
                    {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category_name", width: 200, hozAlign:'left', headerSort:false, headerFilter: true, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions) }} },
                    {title:"{{ trans('contractGroupCategories.vendorCategories') }}", field:"vendor_category_name", width: 280, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategories') }}", field:"vendor_work_category_name", width: 280, hozAlign:"left", headerFilter: true, headerSort:false},
                    {title:"{{ trans('vendorManagement.rating') }}", field:"rating", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.entryDate') }}", field:"entry_date", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.releaseDate') }}", field:"release_date", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.daysInWatchList') }}", field:"days_in_watch_list", width: 200, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.daysToRelease') }}", field:"days_to_release", width: 200, cssClass:"text-center text-middle", headerSort:false, formatter: function(cell){
                        if(!cell.getData()['days_to_release_passed']) return cell.getData()['days_to_release'];
                        return '<span class="text-danger">'+cell.getData()['days_to_release']+'</span>';
                    }},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                show: function(cell){
                                    return cell.getData()['can_edit'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                },
                            },
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.remarks") }}', 'data-action': 'showVendorProfileRemarks' },
                                rowAttributes: {'data-url': 'route:remarks' },
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'far fa-comment-dots'}
                                }
                            }
                        ]
                    }}
                ],
            });

            $(document).on('click', '[data-action="showVendorProfileRemarks"]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $('#vendorProfileRemarksModal').data('url', url);
                $('#vendorProfileRemarksModal').modal('show');
            });

            $(document).on('shown.bs.modal', '#vendorProfileRemarksModal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                var vendorProfileRemarksTable = new Tabulator('#vendorProfileRemarksTable', {
                    height:400,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: url,
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.remarks') }}", field:"content", minWidth: 300, hozAlign:"left", headerSort:false,formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: function(rowData){
                                return '<div class="well">'
                                +'<p style="white-space: pre-wrap;">'+rowData['content']+'</p>'
                                +'<br />'
                                +'<p style="color:#4d8af0">'+rowData['created_by']+' &nbsp;&nbsp;&nbsp;&nbsp; '+rowData['created_at']+'</p>'
                                +'</div>';
                            }
                        }},
                    ],
                });
            });

            var contractGroupCategorySummaryTable = new Tabulator('#vendor-group-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-group-summary]').on('click', function(){
                contractGroupCategorySummaryTable.setData("{{ route('vendorManagement.watchList.summary.contractGroupCategories') }}");
                $('#vendor-group-summary-modal').modal('show');
            });

            var vendorCategorySummaryTable = new Tabulator('#vendor-category-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-category-summary]').on('click', function(){
                vendorCategorySummaryTable.setData("{{ route('vendorManagement.watchList.summary.vendorCategories') }}");
                $('#vendor-category-summary-modal').modal('show');
            });

            var vendorWorkCategorySummaryTable = new Tabulator('#vendor-work-category-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var arr = cell.getData()['contract_group_categories'];
                        var output = [];
                        for(var i in arr){
                            output.push('<span class="label label-primary text-white">'+arr[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendor_categories'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-work-category-summary]').on('click', function(){
                vendorWorkCategorySummaryTable.setData("{{ route('vendorManagement.watchList.summary.vendorWorkCategories') }}");
                $('#vendor-work-category-summary-modal').modal('show');
            });

            var scoresTable = new Tabulator('#scores-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:false, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:vendor_profile']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width:200, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_work_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]}
                ],
            });

            $('[data-action=view-scores]').on('click', function(){
                scoresTable.setData("{{ route('vendorManagement.watchList.scores.list') }}");
                $('#scores-modal').modal('show');
            });

            var scoresWithSubWorkCategoriesTable = new Tabulator('#scores-with-sub-work-categories-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:false, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:vendor_profile']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width:200, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_work_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategories') }}", field:"vendor_sub_work_categories", width:250, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                ],
            });

            $('[data-action=view-scores-with-sub-work-categories]').on('click', function() {
                scoresWithSubWorkCategoriesTable.setData("{{ route('vendorManagement.watchList.scores.subWorkCategories.list') }}");
                $('#scores-with-sub-work-categories-modal').modal('show');
            });

            $('[data-action=export-scores]').on('click', function(){
                var filters = scoresTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorManagement.watchList.scores.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));
                    }
                }

                if(parameters.length){
                    url += '?'+parameters.join('&');
                }

                window.open(url, '_blank');
            });

            $('[data-action=export-scores-with-sub-work-categories]').on('click', function(){
                var filters = scoresWithSubWorkCategoriesTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorManagement.watchList.scores.subWorkCategories.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));
                    }
                }

                if(parameters.length){
                    url += '?'+parameters.join('&');
                }

                window.open(url, '_blank');
            });
        });
    </script>
@endsection