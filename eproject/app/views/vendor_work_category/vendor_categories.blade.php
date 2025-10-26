@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorWorkCategories.index', trans('vendorManagement.vendorWorkCategories'), array()) }}</li>
        <li>{{{ $vendorWorkCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorCategories') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ $vendorWorkCategory->name }}} </h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    {{ Form::open(array('id' => 'assign-form', 'route' => array('vendorWorkCategories.vendorCategories.include', $vendorWorkCategory->id))) }}
                    <footer class="pull-right">
                        <a href="{{ route('vendorGroups.external.index') }}" class="btn btn-default">{{ trans('vendorManagement.viewVendorGroupList') }}</a>
                        <button class="btn btn-primary" data-action="submit"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.generic_table_modal', array('modalId' => 'vendor-work-categories-summary-modal', 'title' => trans('vendorManagement.vendorWorkCategories'), 'tableId' => 'vendor-work-categories-summary-table', 'tablePadding' => true))
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var selectedIds = {{ json_encode($includedIds) }};

            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorWorkCategories.vendorCategories.ajax.list', [$vendorWorkCategory->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                rowSelected: function(row){
                    var id = row.getData()['id'];
                    if(selectedIds.indexOf(id) === -1) selectedIds.push(id);
                },
                rowDeselected: function(row){
                    var id = row.getData()['id'];
                    if((idx = selectedIds.indexOf(id)) > -1) selectedIds.splice(idx, 1);
                },
                dataLoaded: function(data){
                    mainTable.selectRow(selectedIds);

                    selectedIds.forEach(function(id){
                        $('#main-table [data-action=include][data-id='+id+']').prop('checked', true);
                    });
                },
                cellClick:function(e, cell){
                    var selectTriggerFields = ['counter', 'name', 'code', 'included'];
                    if(selectTriggerFields.includes(cell.getField())){
                        cell.getRow().toggleSelect();
                        $('#main-table [data-action=include][data-id='+cell.getData()['id']+']').prop('checked', cell.getRow().isSelected());
                    }
                },
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
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
                    {title:"{{ trans('general.included') }}", field:"included", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", 'true':"{{ trans('general.yes') }}", 'false':"{{ trans('general.no') }}"}}, formatter:function(cell){
                        return '<input type="checkbox" data-action="include" data-id="'+cell.getData()['id']+'">';
                    }}
                ],
            });

            $('#assign-form').one('submit', function(e) {
                e.preventDefault();
                var idInput;
                selectedIds.forEach(function(id, index){
                    idInput = document.createElement('input');
                    idInput.setAttribute('type', 'hidden');
                    idInput.setAttribute('name', 'id[]');
                    idInput.setAttribute('value', id);
                    $('#assign-form').append(idInput);
                });
                $('#assign-form').submit();
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

            $('#main-table').on('click', '[data-action=show-summary]', function(){
                var row = mainTable.getRow($(this).data('id'));
                vendorWorkCategoriesSummaryTable.setData(row.getData()['route:vendor_work_category_summary']);

                $('#vendor-work-categories-summary-modal').modal('show');
            });
        });
    </script>
@endsection