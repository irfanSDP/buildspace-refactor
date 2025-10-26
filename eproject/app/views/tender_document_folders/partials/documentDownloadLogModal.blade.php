<div id="tenderDocumentDownloadLogModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ trans('documentManagementFolders.downloadLogs') }}</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <table class="table table-hover" id="tenderDocumentDownloadLogTable">
                <thead>
                    <tr>
                        <th>{{ trans('documentManagementFolders.company') }}</th>
                        <th>{{ trans('documentManagementFolders.user') }}</th>
                        <th>{{ trans('documentManagementFolders.lastDownloaded') }}</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
      </div>
    </div>
</div>