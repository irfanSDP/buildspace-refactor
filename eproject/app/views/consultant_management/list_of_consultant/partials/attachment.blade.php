@if(Confide::user()->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>

<div class="modal fade" id="locUploadAttachmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['id' => 'loc_attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true]) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', ['id' => 'loc_attachment-upload'])
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="loc_attachment_submit-btn"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>
@else

@include('templates.generic_table_modal', [
    'modalId'    => 'locAttachmentModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'locAttachmentTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@endif