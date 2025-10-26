<article class="col-sm-12 col-md-12 col-lg-5">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget jarviswidget-color-darken" role="widget">
        <header>
            <span class="widget-icon"><i class="fa fa-arrows-alt-v"></i></span>
            <h2><strong><i>{{ trans('extensionOfTime.workflow') }}</i></strong></h2>
        </header>

        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body">
                <ol class="reminderContainer">
                    <li>
                        {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step1.main', array('submissionDate' => $project->getProjectTimeZoneTime($eot->updated_at))) }}
                    </li>
                    <li>
                        {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step2.main', array('latestDate' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($eot->updated_at))->addDays(21)->format(Config::get('dates.submission_date_formatting')))) }}
                        @foreach($eot->responses as $response)
                            <ul>
                                <li>
                                    <a href="#response-{{{ $response->id }}}" data-id="response-{{{ $response->id }}}">
                                        @if($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE)
                                            {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step2.decisionNote.agreeOnProposedValue', array('submissionDate' => $project->getProjectTimeZoneTime($response->updated_at), 'numberOfDays' => $eot->days)) }}
                                        @elseif($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE)
                                            {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step2.decisionNote.rejectProposedValue', array('submissionDate' => $project->getProjectTimeZoneTime($response->updated_at))) }}
                                        @elseif($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT)
                                            {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step2.decisionNote.grant', array('submissionDate' => $project->getProjectTimeZoneTime($response->updated_at), 'numberOfDays' => number_format($response->proposed_value))) }}
                                        @elseif($response->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_PLAIN)
                                            {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step2.responseNote') }}
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        @endforeach
                        @if($eot->canRespond($currentUser))
                            <ul>
                                <li>
                                    <a data-type="goToForm">{{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.replyHere') }}</a>
                                </li>
                            </ul>
                        @endif
                        @if(!$eot->responses->isEmpty())
                            <br/>
                            {{ trans('extensionOfTime.indonesiaCivilContract.workflowSteps.step2.footnote') }}
                        @endif
                    </li>
                </ol>
                <hr/>
                {{ trans('extensionOfTime.indonesiaCivilContract.compensation') }}
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>