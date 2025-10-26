<?php $modalId = isset($modalId) ? $modalId : 'technicalEvaluationAttachmentDownloadModal' ?>

<div class="modal scrollable-modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-type="formModal"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-paperclip"></i>
                    {{{ trans('technicalEvaluation.attachments') }}} 
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div class="well" data-id="company-name"></div>
                <div class="table-responsive">
                    <div data-id="attachments-table"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a data-action="download-all" class="btn btn-primary"><i class="fa fa-download"></i> {{{ trans('general.download') }}}</a>
            </div>
        </div>
    </div>
</div>

<script>
    new Tabulator('#{{ $modalId }} [data-id=attachments-table]', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('technicalEvaluation.item') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('technicalEvaluation.mandatory') }}", field:"compulsory", width: 150, cssClass:"text-center text-middle", headerSort:false, formatter:"tickCross", formatterParams:{
                tickElement:"<i class='fa fa-check'></i>",
                crossElement:"",
            }},
            {title:"{{ trans('technicalEvaluation.uploadedFile') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:function(cell){
                var rowData = cell.getData();
                if(rowData['route:download']){
                    return '<a href="'+rowData['route:download']+'">'+rowData['filename']+'</a>';
                }
                return '-';
            }}
        ],
    });
</script>