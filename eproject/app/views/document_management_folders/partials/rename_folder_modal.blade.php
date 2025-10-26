<div class="modal fade" id="renameFolderModal">
    <div class="modal-dialog modal-dmf">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"
                    id="renameFolderLabel">{{ trans('documentManagementFolders.renameFolder') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            {{ Form::open(array('route' => array('projectDocument.renameFolder', $project->id) , 'id' => 'renameFolderForm')) }}
            <div class="modal-body">
                @include('document_management_folders.partials.folderForm')
            </div>
            <div class="modal-footer">
                {{ Form::submit(trans('documentManagementFolders.save'), array('class' => 'btn btn-primary')) }}

                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{ trans('files.cancel') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>