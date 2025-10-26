<article class="col-sm-12 col-md-12 col-lg-5">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget jarviswidget-color-darken" role="widget">
        <header>
            <span class="widget-icon"><i class="fa fa-arrows-alt-v"></i></span>
            <h2><strong><i>{{ trans('lossAndExpenses.workflow') }}</i></strong></h2>
        </header>

        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body">
                <ol class="reminderContainer">
                    <li>
                        {{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.step1.main', array('currencyCode' => $project->modified_currency_code, 'claimAmount' => number_format($le->claim_amount,2), 'submissionDate' => $project->getProjectTimeZoneTime($le->updated_at))) }}
                    </li>
                    <li>
                        {{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.step2.main') }}
                        @foreach($le->responses as $response)
                            <ul>
                                <li>
                                    <a href="#response-{{{ $response->id }}}" data-id="response-{{{ $response->id }}}">
                                        @if($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE)
                                            {{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.step2.decisionNote.agreeOnProposedValue', array('submissionDate' => $project->getProjectTimeZoneTime($response->updated_at), 'currencyCode' => $project->modified_currency_code, 'claimAmount' => number_format($le->claim_amount, 2))) }}
                                        @elseif($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE)
                                            {{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.step2.decisionNote.rejectProposedValue', array('submissionDate' => $project->getProjectTimeZoneTime($response->updated_at))) }}
                                        @elseif($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT)
                                            {{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.step2.decisionNote.grant', array('submissionDate' => $project->getProjectTimeZoneTime($response->updated_at), 'currencyCode' => $project->modified_currency_code, 'claimAmount' => number_format($response->proposed_value, 2))) }}
                                        @elseif($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_PLAIN)
                                            {{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.step2.responseNote') }}
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        @endforeach
                        @if($le->canRespond($currentUser))
                            <ul>
                                <li>
                                    <a data-type="goToForm">{{ trans('lossAndExpenses.indonesiaCivilContract.workflowSteps.replyHere') }}</a>
                                </li>
                            </ul>
                        @endif
                    </li>
                </ol>
                <hr/>
                {{ trans('lossAndExpenses.indonesiaCivilContract.compensation') }}
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>