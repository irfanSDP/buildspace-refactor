@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('contractGroupCategories.vendorWorkCategories') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorWorkCategories') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorWorkCategories.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('contractGroupCategories.vendorWorkCategories') }}} </h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="vendor-work-categories-table"></div>
                    {{ Form::open(array('route' => array('vendorWorkCategories.hide'))) }}
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
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var vendorWorkCategoriesTable = new Tabulator('#vendor-work-categories-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorWorkCategories.ajax.list') }}",
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
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, hozAlign:"center", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", width: 300, hozAlign:"center", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_categories", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var c = '<div class="well">';
                            $.each(rowData.vendor_categories, function( key, value ) {
                                c+='<p>'+value+'</p>';
                            });
                            c+='</div>';
                            return c;
                        }
                    }},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show:function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        tag: 'a',
                        attributes: {},
                        rowAttributes: {href:'route:vendorWorkSubcategories'},
                        innerHtml: function(rowData){
                            return rowData['name'];
                        }
                    }},
                    {title:"{{ trans('tenders.subCategory') }}", field:"subcategories", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var c = '<div class="well">';
                            $.each(rowData.subcategories, function( key, value ) {
                                c+='<p>'+value+'</p>';
                            });
                            c+='</div>';
                            return c;
                        }
                    }},
                    {title:"{{ trans('tenders.subCategory') }}", field:"total_subcategories", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.hidden') }}", field: 'hidden', width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
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
                                attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.edit") }}'},
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
                                attributes: {class:'btn btn-xs btn-info', title: '{{ trans("vendorManagement.assignVendorCategories") }}'},
                                rowAttributes: {'href': 'route:vendor_categories'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-exchange-alt'}
                                }
                            }
                        ]
                    }}
                ],
            });

            $('#vendor-work-categories-table').on('click', 'input[data-action=hide]', function(){
                $('input[type=checkbox][name="id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });
        });
    </script>
@endsection