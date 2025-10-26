<div class="modal" id="submitTender-{{{ $tenderer->id }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    Tender Rates Attachment(s)
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span>
                </button>
            </div>

            <div class="modal-body">
                @if ( isset($companyName) )
                    <p>
                        <strong>Company Name:</strong><br>
                        {{{ $companyName }}}
                    </p>
                @endif

                <p>
                    <strong>Attachment(s):</strong><br>

                    @include('file_uploads.partials.uploaded_file_show_only', ['files' => $attachments, 'projectId' => $project->id])
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>