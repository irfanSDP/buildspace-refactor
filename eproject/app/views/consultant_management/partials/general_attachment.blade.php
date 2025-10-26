@if($consultantManagementContract->editableByUser(Confide::user()))
<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['id' => 'general_attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true]) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', ['id' => 'general_attachment-upload'])
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>
@else

@include('templates.generic_table_modal', [
    'modalId'    => 'generalAttachmentModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'generalAttachmentTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@endif