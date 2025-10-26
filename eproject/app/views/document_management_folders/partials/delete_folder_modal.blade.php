<div id="folderDeleteConfirm" class="modal fade">
    <div class="modal-dialog modal-dmf">
        <div class="modal-content">
            <div class="modal-body">
                {{ trans('documentManagementFolders.confirmDelete') }}
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="folderDeleteBtn">{{ trans('files.delete') }}</button>

                <button type="button" data-dismiss="modal"
                        class="btn btn-default">{{ trans('files.cancel') }}</button>
            </div>
        </div>
    </div>
</div>