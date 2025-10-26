<fieldset>
    <section>
        <strong>{{ trans('projects.project') }}:</strong><br>
        <label class="input">
            {{{ $le->project->title }}}
        </label>
    </section>

    <section>
        <strong>{{ trans('lossAndExpenses.lossAndExpensesReference') }}:</strong><br>
        {{{ $le->reference }}}
    </section>

    <section>
        <strong>{{ trans('lossAndExpenses.subject') }}:</strong><br>
        {{{ $le->subject }}}
    </section>

    @if ( !$le->attachedClauses->isEmpty() )
        <section>
            <strong>{{ trans('lossAndExpenses.clausesThatEmpower') }}:</strong><br>
            <div>
                @foreach ( $le->attachedClauses as $clause )
                    @include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
                    <br/>
                    <br/>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <strong>{{ trans('lossAndExpenses.claimAmount') }}:</strong><br>
        {{{ $project->modified_currency_code }}} {{{ number_format($le->claim_amount, 2) }}}
    </section>

    <section>
        <strong>{{ trans('lossAndExpenses.details') }}:</strong><br>
        {{{ $le->details }}}
    </section>

    <section>
        <strong>{{ trans('lossAndExpenses.dateIssued') }}:</strong><br>
        <label class="input">
            <strong class="dateSubmitted"><i>{{{ $project->getProjectTimeZoneTime($le->created_at) }}}</i></strong> by {{{ $le->createdBy->present()->byWhoAndRole($le->project, $le->created_at) }}}
        </label>
    </section>

    @if($le->architectInstruction)
        <section>
            <strong>{{ trans('lossAndExpenses.architectInstruction') }}:</strong><br>
                <a href="{{ route('indonesiaCivilContract.architectInstructions.show', array($project->id, $le->architectInstruction->id)) }}">{{{ $le->architectInstruction->reference }}}</a><br/>
        </section>
    @endif

    @if(!$le->earlyWarnings->isEmpty())
        <section>
            <strong>{{ trans('lossAndExpenses.earlyWarnings') }}:</strong><br>
            @foreach($le->earlyWarnings as $warning)
                <a href="{{ route('indonesiaCivilContract.earlyWarning.show', array($project->id, $warning->id)) }}">{{{ $warning->reference }}}</a><br/>
            @endforeach
        </section>
    @endif

    @if ( ! $le->attachments->isEmpty() )
        <section>
            <strong>{{ trans('general.attachments') }}:</strong><br>

            @include('file_uploads.partials.uploaded_file_show_only', ['files' => $le->attachments, 'projectId' => $le->project_id])
        </section>
    @endif
</fieldset>