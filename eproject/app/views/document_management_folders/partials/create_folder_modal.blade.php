<div class="modal fade" id="newFolderModal">
    <div class="modal-dialog modal-dmf">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"
                    id="newFolderLabel">{{ trans('documentManagementFolders.newFolder') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            {{ Form::open(array('route' => array('projectDocument.newFolder', $project->id) , 'id' => 'newFolderForm')) }}
            <div class="modal-body">
                @include('document_management_folders.partials.folderForm')
            </div>
            <div class="modal-footer">
                {{ Form::submit(trans('documentManagementFolders.save'), array('class' => 'btn btn-primary')) }}

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {{ trans('files.cancel') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>