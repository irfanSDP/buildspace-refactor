<div class="modal fade" id="editUploadedFileModal">
    <div class="modal-dialog">
        <div class="modal-content" style="width:510px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('documentManagementFolders.editDocument') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            {{ Form::open(array('route' => array('tenderDocument.uploadUpdate', $project->id) , 'id' => 'editDocumentForm')) }}
            <div class="modal-body no-padding">
                <div class="smart-form form-inline">
                    <fieldset>
                        <section>
                            <label class="label">{{ trans('documentManagementFolders.filename') }}:</label>

                            <div class="form-group">
                                <label class="input" style="width:380px;">
                                    {{ Form::text('filename', Input::old('filename'), array('required' => 'required', 'style' => 'width:350px', 'maxlength' => 200, 'id' => 'editDocumentForm-filename', 'class' => 'form-control', 'placeholder' => trans('documentManagementFolders.filename') )) }}
                                </label>
                            </div>
                            <div class="form-group">
                                {{ HTML::image("img/default-file.png","", array('id' => 'editDocumentForm-thumbnail', 'height'=>64, 'width'=>84)) }}
                            </div>
                        </section>
                        <section>
                            <label class="label">{{ trans('documentManagementFolders.description') }}:</label>
                            <label class="textarea">
                                {{ Form::textarea('description', Input::old('description'), array('id' => 'editDocumentForm-description', 'placeholder' => trans('documentManagementFolders.description'), 'rows' => 3)) }}
                            </label>
                            {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                        </section>
                        <section id="template_tender_document_file_roles_section" style="display:none;">
                            <label class="label">{{ trans('documentManagementFolders.readOnlyDownloadFormatForRoles') }}:</label>
                            <label class="fill-horizontal {{{ $errors->has('contract_group_id[]') ? 'state-error' : null }}}">
                                {{ Form::select('contract_group_id[]', $contractGroups, Input::old('contract_group_id[]'), ['id' => 'editDocumentForm-contract_group', 'multiple'=>true, 'class' => 'select2 fill-horizontal'])}}
                            </label>
                            {{ $errors->first('contract_group_id[]', '<em class="invalid">:message</em>') }}
                        </section>
                        <section>
                            <label class="label">{{ trans('documentManagementFolders.isItRevision') }}
                                ?:</label>
                            <label class="select">
                                <select style="width:100%;" name="revision_to" id="editDocumentForm-revisionTo">
                                    <option></option>
                                </select>
                            </label>
                        </section>
                        <section>
                            <label class="label">{{ trans('documentManagementFolders.dateIssued') }}:</label>
                            <label class="text-info" id="editDocumentForm-date_issued"></label>
                            <label class="text-info" id="editDocumentForm-issued_by"></label>
                        </section>
                    </fieldset>
                    <input name="id" type="hidden" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('files.cancel') }}</button>
                {{ Form::submit(trans('documentManagementFolders.save'), array('class' => 'btn btn-primary')) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>