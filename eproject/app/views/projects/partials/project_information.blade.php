<div>
    <ul id="vendor-attachment-tab" class="nav nav-pills">
        <li class="nav-item active">
            <a class="nav-link" href="#project-main-information" data-toggle="tab"><i class="fas fa-info"></i> {{{ trans('projects.mainInformation') }}}</a>
        </li>

        @if($project->pam2006Detail)
        <li class="nav-item">
            <a class="nav-link" href="#project-post-contract-information" data-toggle="tab"><i class="fas fa-file-invoice"></i> {{{ trans('projects.postContractInformation') }}}</a>
        </li>
        @endif

        @if($project->indonesiaCivilContractInformation)
        <li class="nav-item">
            <a class="nav-link" href="#project-indo-contract-information" data-toggle="tab"><i class="fas fa-file-invoice"></i> {{{ trans('projects.postContractInformation') }}}</a>
        </li>
        @endif
    </ul>
</div>

<div id="vendor-attachment-tab-content" class="tab-content" style="padding-top:1rem!important;">
    <div class="tab-pane fade in active" id="project-main-information">
        <div class="well">
            <div class="row">
                <div class="col col-xs-12 col-md-12 col-lg-12">
                    @include('projects.partials.project_status')
                    <h5>{{{ trans('projects.mainInformation') }}}</h5>
                    <hr class="simple"/>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.title') }}}:</dt>
                        <dd>{{ nl2br($project->title) }}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>

            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.reference') }}}:</dt>
                        <dd>{{{ $project->reference }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.contractType') }}}:</dt>
                        <dd>{{ nl2br($project->contract->name) }}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>

            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.businessUnitName') }}}:</dt>
                        <dd>{{{ mb_strtoupper($project->subsidiary->fullName) }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.projectCreatorName') }}}:</dt>
                        <dd>{{{ $project->createdBy->name }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>

            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.description') }}}:</dt>
                        <dd>
                        @if(!empty($project->description))
                        {{ nl2br($project->description) }}
                        @endif
                        </dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>

            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.address') }}}:</dt>
                        <dd>
                        @if(!empty($project->address))
                        {{ nl2br($project->address) }}
                        @endif
                        </dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>

            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.country') }}}:</dt>
                        <dd>
                        @if($project->country)
                            {{{ mb_strtoupper($project->country->country) }}}
                        @else
                            N/A
                        @endif
                        </dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.state') }}}:</dt>
                        <dd>
                        @if($project->state)
                            {{{ mb_strtoupper($project->state->name) }}}
                        @else
                            N/A
                        @endif
                        </dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>

            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.workCategory') }}}:</dt>
                        <dd>
                        @if($project->workCategory)
                            {{{ mb_strtoupper($project->workCategory->name) }}}
                        @elseif($project->workCategory()->withTrashed()->first())
                            <span style="text-decoration: line-through">{{{ mb_strtoupper($project->workCategory()->withTrashed()->first()->name) }}}</span>
                        @else
                            N/A
                        @endif
                        </dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.currency') }}}:</dt>
                        <dd>
                        @if(empty($project->modified_currency_code))
                            {{{ mb_strtoupper($project->country->currency_code) }}}
                        @else
                            {{{ mb_strtoupper($project->modified_currency_code) }}}
                        @endif
                        </dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>
        @if($project->latestTender)
            <div class="row">
                <div class="col col-lg-12">
                    <h5>{{ trans('tenders.tenderingInformation') }}</h5>
                    <hr class="simple"/>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('tenders.publishDateTime') }}:</dt>
                        <dd>{{{ $project->latestTender->tender_starting_date ? date(\Config::get('dates.created_and_updated_at_formatting'), strtotime($project->getProjectTimeZoneTime($project->latestTender->tender_starting_date))) : '-' }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('tenders.closingDateTime') }}:</dt>
                        <dd>{{{ $project->latestTender->tender_closing_date ? date(\Config::get('dates.created_and_updated_at_formatting'), strtotime($project->getProjectTimeZoneTime($project->latestTender->tender_closing_date))) : '-' }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>
        @endif

        @if($project->completion_date)
            <div class="row">
                <div class="col col-lg-12">
                    <h5>Project Completion Information</h5>
                    <hr class="simple"/>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{ trans('projects.completionDate') }}}:</dt>
                        <dd>{{{ date('d M Y', strtotime($project->getProjectTimeZoneTime($project->completion_date))) }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>
        @endif
        </div>
    </div>

    @if($project->pam2006Detail)
    <?php
        $canEditPostContractInfo = $user->isEditor($project) && $user->hasCompanyProjectRole($project, [\PCK\ContractGroups\Types\Role::PROJECT_OWNER, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT]);
    ?>
    <div class="tab-pane fade" id="project-post-contract-information">
        <div class="well">
            <div class="row">
                <div class="col col-xs-12 col-md-12 col-lg-12">
                    @if($canEditPostContractInfo)
                    <span class="btn-group pull-right">
                        <a href="{{route('projects.postContract.info.edit', [$project->id])}}" class="btn btn-primary"><i class="fa fa-edit"></i> {{ trans('forms.edit') }}</a>
                    </span>
                    @endif
                    <h5>{{{ trans('projects.postContractInformation') }}}</h5>
                    <hr class="simple"/>
                </div>
            </div>
            <div class="well">
                <div class="row">
                    <div class="col col-xs-12 col-md-12 col-lg-12">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{ trans('projects.selectedContractor') }}:</dt>
                            <dd>{{{ ($selectedContractor = $project->getSelectedContractor()) ? mb_strtoupper($selectedContractor->name) : null }}}</dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{{ trans('projects.commencementDate') }}}:</dt>
                            <dd>{{{ $project->getProjectTimeZoneTime($project->pam2006Detail->commencement_date) }}}</dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{{ trans('projects.completionDate') }}}:</dt>
                            <dd>{{{ $project->getProjectTimeZoneTime($project->pam2006Detail->completion_date) }}}</dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{{ trans('openTenderAwardRecommendation.contractSum') }}}:</dt>
                            <dd>
                                @if(empty($project->modified_currency_code))
                                    {{{ mb_strtoupper($project->country->currency_code) }}}
                                @else
                                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                                @endif
                                {{{ number_format($project->pam2006Detail->contract_sum, 2) }}}
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
                <hr>
                @if($isBuOrGcdEditor)
                <div>
                    <a href="#" id="btnAddSectionalCompletionDate" class="btn btn-primary" data-target="#editorModal" data-toggle="modal"><i class="fa fa-plus"></i> {{ trans('general.add') }}</a>
                </div>
                @endif
                <br>
                <div id="sectionalCompletionDateTable"></div>
            </div>

            <br/>

            <div class="well">
                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Minimum no of days</dt>
                            <dt class="no-top-padding">to comply with AI</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->min_days_to_comply_with_ai }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for submitting</dt>
                            <dt class="no-top-padding no-bottom-padding">the notice of intention</dt>
                            <dt class="no-top-padding">to claim EOT</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_submitting_notice_of_intention_claim_eot }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for submitting</dt>
                            <dt class="no-top-padding">the final claim for EOT</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_submitting_final_claim_eot }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for architect to</dt>
                            <dt class="no-top-padding no-bottom-padding">request for further</dt>
                            <dt class="no-top-padding no-bottom-padding">particulars from</dt>
                            <dt class="no-top-padding">contractor for EOT Claim</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_architect_request_info_from_contractor_eot_claim }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for architect</dt>
                            <dt class="no-top-padding no-bottom-padding">to decide on</dt>
                            <dt class="no-top-padding">Contractor's EOT claim</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_architect_decide_on_contractor_eot_claim }}} {{{ trans('extensionOfTime.weeks') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for submitting</dt>
                            <dt class="no-top-padding">the notice of intention</dt>
                            <dt class="no-top-padding">to claim LE</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_submitting_note_of_intention_claim_l_and_e }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for submitting</dt>
                            <dt class="no-top-padding">the final claim for LE</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_submitting_final_claim_l_and_e }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for submitting</dt>
                            <dt class="no-top-padding no-bottom-padding">the notice of intention</dt>
                            <dt class="no-top-padding">to claim AE</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_submitting_note_of_intention_claim_ae }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Deadline for submitting</dt>
                            <dt class="no-top-padding">the final claim for AE</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->deadline_submitting_final_claim_ae }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Period of Architect issue</dt>
                            <dt class="no-top-padding no-bottom-padding">interim certificate to the</dt>
                            <dt class="no-top-padding no-bottom-padding">Employer after receive</dt>
                            <dt class="no-top-padding">Contractor's payment</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->period_of_architect_issue_interim_certificate }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Percentage of the</dt>
                            <dt class="no-top-padding no-bottom-padding">value of materials and</dt>
                            <dt class="no-top-padding no-bottom-padding">goods included in the</dt>
                            <dt class="no-top-padding">Certificate</dt>
                            <dd>
                                <div class="well">
                                {{{ number_format($project->pam2006Detail->percentage_value_of_materials_and_goods_included_in_certificate, 2, '.', ',') }}} %
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Percentage of</dt>
                            <dt class="no-top-padding">certified value retained</dt>
                            <dd>
                                <div class="well">
                                {{{ number_format($project->pam2006Detail->percentage_of_certified_value_retained, 2, '.', ',') }}} %
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Limit Of Retention Fund</dt>
                            <dd>
                                <div class="well">
                                {{{ number_format($project->pam2006Detail->limit_retention_fund, 2, '.', ',') }}} %
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Liquidate Damages</dt>
                            <dt class="no-top-padding">per day</dt>
                            <dd>
                                <div class="well">
                                @if(empty($project->modified_currency_code))
                                    {{{ mb_strtoupper($project->country->currency_code) }}}
                                @else
                                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                                @endif
                                {{{ number_format($project->pam2006Detail->liquidate_damages, 2, '.', ',') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Interim claim interval</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->interim_claim_interval }}} {{{ Str::plural('month', $project->pam2006Detail->interim_claim_interval) }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Amount Of</dt>
                            <dt class="no-top-padding">Performance Bond</dt>
                            <dd>
                                <div class="well">
                                @if(empty($project->modified_currency_code))
                                    {{{ mb_strtoupper($project->country->currency_code) }}}
                                @else
                                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                                @endif
                                {{{ number_format($project->pam2006Detail->amount_performance_bond, 2, '.', ',') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Period Of Honouring</dt>
                            <dt class="no-top-padding">Certificate</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->period_of_honouring_certificate }}} {{{ trans('extensionOfTime.days') }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Selected Trade:</dt>
                            <dd>
                                <div class="well">
                                @if($project->pam2006Detail->preDefinedLocationCode)
                                    {{{ $project->pam2006Detail->preDefinedLocationCode->name }}}
                                @endif
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">{{{trans('projects.cpcDate')}}}</dt>
                            <dd>
                                <div class="well">
                                {{{ ($project->pam2006Detail->cpc_date) ? date('d M Y', strtotime($project->pam2006Detail->cpc_date)) : "-"}}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">{{{trans('projects.extensionOfTimeDate')}}}</dt>
                            <dd>
                                <div class="well">
                                {{{ ($project->pam2006Detail->extension_of_time_date) ? date('d M Y', strtotime($project->pam2006Detail->extension_of_time_date)) : "-"}}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">{{{trans('projects.dlpPeriod')}}}</dt>
                            <dd>
                                <div class="well">
                                {{{ $project->pam2006Detail->defect_liability_period }}} {{{ $project->pam2006Detail->getDefectLiabilityPeriodUnitText() }}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">{{{trans('projects.cmgdDate')}}}</dt>
                            <dd>
                                <div class="well">
                                {{{ ($project->pam2006Detail->certificate_of_making_good_defect_date) ? date('d M Y', strtotime($project->pam2006Detail->certificate_of_making_good_defect_date)) : "-"}}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">{{{trans('projects.cncDate')}}}</dt>
                            <dd>
                                <div class="well">
                                {{{ ($project->pam2006Detail->cnc_date) ? date('d M Y', strtotime($project->pam2006Detail->cnc_date)) : "-"}}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Performance Bond</dt>
                            <dt class="no-bottom-padding">Validity Date</dt>
                            <dd>
                                <div class="well">
                                {{{ ($project->pam2006Detail->performance_bond_validity_date) ? date('d M Y', strtotime($project->pam2006Detail->performance_bond_validity_date)) : "-"}}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Insurance Policy</dt>
                            <dt class="no-bottom-padding">Coverage Date</dt>
                            <dd>
                                <div class="well">
                                {{{ ($project->pam2006Detail->insurance_policy_coverage_date) ? date('d M Y', strtotime($project->pam2006Detail->insurance_policy_coverage_date)) : "-"}}}
                                </div>
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <br />
            @include('projects.partials.project_labour_rates_information')
        </div>
    </div>
    @endif

    @if($project->indonesiaCivilContractInformation)
    <div class="tab-pane fade" id="project-indo-contract-information">
        <div class="well">
            <h5>{{{ trans('projects.postContractInformation') }}}</h5>
            <hr class="simple"/>
            <div class="well">
                <div class="row">
                    <div class="col col-xs-12 col-md-12 col-lg-12">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{ trans('projects.selectedContractor') }}:</dt>
                            <dd>{{{ ($selectedContractor = $project->getSelectedContractor()) ? mb_strtoupper($selectedContractor->name) : null }}}</dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-xs-12 col-md-12 col-lg-12">
                        <dl class="dl-horizontal no-margin">
                            <dt class="no-bottom-padding">Selected Trade:</dt>
                            <dd>
                            @if($project->postContractInformation->preDefinedLocationCode)
                                {{{ $project->postContractInformation->preDefinedLocationCode->name }}}
                            @endif
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{{ trans('projects.commencementDate') }}}:</dt>
                            <dd>{{{ $project->getProjectTimeZoneTime($project->postContractInformation->commencement_date) }}}</dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{{ trans('projects.completionDate') }}}:</dt>
                            <dd>{{{ $project->getProjectTimeZoneTime($project->postContractInformation->completion_date) }}}</dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                    <div class="col col-lg-4">
                        <dl class="dl-horizontal no-margin">
                            <dt>{{{ trans('openTenderAwardRecommendation.contractSum') }}}:</dt>
                            <dd>
                                @if(empty($project->modified_currency_code))
                                    {{{ mb_strtoupper($project->country->currency_code) }}}
                                @else
                                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                                @endif
                                {{{ number_format($project->postContractInformation->contract_sum, 2, '.', ',') }}}
                            </dd>
                            <dt>&nbsp;</dt>
                            <dd>&nbsp;</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <br />
            @include('projects.partials.project_labour_rates_information')
        </div>
    </div>
    @endif

</div>