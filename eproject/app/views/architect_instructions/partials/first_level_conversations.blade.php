<div class="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
    @foreach ($messages as $message)
        <div>
            <h4 role="tab" aria-selected="false" tabindex="0"
                id="{{ str_replace('%id%', $message->id, PCK\Forms\ArchitectInstructionMessageForm::accordianId) }}">
                <span class="ui-accordion-header-icon ui-icon fa fa-plus"></span>

                @if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
                    {{{ PCK\Forms\ArchitectInstructionMessageForm::formTitleOne }}}
                @else
                    {{{ PCK\Forms\ArchitectInstructionMessageForm::formTitleTwo }}}
                @endif
            </h4>

            <div class="padding-10 ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom"
                 role="tabpanel" aria-expanded="false" aria-hidden="true" style="display: none;">
                <div>
                    <strong>Subject/Reference:</strong><br>
                    {{{ $message->subject }}}
                </div>

                <div>
                    <strong>Date Submitted:</strong><br>
                    <span class="dateSubmitted">{{{ $ai->project->getProjectTimeZoneTime($message->created_at) }}}</span>
                    by {{{ $message->createdBy->present()->byWhoAndRole($ai->project, $ai->created_at) }}}
                </div>

                @if ( ! $message->attachedClauses->isEmpty() )
                    <div>
                        <strong>Clause(s) that empower the issuance of AI:</strong>
                        <br/>

                        <div>
                            @foreach ( $message->attachedClauses as $clause )
                                @include('clause_items.partials.clause_item_description_formatter', ['item' => $clause])
                                <br/>
                                <br/>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ( $message->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
                    <div>
                        <strong>Letter to Contractor:</strong><br>
                        {{{ $message->reason }}}
                    </div>
                @else
                    <div>
                        <strong>Letter to Architect:</strong><br>
                        {{{ $message->reason }}}
                    </div>
                @endif

                @if ( ! $message->attachments->isEmpty() )
                    <div>
                        <strong>Attachment(s):</strong><br>

                        @include('file_uploads.partials.uploaded_file_show_only', ['files' => $message->attachments, 'projectId' => $ai->project_id])
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>