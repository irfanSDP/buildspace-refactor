<article class="col-sm-12 col-md-12 col-lg-5">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget jarviswidget-color-darken" role="widget">
        <header>
            <span class="widget-icon"><i class="fa fa-arrows-alt-v"></i></span>
            <h2><strong><i>{{ trans('earlyWarnings.workflow') }}</i></strong></h2>
        </header>

        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body">
                <ol class="reminderContainer">
                    <li>
                        {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step1.main') }}
                    </li>
                    <li>
                        {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.main') }}
                        <ul>
                            @foreach($ew->extensionsOfTime as $eot)
                                <li>
                                    @if($eot->status == \PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime::STATUS_DRAFT)
                                        @if($currentUser->getAssignedCompany($project)->id == $project->getSelectedContractor()->id)
                                            <a href="{{ route('indonesiaCivilContract.extensionOfTime.show', array($project->id, $eot->id)) }}">
                                                {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.link.draftExtensionOfTime') }} [{{{ $eot->reference }}}]
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('indonesiaCivilContract.extensionOfTime.show', array($project->id, $eot->id)) }}">
                                            {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.link.pendingExtensionOfTime') }} [{{{ $eot->reference }}}]
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                            @if($ew->extensionsOfTime->isEmpty() && ($currentUser->getAssignedCompany($project)->id == $project->getSelectedContractor()->id))
                                <li>
                                    <a href="{{ route('indonesiaCivilContract.extensionOfTime.create', array($project->id)).'?ew='.$ew->id }}">
                                        {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.link.createExtensionOfTime') }}
                                    </a>
                                </li>
                            @endif
                            @foreach($ew->lossAndExpenses as $le)
                                <li>
                                    @if($le->status == \PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime::STATUS_DRAFT)
                                        @if($currentUser->getAssignedCompany($project)->id == $project->getSelectedContractor()->id)
                                            <a href="{{ route('indonesiaCivilContract.lossAndExpenses.show', array($project->id, $le->id)) }}">
                                                {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.link.draftLossAndExpense') }} [{{{ $le->reference }}}]
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('indonesiaCivilContract.lossAndExpenses.show', array($project->id, $le->id)) }}">
                                            {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.link.pendingLossAndExpense') }} [{{{ $le->reference }}}]
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                            @if($ew->lossAndExpenses->isEmpty() && ($currentUser->getAssignedCompany($project)->id == $project->getSelectedContractor()->id))
                                <li>
                                    <a href="{{ route('indonesiaCivilContract.lossAndExpenses.create', array($project->id)).'?ew='.$ew->id }}">
                                        {{ trans('earlyWarnings.indonesiaCivilContract.workflowSteps.step2.link.createLossAndExpense') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                </ol>
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>