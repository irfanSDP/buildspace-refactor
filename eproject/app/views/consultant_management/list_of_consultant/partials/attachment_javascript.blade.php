<script type="text/javascript">
    Tabulator.prototype.extendModule("format", "formatters", {
        generalAttachmentDownloadButton:function(cell, formatterParams){
            var obj = cell.getRow().getData();
            var downloadButton = document.createElement('a');
            downloadButton.dataset.toggle = 'tooltip';
            downloadButton.className = 'btn btn-xs btn-primary';
            downloadButton.innerHTML = '<i class="fas fa-download"></i>';
            downloadButton.style['margin-right'] = '5px';
            downloadButton.href = obj.download_url;
            downloadButton.download = obj.filename;
            return downloadButton;
        }
    });
    
    $(document).ready(function () {
    
        @if(Confide::user()->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
    
        $('#upload_loc_attachment-btn').on('click', function(e) {
            e.preventDefault();
    
            var attachmentsListUrl   = $(this).data('route-get-attachments-list');
            var attachmentsUpdateUrl = $(this).data('route-update-attachments');
            var attachmentsCountUrl  = $(this).data('route-get-attachments-count');
            var phaseId              = parseInt($(this).data('phase-id'));
    
            var target = $('#uploadFileTable tbody.files').empty();
            var data   = $.get(attachmentsListUrl, function(data) {
                for(var i in data){
                    generalAttachmentAddRowToUploadModal({
                        download_url: data[i]['download_url'],
                        filename: data[i]['filename'],
                        imgSrc: data[i]['imgSrc'],
                        id: data[i]['id'],
                        size: data[i]['size'],
                        deleteRoute: data[i]['deleteRoute'],
                        createdAt: data[i]['createdAt'],
                    });
                }
            });
    
            $('#loc_attachment_submit-btn').data('updated-attachment-count-url', $(this).data('route-get-attachments-count'));
            $('#locUploadAttachmentModal').modal('show');
            $('#loc_attachment-upload-form').prop('action', attachmentsUpdateUrl);
        });
    
        $(document).on('click', 'button[data-action="delete"]', function(e) {
            var url = $(this).data('route');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{csrf_token()}}'
                },
                success: function(ret) {
                    if(ret.success){
                        $.get(ret.count_url, {},function(resp) {
                            $(document).find(`[data-component="${resp.phase_id}_${resp.field}_count"]`).text(resp.attachmentCount);
                        });
                    }
                }
            });
        });
    
        $('#loc_attachment_submit-btn').on('click', function(){
            var updatedAttachmentCountUrl = $(this).data('updated-attachment-count-url');
            var uploadedFilesInput = [];
    
            $('form#loc_attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                uploadedFilesInput.push($(this).val());
            });
    
            app_progressBar.show();
    
            $.post($('form#loc_attachment-upload-form').prop('action'),{
                _token: _csrf_token,
                uploaded_files: uploadedFilesInput
            })
            .done(function(data){
                if(data.success){
                    $('#locUploadAttachmentModal').modal('hide');
    
                    $.get(updatedAttachmentCountUrl, {},function(resp) {
                        $(document).find(`[data-component="${resp.phase_id}_${resp.field}_count"]`).text(resp.attachmentCount);
                    });
    
                    app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                }
            })
            .fail(function(data){
                console.error('failed');
            });
        });
    
        @else
    
        $('#view_upload_loc_attachment-btn').on('click', function(e) {
            e.preventDefault();
    
            $('#locAttachmentModal').data('url', $(this).data('route-get-attachments-list'));
            $('#locAttachmentModal').modal('show');
        });
    
        $('#locAttachmentModal').on('shown.bs.modal', function(e) {
            e.preventDefault();
    
            var url = $(this).data('url');
    
            var generalAttachmentTable = new Tabulator('#locAttachmentTable', {
                height:360,
                pagination:"local",
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('general.download') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: 'generalAttachmentDownloadButton' },
                ],
                layout:"fitColumns",
                ajaxURL: url,
                movableColumns:true,
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false
            });
        });
        
        @endif
    });
    
    function generalAttachmentAddRowToUploadModal(fileAttributes){
        var clone = $('[data-type=template] tr.template-download').clone();
        var target = $('#uploadFileTable tbody.files');
    
        $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
        $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
        $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
        if(fileAttributes['imgSrc'] !== undefined && fileAttributes['imgSrc'].length){
            $(clone).find("span.preview img[data-category=img]").attr('src', fileAttributes['imgSrc']);
        }
        $(clone).find("p.name a[data-category=link]").html(fileAttributes['filename']);
        $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
        $(clone).find("[data-category=size]").html(fileAttributes['size']);
        $(clone).find("button[data-action=delete]").attr('data-route', fileAttributes['deleteRoute']);
        $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);
    
        target.append(clone);
    }
    
    </script>