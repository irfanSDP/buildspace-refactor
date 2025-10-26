@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-cogs"></i> {{{ trans('general.attachmentSettings') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-building"></i> {{{ $vendorCategoryRfp->vendorCategory->name }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <ul id="rfp-attachment-settings-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#rfp-attachment-settings-tab-general" data-toggle="tab" id="general-tab">{{{ trans('general.generalAttachments') }}}</a>
                        </li>
                        <li>
                            <a href="#rfp-attachment-settings-tab-rfp" data-toggle="tab" id="rfp-tab">{{{ $vendorCategoryRfp->vendorCategory->name }}} {{{ trans('general.attachments') }}}</a>
                        </li>
                    </ul>
                    <div id="consultant-management-subsidiaries-tab-content" class="tab-content">
                        <div class="tab-pane fade in active" id="rfp-attachment-settings-tab-general">
                            <div id="general_attachment-table"></div>
                        </div>
                        <div class="tab-pane fade in" id="rfp-attachment-settings-tab-rfp">
                            <div class="row pe-4 pb-4">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <a href="{{ route('consultant.management.rfp.attachment.settings.create', [$vendorCategoryRfp->id]) }}" class="btn btn-info btn-md pull-right header-btn">
                                        <i class="fa fa-plus"></i> {{{ trans('general.new') }}} {{{trans('general.attachments')}}}
                                    </a>
                                </div>
                            </div>
                            <div id="rfp_attachment-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    mandatory:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.mandatory) ? '<i class="fas fa-lg fa-fw fa-check-circle text-success"></i>' : '<i class="fas fa-lg fa-fw fa-times-circle text-danger"></i>';
        return this.emptyToSpace(str);
    },
    exclude:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.exclude == 'yes') ? "{{{trans('general.yes')}}}" : "{{{trans('general.no')}}}";
        return this.emptyToSpace('<i class="fa fa-sm fa-edit"></i> '+str);
    }
});

$(document).ready(function () {
    var generalAttachmentTbl = new Tabulator('#general_attachment-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.rfp.general.settings.ajax.list', $vendorCategoryRfp->id) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                show:function(cell){
                    cell.getElement().style.whiteSpace = "pre-wrap";
                    return cell.getData().hasOwnProperty('id');
                },
                innerHtml: function(rowData){
                    return rowData['title'];
                }
            }},
            {title:"Mandatory", field:"mandatory", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
            {title:"{{ trans('forms.exclude') }}", field:"exclude", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:'exclude', editor:"select", editorParams:{
                values:{
                    "yes":"{{{ trans('general.yes') }}}",
                    "no":"{{{ trans('general.no') }}}"
                }}
            }
        ],
        cellEdited:function(cell) {
            var row = cell.getRow();
            var item = row.getData();
            var field = cell.getField();
            var value = cell.getValue();
            var table = cell.getTable();

            table.modules.ajax.showLoader();
            
            var params = {
                id: parseInt(item.id),
                field: field,
                val: value,
                _token:'{{{csrf_token()}}}',
            };

            $.post(item['route:update'], params)
            .done(function(data){
                if(data.updated){
                    cell.getRow().update(data.item);
                    cell.getRow().reformat();
                }
                table.modules.ajax.hideLoader();
            })
            .fail(function(data){
                console.error('failed');
                table.modules.ajax.hideLoader();
            });
        }
    });

    var rfpAttachmentTbl = new Tabulator('#rfp_attachment-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.rfp.settings.ajax.list', $vendorCategoryRfp->id) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                show:function(cell){
                    cell.getElement().style.whiteSpace = "pre-wrap";
                    return cell.getData().hasOwnProperty('id');
                },
                tag: 'a',
                attributes: {},
                rowAttributes: {href:'route:show'},
                innerHtml: function(rowData){
                    return rowData['title'];
                }
            }},
            {title:"Mandatory", field:"mandatory", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
            {title:"{{ trans('general.createdAt') }}", field:"created_at", width: 140, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.target // newly activated tab
        e.relatedTarget // previous active tab
        if(e.target.id=='rfp-tab'){
            rfpAttachmentTbl.redraw(true);
        };
    })
});
</script>
@endsection