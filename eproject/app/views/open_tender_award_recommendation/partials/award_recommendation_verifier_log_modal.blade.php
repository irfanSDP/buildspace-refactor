<div class="modal" id="openTenderAwardRecommendationVerifierLogModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header alert-danger">
                <h4 class="modal-title" id="myModalLabel">
                    {{ trans('verifiers.verifierLogs') }}
                </h4>
            </div>

            <div class="modal-body">
                @if($awardRecommendation->submitter)
                    <div class="well" style="margin-top:12px;margin-bottom:12px;">
                        <?php
                            $updatedAt = $awardRecommendation->updated_at;
                            if(isset($project)) $updatedAt = $project->getProjectTimeZoneTime($awardRecommendation->updated_at);
                            $requestedAt = $verifierRecords->isEmpty() ? $updatedAt : $verifierRecords->first()->created_at;
                            $requested_at = Carbon\Carbon::parse($requestedAt)->format(\Config::get('dates.created_at'));
                        ?>
                        <strong>{{ trans('general.verificationRequestedBy') }} <span class="text-primary">{{{ $awardRecommendation->submitter->name }}}</span> {{ trans('general.at') }} <span class="text-danger">{{{ $requested_at }}}</span></strong>
                    </div>
                @endif
                @include('verifiers.verifier_status_overview')
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>