@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.calling.rfp.index', $vendorCategoryRfp->vendorCategory->name, [$vendorCategoryRfp->id]) }}</li>
        <li>{{{ trans('general.questionnaires') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tasks"></i> {{ $vendorCategoryRfp->vendorCategory->name }} {{{ trans('general.questionnaires') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-fw fa-tasks"></i> {{{ $company->name }}} @if(!isset($consultantQuestionnaire) or $consultantQuestionnaire->status == PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire::STATUS_UNPUBLISHED) <span class="label bg-color-red">{{{ trans('general.unpublish') }}}</span> @else <span class="label bg-color-green">{{{ trans('general.publish') }}}</span> @endif</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <ul id="rfp-questionnaire-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#rfp-questionnaires-tab-rfp" data-toggle="tab" id="rfp-tab">{{{ $vendorCategoryRfp->vendorCategory->name }}} {{{ trans('general.questionnaires') }}}</a>
                        </li>
                        <li>
                            <a href="#rfp-questionnaires-tab-general" data-toggle="tab" id="general-tab">{{{ trans('general.generalQuestionnaires') }}}</a>
                        </li>
                        <li>
                            <a href="#rfp-questionnaires-tab-replies" data-toggle="tab" id="replies-tab">{{{ trans('general.replies') }}}</a>
                        </li>
                    </ul>
                    <div id="consultant-management-questionnaire-tab-content" class="tab-content">
                        <div class="tab-pane fade in active" id="rfp-questionnaires-tab-rfp">
                            <div class="row" style="padding-right:4px;">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <a href="{{ route('consultant.management.consultant.questionnaire.rfp.create', [$vendorCategoryRfp->id, $company->id]) }}" class="btn btn-info btn-md pull-right header-btn">
                                        <i class="fa fa-plus"></i> {{{trans('general.newQuestionnaire')}}}
                                    </a>
                                </div>
                            </div>
                            <div class="row" style="padding-top:8px;">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div id="rfp_questionnaires-table"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade in" id="rfp-questionnaires-tab-general">
                            <div id="general_questionnaires-table"></div>
                        </div>
                        <div class="tab-pane fade in" id="rfp-questionnaires-tab-replies">
                            <div class="row" style="padding-right:4px;">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <button type="button" id="replies_reload-btn" class="btn btn-primary btn-md pull-right header-btn">
                                        <i class="fa fa-sync"></i> Reload
                                    </button>
                                </div>
                            </div>
                            <div class="row" style="padding-top:8px;">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div id="consultant_replies-table"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{ Form::open(['route' => ['consultant.management.consultant.questionnaire.publish', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <footer>
                        {{ Form::hidden('cid', $company->id) }}
                        @if(!isset($consultantQuestionnaire) or $consultantQuestionnaire->status == PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire::STATUS_UNPUBLISHED)
                        {{ Form::button('<i class="fa fa-upload"></i> '.trans('general.publish'), ['type' => 'submit', 'class' => 'btn btn-success'] )  }}
                        @else
                        {{ Form::button('<i class="fa fa-eye-slash"></i> '.trans('general.unpublish'), ['type' => 'submit', 'class' => 'btn btn-danger'] )  }}
                        @endif
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'consultantAttachmentModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'consultant_attachment-table',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    mandatory:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.required) ? '<i class="fas fa-lg fa-fw fa-check-circle text-success"></i>' : '<i class="fas fa-lg fa-fw fa-times-circle text-danger"></i>';
        return this.emptyToSpace(str);
    },
    exclude:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.exclude == 'yes') ? "{{{trans('general.yes')}}}" : "{{{trans('general.no')}}}";
        return this.emptyToSpace('<i class="fa fa-sm fa-edit"></i> '+str);
    },
    textarea:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<strong>{{{ trans('general.question') }}}</strong><div class="well">' +this.emptyToSpace(obj.question)+ '</div>';
        if(parseInt(obj.type) != {{{ PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY }}}){
            str += '<br/>'
            + '<strong>{{{ trans('general.replies') }}}</strong><div class="well">';
            if(!obj.replies.length){
                str += '-';
            }else{
                $.each(obj.replies, function( index, value ) {
                    str += (value.length) ? '<p>'+value+'</p>' : '-';
                });
            }
            str += '</div>';
        }
        
        return this.emptyToSpace(str);
    },
    attachmentDownloadButton: function(cell, formatterParams, onRendered) {
        var obj = cell.getRow().getData();
        if(obj.type=='file'){
            var btn = document.createElement('a');
            btn.dataset.toggle = 'tooltip';
            btn.className = 'btn btn-xs btn-primary';
            btn.innerHTML = '<i class="fas fa-download"></i>';
            btn.style['margin-right'] = '5px';
            btn.href = obj['route:download'];

            return btn;
        }
    },
    attachmentTitle: function(cell, formatterParams, onRendered){
        var obj = cell.getRow().getData();
        if(obj.type=='folder'){
            return this.emptyToSpace('&nbsp;<strong>'+obj.title+'</strong>');
        }else{
            return this.emptyToSpace(obj.title);
        }
    }
});

$(document).ready(function () {
    var generalQuestionnaireTbl = new Tabulator('#general_questionnaires-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.questionnaire.general.ajax.list', [$vendorCategoryRfp->id, $company->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.question') }}", field:"question", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                show:function(cell){
                    cell.getElement().style.whiteSpace = "pre-wrap";
                    return cell.getData().hasOwnProperty('id');
                },
                tag: 'a',
                attributes: {},
                rowAttributes: {href:'route:show'},
                innerHtml: function(rowData){
                    return rowData.question;
                }
            }},
            {title:"Mandatory", field:"required", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
            {title:"{{ trans('general.type') }}", field:"type_txt", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
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

            $.post(item['route:exclude'], params)
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

    var rfpQuestionnaireTbl = new Tabulator('#rfp_questionnaires-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.questionnaire.rfp.ajax.list', [$vendorCategoryRfp->id, $company->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.question') }}", field:"question", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                show:function(cell){
                    cell.getElement().style.whiteSpace = "pre-wrap";
                    return cell.getData().hasOwnProperty('id');
                },
                tag: 'a',
                attributes: {},
                rowAttributes: {href:'route:show'},
                innerHtml: function(rowData){
                    return rowData.question;
                }
            }},
            {title:"Mandatory", field:"required", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
            {title:"{{ trans('general.type') }}", field:"type_txt", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.createdAt') }}", field:"created_at", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    var consultantRepliesTbl = new Tabulator('#consultant_replies-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.questionnaire.rfp.replies', [$vendorCategoryRfp->id, $company->id]) }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.question') }}", field:"question", minWidth: 300, hozAlign:"left", headerSort:false, formatter:'textarea'},
            {title:"Mandatory", field:"required", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
            {title:"{{ trans('general.attachments') }}", field:"attachment_count", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(rowData.with_attachment){
                            return '<button type="button" class="btn btn-xs btn-info"><i class="fa fa-sm fa-paperclip"></i> ('+rowData.attachment_count+')</button>';
                        }
                        return '';
                    }
                }]
            }, cellClick:function(e, cell){
                var data = cell.getRow().getData();
                if(data.with_attachment){
                    var table = Tabulator.prototype.findTable("#consultant_attachment-table")[0];
                    if(!table){
                        table = new Tabulator('#consultant_attachment-table', {
                            height:420,
                            columns: [
                                {title:"{{ trans('general.no') }}", width:20, cssClass:"text-center", headerSort:false, formatter:'rownum'},
                                { title:"{{ trans('general.attachments') }}", field: 'title', cssClass:"text-left", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                                    return '<label class="text-success" style="font-size:14px;"><i class="fa-lg far fa-file"></i></label>&nbsp;&nbsp;' + cell.getValue();
                                }},
                                { title:"{{ trans('general.download') }}", width: 92, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: 'attachmentDownloadButton' },
                            ],
                            layout:"fitColumns",
                            ajaxURL: data['route:attachment-list'],
                            ajaxConfig: "GET",
                            ajaxParams: {id:data.record_id, type:data.record_type},
                            placeholder:"{{ trans('general.noRecordsFound') }}"
                        });
                    }else{
                        table.setData(data['route:attachment-list'], {id:data.record_id, type:data.record_type});
                    }

                    $('#consultantAttachmentModal').modal('show');
                }
            }},
            {title:"{{ trans('forms.submittedAt') }}", field:"submitted_date", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    $('#replies_reload-btn').on('click', function(e){
        e.preventDefault();
        consultantRepliesTbl.setData();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.target // newly activated tab
        e.relatedTarget // previous active tab
        if(e.target.id=='general-tab'){
            generalQuestionnaireTbl.redraw(true);
        }else if(e.target.id=='replies-tab'){
            consultantRepliesTbl.redraw(true);
        };
    })
});
</script>
@endsection