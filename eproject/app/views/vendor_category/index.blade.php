@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorGroups.external.index', trans('contractGroupCategories.externalVendorGroups'), array()) }}</li>
        <li>{{{ $contractGroupCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorCategories') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorCategories.create', array($contractGroupCategory->id)) }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ $contractGroupCategory->name }}} </h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="vendor-categories-table"></div>
                    {{ Form::open(array('route' => array('vendorCategories.hide', $contractGroupCategory->id))) }}
                    @foreach($recordIds as $id)
                    <input hidden type="checkbox" name="id[{{ $id }}]" value="{{ $id }}"/>
                    @endforeach
                    <footer class="pull-right">
                        <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="vendorCategoryVendorListModal" tabindex="-1" role="dialog" aria-labelledby="vendorCategoryVendorListModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header alert-warning">
                <h4 class="modal-title">
                    <i class="fa fa-users"></i> {{{ trans("vendorManagement.vendors") }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="vendor_categories_vendor_list-table"></div>
            </div>
            <div class="modal-footer pull-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{{trans('forms.close')}}}</button>
            </div>
        </div>
    </div>
</div>
@include('templates.generic_table_modal', array('modalId' => 'vendor-work-categories-summary-modal', 'title' => trans('vendorManagement.vendorWorkCategories'), 'tableId' => 'vendor-work-categories-summary-table', 'tablePadding' => true))
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var vendorCategoriesTable = new Tabulator('#vendor-categories-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorCategories.ajax.list', [$contractGroupCategory->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                dataLoaded: function(data){
                    var selectedIds = {{ json_encode($hiddenIds) }};

                    for(var i in selectedIds){
                        $('input[type=checkbox][name="id['+selectedIds[i]+']"]').prop('checked', true);
                    }
                },
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show:function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        tag: 'a',
                        attributes: {'href': 'javascript:void(0)', 'data-action': 'show-summary'},
                        rowAttributes: {'data-id':'id'},
                        innerHtml: function(rowData){
                            return rowData['name'];
                        }
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategories') }}", field:"vendor_work_categories", minWidth: 300, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", cssClass:"text-left text-middle", headerSort:false, formatter: function(cell){
                        var rowData = cell.getData();
                        var output = '<div class="well text-truncate" data-action="show-summary" data-id="'+rowData['id']+'">';
                        for(var i in rowData['vendor_work_categories']){
                            if(i < 3){
                                output += '<p>'+rowData['vendor_work_categories'][i]+'</p>';
                            }
                            else{
                                output += '<p>...</p>';
                                break;
                            }
                        }
                        output += '</div>';
                        return output;
                    }},
                    {title:"{{ trans('contractGroupCategories.target') }}", field:"target", width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendors') }}", field:"total_vendor", width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var btnElem = "";
                            if(rowData.hasOwnProperty('id') && parseInt(rowData.id) > 0 && rowData.hasOwnProperty('total_vendor') && parseInt(rowData.total_vendor) > 0){
                                btnElem = '<button type="button" class="btn btn-warning btn-xs pull-right btn-vendor-list" data-vendor-category-id="'+parseInt(rowData.id)+'" data-toggle="modal" data-target="#vendorCategoryVendorListModal" title="{{{trans("general.view")}}}"><i class="fa fa-list"></i></button>';
                            }
                            return parseInt(rowData.total_vendor)+'&nbsp;'+btnElem;
                        }
                    }},
                    {title:"{{ trans('contractGroupCategories.hidden') }}", field: 'hidden', width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                var checked = rowData['hidden'] ? 'checked' : '';
                                return '<input type="checkbox" data-action="hide" data-id="'+rowData['id']+'" '+checked+'>';
                            }
                        }
                    }, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($hiddenFilterOptions) }}},
                    {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-info', title: '{{ trans("vendorManagement.assignVendorWorkCategories") }}'},
                                rowAttributes: {'href': 'route:vendor_work_categories'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-exchange-alt'}
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#vendor-categories-table').on('click', 'input[data-action=hide]', function(){
                $('input[type=checkbox][name="id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });

            <?php $canViewVendorProfile = $currentUser->canViewVendorProfile(); ?>

            $('#vendorCategoryVendorListModal').on('shown.bs.modal', function(e){
                var vgId = $(e.relatedTarget).data('vendor-category-id');
                if(parseInt(vgId)){
                    var url = '{{ route('vendorCategories.ajax.vendor.list', [$contractGroupCategory->id, ":id"])  }}';
                    url = url.replace(':id', parseInt(vgId));
                    new Tabulator('#vendor_categories_vendor_list-table', {
                        height:450,
                        placeholder: "{{ trans('general.noRecordsFound') }}",
                        ajaxURL: url,
                        ajaxConfig: "GET",
                        paginationSize: 100,
                        pagination: "remote",
                        ajaxFiltering:true,
                        layout:"fitColumns",
                        columns:[
                            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width: 100, cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                            {title:"{{ trans('vendorProfile.vendor') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:function(cell){
                                @if($canViewVendorProfile)
                                    return '<a href="'+cell.getData()['route:show']+'">'+cell.getData()['name']+'</a>';
                                @else
                                    return cell.getData()['name'];
                                @endif
                            }},
                            {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:160, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false}
                        ]
                    });
                }
            });

            var vendorWorkCategoriesSummaryTable = new Tabulator('#vendor-work-categories-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                ]
            });

            $('#vendor-categories-table').on('click', '[data-action=show-summary]', function(){
                var row = vendorCategoriesTable.getRow($(this).data('id'));
                vendorWorkCategoriesSummaryTable.setData(row.getData()['route:vendor_work_category_summary']);
                $('#link-to-assign-page').prop('href', row.getData()['route:vendor_work_categories']);

                $('#vendor-work-categories-summary-modal').modal('show');
            });

            $('#vendor-work-categories-summary-modal .modal-footer').html('<a href="" id="link-to-assign-page" class="btn btn-md btn-info"><i class="fa fa-exchange-alt"></i> {{ trans("forms.update") }}</a>');
        });
    </script>
@endsection