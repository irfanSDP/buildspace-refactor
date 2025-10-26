<fieldset>
    <section>
        <strong>{{ trans('projects.project') }}:</strong><br>
        <label class="input">
            {{{ $eot->project->title }}}
        </label>
    </section>

    <section>
        <strong>{{ trans('extensionOfTime.extensionOfTimeReference') }}:</strong><br>
        {{{ $eot->reference }}}
    </section>

    <section>
        <strong>{{ trans('extensionOfTime.subject') }}:</strong><br>
        {{{ $eot->subject }}}
    </section>

    @if ( !$eot->attachedClauses->isEmpty() )
        <section>
            <strong>{{ trans('extensionOfTime.clausesThatEmpower') }}:</strong><br>
            <div>
                @foreach ( $eot->attachedClauses as $clause )
                    @include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
                    <br/>
                    <br/>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <strong>{{ trans('extensionOfTime.totalDays') }}:</strong><br>
        {{{ $eot->days }}}
    </section>

    <section>
        <strong>{{ trans('extensionOfTime.details') }}:</strong><br>
        {{{ $eot->details }}}
    </section>

    <section>
        <strong>{{ trans('extensionOfTime.dateIssued') }}:</strong><br>
        <label class="input">
            <strong class="dateSubmitted"><i>{{{ $project->getProjectTimeZoneTime($eot->created_at) }}}</i></strong> by {{{ $eot->createdBy->present()->byWhoAndRole($eot->project, $eot->created_at) }}}
        </label>
    </section>

    @if($eot->architectInstruction)
        <section>
            <strong>{{ trans('extensionOfTime.architectInstruction') }}:</strong><br>
                <a href="{{ route('indonesiaCivilContract.architectInstructions.show', array($project->id, $eot->architectInstruction->id)) }}">{{{ $eot->architectInstruction->reference }}}</a><br/>
        </section>
    @endif

    @if(!$eot->earlyWarnings->isEmpty())
        <section>
            <strong>{{ trans('extensionOfTime.earlyWarnings') }}:</strong><br>
            @foreach($eot->earlyWarnings as $warning)
                <a href="{{ route('indonesiaCivilContract.earlyWarning.show', array($project->id, $warning->id)) }}">{{{ $warning->reference }}}</a><br/>
            @endforeach
        </section>
    @endif

    @if ( ! $eot->attachments->isEmpty() )
        <section>
            <strong>{{ trans('general.attachments') }}:</strong><br>

            @include('file_uploads.partials.uploaded_file_show_only', ['files' => $eot->attachments, 'projectId' => $eot->project_id])
        </section>
    @endif
</fieldset>