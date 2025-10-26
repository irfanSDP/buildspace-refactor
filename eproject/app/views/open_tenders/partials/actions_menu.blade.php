<?php
    $hasEditorRole = $currentUser->hasCompanyProjectRole($project, PCK\Filters\OpenTenderFilters::editorRoles($project)) && $currentUser->isEditor($project);
    $showTenderRevisionButton = $hasEditorRole && (! $project->onPostContractStages()) && (!$tender->hasBeenReTender() && $project->latestTender->allowedReTender());
    $showToPostContractButtons = $hasEditorRole && (! $project->onPostContractStages()) && $toPostContract;
    $showTenderValidityPeriodButton = $hasEditorRole && $isAClosed_openTender;
?>

<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @if ( $showVerifierLogs )
            <li>
                <a href="#" data-toggle="modal" data-target="#verifierLogsModal" class="btn btn-block btn-md btn-warning">
                    View {{ trans("tenders.tenderResubmission") }} Verifier Logs
                </a>
            </li>
        @endif

        @if ( $showTenderRevisionButton )
            <li class="divider"></li>
            <li>
                <a href="{{route('projects.openTender.reTender', array($tender->project_id, $tender->id) )}}" class="btn btn-block btn-md btn-danger">
                    <i class="fa fa-plus"></i>
                    {{ trans("tenders.tenderResubmission") }}
                </a>
            </li>
        @endif
        @if($project->canSyncBuildSpaceContractorRates())
        <li class="divider sync-buildspace-contractor-rates"></li>
        <li class="sync-buildspace-contractor-rates">
            <a href="{{route('projects.openTender.syncContractorRatesIntoBuildSpace', array($tender->project_id) )}}" class="btn btn-block btn-md btn-success">
                <i class="fa fa-sync-alt"></i> {{ trans('tenders.syncToBuildspace') }}
            </a>
        </li>
        @endif
        @if($isProjectOwnerOrGCD && $isLatestTender)
            <li class="divider"></li>
            <li>
            @if($awardRecommendation)
                {{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id], ['class' => 'btn btn-block btn-md btn-primary ' . ( ($canViewAwardRecommendation) ? null : 'disabled'), 'id' => 'btnAwardRecommendation']) }}
            @else
                @if($project->onPostContractStages() && $previousAwardRecommendation && $previousAwardRecommendation->isApproved())
                    {{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendationPrevious'), [$project->id, $previousTender->id], ['class' => 'btn btn-block btn-md btn-primary', 'id' => 'btnAwardRecommendation']) }}
                @else
                    {{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id], ['class' => 'btn btn-block btn-md btn-primary disabled', 'id' => 'btnAwardRecommendation']) }}
                @endif
            @endif
            </li>
        @endif
        @if ( $showToPostContractButtons )
            <li class="divider"></li>
            <li>
                <a id="postToPostContract" href="{{route('projects.postContract.create', array($project->id) )}}" class="btn btn-block btn-md btn-primary">
                    Post Contract
                </a>
            </li>
        @endif
        @if($showTenderValidityPeriodButton)
            <?php
            $tenderValidUntilDate = \Carbon\Carbon::parse($tender->project->getProjectTimeZoneTime($tender->validUntil()))->format(Config::get('dates.standard_spaced'));
            $tenderValidityPeriodButtonText = $tender->validity_period_in_days ?
                    'Tender Valid Until: [' . $tenderValidUntilDate . '] (' . $tender->validity_period_in_days . ' days total)' :
                    'Tender Validity Period (Not Specified)';
            ?>
            <li class="divider"></li>
            <li>
                <button type="button" class="btn btn-block btn-md {{{ $tender->validity_period_in_days ? 'btn-success' : 'btn-warning' }}}" id="tender_validity_period_button" data-toggle="modal" data-tooltip data-target="#tenderValidityPeriodModal" data-placement="bottom" title="Click to specify the tender's validity period">{{{ $tenderValidityPeriodButtonText }}}</button>
            </li>
        @endif
        @if($tender->isTenderOpen())
        <li class="divider"></li>
        <li>
            <a href="{{ route('projects.openTender.form.excel.export', [$project->id, $tender->id]) }}" class="btn btn-block btn-md btn-default" data-action="export-overall-report">
	    		<i class="fas fa-file-excel"></i>&nbsp;&nbsp;{{ trans('tenders.exportOpenTenderForm') }}
	    	</a>
	    </li>
        @endif
        <li class="divider enableEbidding" style="{{ $showEnableEbidding ? '' : 'display:none;' }}"></li>
        <li>
            <a href="{{ route('projects.e_bidding.create', [$project->id, $tender->id]) }}" class="btn btn-block btn-md btn-default enableEbidding" style="{{ $showEnableEbidding ? '' : 'display:none;' }}">
                <i class="fas fa-gavel"></i>&nbsp;&nbsp;{{ trans('tenders.enableebidding') }}
            </a>
        </li>
    </ul>
</div>