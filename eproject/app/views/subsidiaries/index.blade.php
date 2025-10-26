@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('subsidiaries.subsidiaries') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-cubes"></i> {{{ trans('subsidiaries.subsidiaries') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('subsidiaries.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('subsidiaries.addSubsidiary') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('subsidiaries.subsidiaries') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="subsidiaries-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
<script>
$(document).ready(function () {
    var expandEl = document.createElement("i");
    expandEl.classList.add("fas");
    expandEl.classList.add("fa-plus-square");
    expandEl.innerHTML = "&nbsp;";

    // Filter function
    function customHeaderTreeFilter(headerValue, rowValue, rowData, filterParams){
        // Operators
        var compare = {
            '=': function(a, b) { return a == b },
            '<': function(a, b) { return a < b },
            '<=': function(a, b) { return a <= b },
            '>': function(a, b) { return a > b },
            '>=': function(a, b) { return a >= b },
            '!=': function(a, b) { return a != b },
            'like': function(a, b) { return (a && b) ? a.toLowerCase().includes(b.toLowerCase()) : a.includes(b)}
        };
        if (rowData['_children'] && rowData['_children'].length > 0) {
            for (var i in rowData['_children']) {
                return compare[filterParams.type](rowData[filterParams.field], headerValue) || customHeaderTreeFilter(headerValue, rowValue, rowData['_children'][i], filterParams);
            }
        }

        return compare[filterParams.type](rowData[filterParams.field], headerValue);
    }

    var subsidiariesTable = new Tabulator('#subsidiaries-table', {
        fillHeight:true,
        dataTree:true,
        dataTreeExpandElement: expandEl,
        dataTreeChildIndent:15,
        dataTreeStartExpanded:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('subsidiaries.ajax.list') }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('subsidiaries.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}",
            headerFilterFunc:customHeaderTreeFilter, headerFilterFuncParams:{field:'name', type:"like"},
            formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: function(rowData){
                    if(!rowData.parent_id){
                        return '<strong>'+rowData.name+'</strong>';
                    }
                    return rowData.name;
                }
            }},
            {title:"{{ trans('subsidiaries.identifier') }}", field:"identifier", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", headerFilterFunc:customHeaderTreeFilter, headerFilterFuncParams:{field:'identifier', type:"like"}},
            {title:"{{ trans('companies.company') }}", field:"company_name", width: 280, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", headerFilterFunc:customHeaderTreeFilter, headerFilterFuncParams:{field:'company_name', type:"like"}},
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
                        innerHtml: function(rowData){
                            return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                        }
                    }
                ]
            }}
        ],
    });
});
</script>
@endsection