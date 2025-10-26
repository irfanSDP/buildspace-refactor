@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-folder-open"></i> RFP Documents
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div id="rfp_documents-table"></div>
                    <div class="row pe-4">
                        <div class="col col-xs-12 col-md-12 col-lg-12">
                            <div class="pull-right" id="document_upload-container">
                                <button type="button" class="btn btn-success" id="document_upload-btn">
                                    <i class="fas fa-upload fa-md"></i> {{ trans('forms.upload') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadDocumentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('documentManagementFolders.documents') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['id' => 'document-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true]) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', ['id' => 'rfp_document-upload'])
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-documents"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="remarkInputModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-pencil-alt"></i> {{trans('general.remarks')}}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body" style="padding:4px;">
                {{ Form::open(['route' => ['consultant.management.rfp.documents.remarks.store', $vendorCategoryRfp->id], 'id' => 'document_remarks-form', 'class' => 'smart-form', 'method' => 'post']) }}
                    <section>
                        <label class="label" for="remarks-input"></label>
                        <label class="textarea ">
                            <textarea rows="1" autofocus="autofocus" name="remarks" id="remarks-input" cols="50"></textarea>
                        </label>
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="remarks-save-button"><i class="fa fa-save"></i> {{trans('forms.save')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
var documentTbl;

var validateInputFormatter = function(cell, formatterParams, onRendered){
    var rowData = cell.getRow().getData();
    return app_tabulator_utilities.variableHtmlFormatter(cell, formatterParams, onRendered);
};

$(document).ready(function () {
    documentTbl = new Tabulator('#rfp_documents-table', {
        placeholder: "{{ trans('general.noRecordsFound') }}",
        fillHeight:true,
        ajaxURL: "{{ route('consultant.management.rfp.documents.ajax.list', [$vendorCategoryRfp->id]) }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        responsiveLayout:'collapse',
        columns:[
            {title:"&nbsp;", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                var data = cell.getData();
                return '<label class="text-success" style="font-size:14px;"><i class="fa-lg far fa-file"></i></label>&nbsp;&nbsp;' + cell.getValue();
            }},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width: 380, hozAlign:"left", headerSort:false, formatter:validateInputFormatter,
                formatterParams: {
                    tag: 'div',
                    rowAttributes: {'data-id': 'id', },
                    attributes: {'class': 'fill document_remarks', 'data-type': 'remarks_view', 'data-tooltip': 'data-tooltip', 'title': "{{ trans('forms.remarks') }}", 'data-placement': 'left', 'data-action': 'remark_input_toggle'},
                    innerHtml: function(rowData){
                        var defaultTxt = 'Click to enter remarks';
                        return rowData.remarks.length ? '<div class="well" style="white-space: pre-wrap;">'+rowData.remarks+'</div>' : defaultTxt;
                    }
                }
            },
            {title:"{{ trans('general.type') }}", field:"extension", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.actions') }}", field:"id", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var content = '<a href="'+rowData['route:download']+'" class="btn btn-xs btn-primary" title="{{{ trans("general.download") }}}"><i class="fa fa-download"></i></a>';
                        content +='&nbsp;<button class="btn btn-xs btn-danger" data-id="'+rowData.id+'" onclick="documentDelete(\''+rowData['route:delete']+'\')"><i class="fa fa-trash"></i></button>';
                        return content;
                    }
                }]
            }}
        ],
        renderComplete:function(){
            $('.document_remarks[data-action=remark_input_toggle]').on('click', function(e){
                e.preventDefault();

                var saveButton =$('#remarks-save-button');
                saveButton.removeData('id');
                saveButton.attr('data-id', $(this).data('id'));

                //populate the textarea with current remark
                var textView = $('[data-type=remarks_view][data-id='+$(this).data('id')+']');
                var currentRemarks = textView.text().trim();
                if(currentRemarks.toLowerCase() == 'click to enter remarks'){
                    currentRemarks = "";
                }
                var textArea = $('#remarks-input');
                textArea.val(currentRemarks);

                //show modal
                $('#remarkInputModal').modal('show');
            });
        }
    });
});

$("#document_upload-btn").on('click', function(e){
    $('#uploadDocumentModal').modal('show');
});

$("#remarks-save-button").on('click', function(e){
    e.preventDefault();

    var btn = $(this);
    btn.prop('disabled', true);

    var form = $("#document_remarks-form");
    var url = form.attr('action');

    $.ajax({
        type: 'POST',
        url: url,
        data: form.serialize()+ "&id=" + parseInt($(this).data('id')),
        beforeSend: function() {
            app_progressBar.show();
        }})
        .done(function(data){
            btn.prop('disabled', false);
            $('#remarkInputModal').modal('hide');
            if(documentTbl){
                documentTbl.setData();
            }
            app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
        })
        .fail(function(){
            btn.prop('disabled', false);
            $('#remarkInputModal').modal('hide');
            if(documentTbl){
                documentTbl.setData();
            }
            app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
        });
});

$("[data-action=submit-documents]").on('click', function(e){
    e.preventDefault();

    var uploadedFilesInput = [];

    $('form#document-upload-form input[name="uploaded_files[]"]').each(function(index){
        uploadedFilesInput.push($(this).val());
    });

    app_progressBar.show();

    $.post("{{route('consultant.management.rfp.documents.upload', [$vendorCategoryRfp->id])}}",{
        _token: "{{{csrf_token()}}}",
        uploaded_files: uploadedFilesInput
    })
    .done(function(data){
        if(data.success){
            $(".template-download").remove();
            $('#uploadDocumentModal').modal('hide');
            if(documentTbl){
                documentTbl.setData();
            }
        }
        app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
    })
    .fail(function(data){
        console.error('failed');
    });
});

function documentDelete(route){
    var r = confirm('Are you sure you want to delete this record?');
    if (r == true) {
        $.ajax({
            url: route,
            type: 'DELETE',
            data: {
                _token:'{{{csrf_token()}}}'
            },
            success: function(result) {
                if(result.success && documentTbl){
                    documentTbl.setData();//reload
                }
            }
        });
    }
}
</script>
@endsection