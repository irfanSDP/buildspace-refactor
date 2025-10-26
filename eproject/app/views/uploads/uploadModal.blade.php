<!--
Usage:
In your element, include the following attributes:
1. data-action=upload-item-attachments
2. data-do-upload: route for saving the uploads to the object.
3. data-get-uploads: route for obtaining the list of uploaded files.
    Returns an array where each element is in the format:
        [
            imgSrc
            deleteRoute
            createdAt
            size
        ]

Events:
1. uploadAttachmentModal.done
2. uploadAttachmentModal.fail
-->
<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>
<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(array('id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal')
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
    function addRowToUploadModal(fileAttributes){
        var clone = $('[data-type=template] tr.template-download').clone();
        var target = $('#uploadFileTable tbody.files');
        $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
        $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
        $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
        $(clone).find("a[data-category=link]").html(fileAttributes['filename']);
        $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
        $(clone).find("[data-category=size]").html(fileAttributes['size']);
        $(clone).find("button[data-action=delete]").prop('data-do-upload', fileAttributes['deleteRoute']);
        $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);
        target.append(clone);
    }

    $(document).on('click', '[data-action=upload-item-attachments]', function(){
        var target = $('#uploadFileTable tbody.files').empty();
        var data = $.get($(this).data('get-uploads'), function(data){
            for(var i in data){
                addRowToUploadModal({
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

        // $('[data-action=submit-attachments]').data('id', $(this).data('id'));
        // $('[data-action=submit-attachments]').data('get-uploads-count', $(this).data('get-uploads-count'));
        $('#uploadAttachmentModal').modal('show');
        $('#attachment-upload-form').prop('action',$(this).data('do-upload'));

    });
    $(document).on('click', '[data-action=submit-attachments]', function(){
        // var rowId                     = $(this).data('id');
        // var getUploadsCountUrl = $(this).data('get-uploads-count');
        var uploadedFilesInput        = [];

        $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
            uploadedFilesInput.push($(this).val());
        });

        app_progressBar.show();

        $.post($('form#attachment-upload-form').prop('action'),{
            _token: _csrf_token,
            uploaded_files: uploadedFilesInput
        })
        .done(function(data){
            if(data.success){
                $('#uploadAttachmentModal').modal('hide');
                app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
            }

            $("#uploadAttachmentModal").trigger("uploadAttachmentModal.done", {response: data});

        })
        .fail(function(data){
            $("#uploadAttachmentModal").trigger("uploadAttachmentModal.fail", {response: data});

            console.error('failed');
        });
    });
</script>