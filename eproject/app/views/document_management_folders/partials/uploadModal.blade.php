<div class="modal fade" id="uploadDocumentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('documentManagementFolders.uploadFiles') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- The file upload form used as target for the file upload widget -->
                <div id="fileupload">
                    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                    <div class="row fileupload-buttonbar" style="margin: 0;">
                        <div>
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <span class="btn btn-sm btn-success fileinput-button">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>{{ trans('files.addFiles') }}</span>
                                <input type="file" name="file" multiple>
                            </span>
                            <button class="btn btn-sm btn-primary start">
                                <i class="glyphicon glyphicon-upload"></i>
                                <span>{{ trans('files.startUpload') }}</span>
                            </button>
                            <button class="btn btn-sm btn-warning cancel">
                                <i class="glyphicon glyphicon-ban-circle"></i>
                                <span>{{ trans('files.cancelUpload') }}</span>
                            </button>

                            <!-- The global file processing state -->
                            <span class="fileupload-process"></span>
                        </div>
                    </div>
                    <!-- The global progress state -->
                    <div class="fileupload-progress fade">
                        <!-- The global progress bar -->
                        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0"
                             aria-valuemax="100">
                            <div class="progress-bar progress-bar-success" style="width:0;"></div>
                        </div>
                        <!-- The extended global progress state -->
                        <div class="progress-extended">&nbsp;</div>
                    </div>
                    <!-- The table listing the files available for upload/download -->
                    <table role="presentation" class="table  table-bordered table-hover" id="uploadFileTable">
                        <thead>
                        <tr>
                            <th style="width:18%;">{{ trans('documentManagementFolders.preview') }}</th>
                            <th style="width:40%;">{{ trans('documentManagementFolders.filename') }}</th>
                            <th style="width:14%;">{{ trans('documentManagementFolders.size') }}</th>
                            <th style="width:28%;">{{ trans('documentManagementFolders.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody class="files" style="font-size:11px!important;"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>