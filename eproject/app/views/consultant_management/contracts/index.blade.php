@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('navigation/mainnav.developmentPlanMasterlist') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-table"></i> {{{ trans('navigation/mainnav.developmentPlanMasterlist') }}}
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 mb-4">
        @if($user->isSuperAdmin() or $user->isGroupAdmin())
        <a href="{{ route('consultant.management.contracts.contract.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('general.new') }}} {{{trans('general.developmentPlanning')}}}
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('navigation/mainnav.developmentPlanMasterlist') }}} </h2>
            </header>
            <div>
                <ul id="consultant-management-contracts-tabs" class="nav nav-pills">
                    <li class="nav-item active">
                        <a class="nav-link" href="#consultant-management-contracts-tab-contract" data-toggle="tab"><i class="fa fa-md fa-fw fa-table"></i> {{{trans('contracts.contracts')}}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#consultant-management-contracts-tab-rfp" data-toggle="tab"><i class="fa fa-md fa-fw fa-building"></i> RFP</a>
                    </li>
                </ul>
                <div id="consultant-management-contracts-tab-content" class="tab-content" style="padding-top:1rem!important;">
                    <div class="tab-pane fade in active" id="consultant-management-contracts-tab-contract">
                        <div id="contracts-table"></div>
                    </div>
                    <div class="tab-pane fade in" id="consultant-management-contracts-tab-rfp">
                        <div id="rfp-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    textarea:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<a href="'+obj["route:show"]+'" class="plain">'
            + '<div class="well">' +this.sanitizeHTML(obj.contract_title)+ '</div>'
            + '<p style="padding-top:4px;">'
            + '<span class="label label-success">'
            + this.sanitizeHTML(obj.contract_created_at)
            + '</span>'
            + '&nbsp;'
            + '<span class="label label-info">'
            + this.sanitizeHTML(obj.country) + ', ' + this.sanitizeHTML(obj.state)
            + '</span>'
            +'</p></a>';
        return this.emptyToSpace(str);
    }
});
$(document).ready(function () {
    var contractTable = new Tabulator('#contracts-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.contracts.ajax.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.referenceNo') }}", field:"reference_no", width: 180, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('projects.title') }}", field:"contract_title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
            {title:"Awarded RFP", field:"total_awarded_rfp", width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(parseInt(rowData.total_rfp) > 0){
                            return rowData.total_awarded_rfp +"/"+ rowData.total_rfp;
                        }

                        return 'No RFP';
                    }
                }]
            }},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(rowData.deletable){
                            return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                        }

                        return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                    }
                }]
            }}
        ]
    });

    var rfpTable = new Tabulator('#rfp-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.contracts.rfp.ajax.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.referenceNo') }}", field:"reference_no", width: 180, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('contracts.contracts') }}", field:"contract_title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
            {title:"RFP", field:"title", width: 320, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:show']+'" class="plain">'+rowData.title+'</a>';
                    }
                }]
            }},
            {title:"{{ trans('projects.status') }}", field: 'rfp_status', cssClass:"text-center text-middle", width: 140, headerSort:false, headerFilterPlaceholder: "{{ trans('general.filter') }}", editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($rfpStatuses) }}, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var txt;
                        switch(rowData.rfp_status){
                            case "{{ trans('general.awarded') }}":
                                txt = ' <b class="badge bg-color-green inbox-badge">'+rowData.rfp_status+'</b>';
                                break;
                            case "{{ trans('verifiers.approved') }}":
                                txt = '<b class="badge bg-color-purple inbox-badge">'+rowData.rfp_status+'</b>';
                                break;
                            case "{{ trans('general.callingRFP') }}":
                                txt = '<b class="badge bg-color-yellow inbox-badge">'+rowData.rfp_status+'</b>';
                                break;
                            default:
                                txt = rowData.rfp_status;
                        }
                        return txt;
                    }
                }]
            }}
        ]
    });
});
</script>
@endsection