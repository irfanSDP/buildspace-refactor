<fieldset class="border-top {{{ ($response->sequence % 2 == 0) ?:'bg-grey-e' }}}" id="response-{{{ $response->id }}}" style="padding-top: 5px;">
    <div data-action="expandToggle" data-target="response-{{{ $response->id }}}" class="text-right">
        <button type="button" class="btn btn-xs btn-warning">&nbsp;<i class="fa fa-minus"></i>&nbsp;</button>
    </div>
    <div data-type="expandable" data-id="response-{{{ $response->id }}}" data-default="hide">
        <section>
            <label class="label">{{ trans('architectInstructions.subject') }}:</label>
            <strong>
                {{{ $response->subject }}}
            </strong>
        </section>
        <section>
            <label class="label">{{ trans('architectInstructions.response') }}:</label>
            {{{ $response->content }}}
        </section>
        @if ( ! $response->attachments->isEmpty() )
            <section>
                <strong>{{ trans('general.attachments') }}:</strong><br>

                @include('file_uploads.partials.uploaded_file_show_only', ['files' => $response->attachments, 'projectId' => $response->project_id])
            </section>
        @endif
        <section>
            <div class="row">
            <div class="col col-md-6">
                <label class="label">{{ trans('forms.submittedBy') }}:</label>
                <span class="text-success">
                    {{{ $response->createdBy->name }}}
                </span>
            </div>
            <div class="col col-md-6"></div>
                <label class="label">{{ trans('forms.submittedAt') }}:</label>
                <span class="text-success">
                    {{{ $project->getProjectTimeZoneTime($response->updated_at) }}}
                </span>
            </div>
        </section>
    </div>
</fieldset>