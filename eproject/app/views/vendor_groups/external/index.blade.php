@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('contractGroupCategories.externalVendorGroups') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.externalVendorGroups') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="pull-right ">
            <a href="{{ route('vendorGroups.external.create') }}" class="btn btn-primary btn-md header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </a>
            <button type="button" class="btn btn-success btn-md header-btn" id="btnVendorGroupExportModal" data-toggle="modal" data-target="#vendorGroupExportModal"><i class="fa fa-file-excel"></i> {{ trans('general.exportToExcel') }}</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('contractGroupCategories.externalVendorGroups') }}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    {{ Form::open(array('route' => array('vendorGroups.external.updateSettings'))) }}
                    @foreach($recordIds as $id)
                    <input hidden type="checkbox" name="hide-id[{{ $id }}]" value="{{ $id }}"/>
                    <input hidden type="checkbox" name="buildspace-access-id[{{ $id }}]" value="{{ $id }}"/>
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

<div class="modal" id="vendorGroupExportModal" tabindex="-1" role="dialog" aria-labelledby="vendorGroupExportModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header alert-success">
                <h4 class="modal-title">
                    <i class="fa fa-file-excel"></i> {{{ trans('general.exportToExcel') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            {{ Form::open(['route' => ['vendorGroups.external.export.excel'], 'target'=>"_blank"]) }}
            <div class="modal-body">
                <div id="vendor_groups_export-table"></div>
            </div>
            <div id="selected-ids"></div>
            <div class="modal-footer pull-right" style="border-top:none;">
                <button type="submit" id="vendor-group-export-excel" class="btn btn-primary"><i class="fa fa-file-export"></i> {{{trans('general.export')}}}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{{trans('forms.close')}}}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

<div class="modal" id="vendorGroupVendorListModal" tabindex="-1" role="dialog" aria-labelledby="vendorGroupVendorListModalLabel" aria-hidden="true">
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
                <div id="vendor_groups_vendor_list-table"></div>
            </div>
            <div class="modal-footer pull-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{{trans('forms.close')}}}</button>
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
                ajaxURL: "{{ route('vendorGroups.external.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                dataLoaded: function(data){
                    var selectedIds = {{ json_encode($hiddenIds) }};

                    for(var i in selectedIds){
                        $('input[type=checkbox][name="hide-id['+selectedIds[i]+']"]').prop('checked', true);
                    }
                    @if($currentUser->isSuperAdmin())
                    selectedIds = {{ json_encode($defaultBuildspaceAccessIds) }};

                    for(var i in selectedIds){
                        $('input[type=checkbox][name="buildspace-access-id['+selectedIds[i]+']"]').prop('checked', true);
                    }
                    @endif
                },
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show:function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        tag: 'a',
                        attributes: {},
                        rowAttributes: {href:'route:vendorCategories'},
                        innerHtml: function(rowData){
                            return rowData['name'];
                        }
                    }},
                    @if($currentUser->isSuperAdmin())
                    {title:"{{ trans('contractGroupCategories.defaultBuildSpaceAccess') }}", field: 'default_buildspace_access', width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                var checked = rowData['default_buildspace_access'] ? 'checked' : '';
                                return '<input type="checkbox" data-action="toggle-default-buildspace-access" data-id="'+rowData['id']+'" '+checked+'>';
                            }
                        }
                    }},
                    @endif
                    {title:"{{ trans('contractGroupCategories.target') }}", field:"target", width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendors') }}", field:"total_vendor", width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var btnElem = "";
                            if(rowData.hasOwnProperty('id') && parseInt(rowData.id) > 0 && rowData.hasOwnProperty('total_vendor') && parseInt(rowData.total_vendor) > 0){
                                btnElem = '<button type="button" class="btn btn-warning btn-xs pull-right btn-vendor-list" data-vendor-group-id="'+parseInt(rowData.id)+'" data-toggle="modal" data-target="#vendorGroupVendorListModal" title="{{{trans("general.view")}}}"><i class="fa fa-list"></i></button>';
                            }
                            return parseInt(rowData.total_vendor)+'&nbsp;'+btnElem;
                        }
                    }},
                    {title:"{{ trans('contractGroupCategories.vendorType') }}", field:"vendor_type", width: 200, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($vendorTypes) }}, headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.hidden') }}", field: 'hidden', width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                var checked = rowData['hidden'] ? 'checked' : '';
                                return '<input type="checkbox" data-action="hide" data-id="'+rowData['id']+'" '+checked+'>';
                            }
                        }
                    }, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($hiddenFilterOptions) }}},
                    {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [{
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                            rowAttributes: {'href': 'route:edit'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        }]
                    }}
                ]
            });

            $('#main-table').on('click', 'input[data-action=hide]', function(){
                $('input[type=checkbox][name="hide-id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });

            @if($currentUser->isSuperAdmin())
            $('#main-table').on('click', 'input[data-action=toggle-default-buildspace-access]', function(){
                $('input[type=checkbox][name="buildspace-access-id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });
            @endif

            var tabulatorSelectedIndexes = [];
            $('#vendorGroupExportModal').on('shown.bs.modal', function(e){
                new Tabulator('#vendor_groups_export-table', {
                    height:450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('vendorGroups.external.ajax.list') }}",
                    ajaxConfig: "GET",
                    paginationSize: 100,
                    pagination: "remote",
                    ajaxFiltering:true,
                    layout:"fitColumns",
                    selectable: true, 
                    selectablePersistence: true,
                    rowSelectionChanged:function(data, rows){
                        tabulatorSelectedIndexes = [];
                        $("input[type=hidden][id^='id_']").remove();
                        $.each(data, function (idx, obj) {
                            tabulatorSelectedIndexes.push(obj.id);
                            $('<input>', {
                                type: 'hidden',
                                id: 'id_'+obj.id,
                                name: 'ids[]',
                                value: obj.id
                            }).appendTo('#selected-ids');
                        })

                        $('#vendor-group-export-excel').prop('disabled', !(tabulatorSelectedIndexes.length));
                    },
                    columns:[
                        {formatter: "rowSelection", titleFormatter: "rowSelection", cssClass:"text-center text-middle", field: 'id', width: 12, 'align': 'center', headerSort:false},
                        {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                        {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                        {title:"{{ trans('contractGroupCategories.hidden') }}", field: 'hidden', width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: function(rowData){
                                if(rowData.hasOwnProperty('id')){
                                    var img = rowData['hidden'] ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i';
                                    return img;
                                }
                            }
                        }, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($hiddenFilterOptions) }}}
                    ]
                });
            });

            <?php $canViewVendorProfile = $currentUser->canViewVendorProfile(); ?>

            $('#vendorGroupVendorListModal').on('shown.bs.modal', function(e){
                var vgId = $(e.relatedTarget).data('vendor-group-id');
                if(parseInt(vgId)){
                    var url = '{{ route("vendorGroups.external.ajax.vendor.list", ":id") }}';
                    url = url.replace(':id', parseInt(vgId));
                    new Tabulator('#vendor_groups_vendor_list-table', {
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
        });
    </script>
@endsection