<fieldset>
    <section>
        <strong>{{ trans('projects.project') }}:</strong><br>
        <label class="input">
            {{{ $ai->project->title }}}
        </label>
    </section>

    <section>
        <strong>{{ trans('architectInstructions.architectInstructionReference') }}:</strong><br>
        {{{ $ai->reference }}}
    </section>

    @if ( !$ai->attachedClauses->isEmpty() )
        <section>
            <strong>{{ trans('architectInstructions.clausesThatEmpower') }}:</strong><br>
            <div>
                @foreach ( $ai->attachedClauses as $clause )
                    @include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
                    <br/>
                    <br/>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <strong>{{ trans('architectInstructions.architectsInstruction') }}:</strong><br>
        {{{ $ai->instruction }}}
    </section>

    <section>
        <strong>{{ trans('architectInstructions.dateIssued') }}:</strong><br>
        <label class="input">
            <strong class="dateSubmitted"><i>{{{ $ai->project->getProjectTimeZoneTime($ai->created_at) }}}</i></strong> by {{{ $ai->createdBy->present()->byWhoAndRole($ai->project, $ai->created_at) }}}
        </label>
    </section>

    <section>
        <strong>{{ trans('architectInstructions.deadlineToComply') }}:</strong><br>
        {{{ $project->getProjectTimeZoneTime($ai->deadline_to_comply) ?? '-' }}}
    </section>

    @if(!$ai->requestsForInformation->isEmpty())
        <section>
            <strong>{{ trans('architectInstructions.requestsForInformation') }}:</strong><br>
            @foreach($ai->requestsForInformation as $request)
                <a href="{{ route('requestForInformation.show', array($project->id, $request->id)) }}">{{{ $request->reference }}}</a><br/>
            @endforeach
        </section>
    @endif

    @if ( ! $ai->attachments->isEmpty() )
        <section>
            <strong>{{ trans('general.attachments') }}:</strong><br>

            @include('file_uploads.partials.uploaded_file_show_only', ['files' => $ai->attachments, 'projectId' => $ai->project_id])
        </section>
    @endif
    @if($ai->status == \PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction::STATUS_SUBMITTED)
        <section>
            @include('verifiers.verifier_status_overview')
        </section>
        <section class="text-right">
            <strong class="text-success">{{{ $ai->status_text }}}</strong>
        </section>
    @endif
    @if($ai->status == \PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction::STATUS_PENDING)
        <section class="text-right">
            <strong class="text-warning">{{{ $ai->status_text }}}</strong>
        </section>
    @endif
</fieldset>