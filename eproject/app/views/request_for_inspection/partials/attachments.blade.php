@if($attachments->count() > 0)
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('requestForInspection.attachments') }}} :</label>
            @foreach($attachments as $attachment)
                <a title="{{ trans('documentManagementFolders.clickToDownload') }}" href="{{ route('moduleUploads.download', array($project->id, $attachment->file->id)) }}">
                    {{{ $attachment->file->filename }}}
                </a>
                <br/>
            @endforeach
        </section>
    </div>
@endif