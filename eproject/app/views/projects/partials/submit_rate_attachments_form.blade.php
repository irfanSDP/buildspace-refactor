<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-md-10 col-lg-10">
                            <h1 class="txt-color-blueDark">Attachment(s)</h1>
                        </div>
                    </div>

                    <hr class="simple">

                    @if ( ! $tenderClosed )
                        {{ Form::open(array('route' => array('projects.submitTender.saveAttachments', $project->id, $tender->id), 'class' => 'smart-form', 'files' => true)) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">Attachments :</label>

                                        <div class="input input-file {{{ $errors->has('uploaded_files') ? 'state-error' : null }}}">
                                            @include('file_uploads.partials.upload_file_modal')
                                        </div>

                                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>

                            <footer>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-paperclip"></i> Save Attachment(s)
                                </button>
                            </footer>
                        {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>