<?php
use PCK\ContractGroups\Types\Role;
$withModel = $tender->recommendationOfTendererInformation ? true : false;
$readOnly = ( ( $withModel && ( $tender->recommendationOfTendererInformation->isBeingValidated() OR $tender->recommendationOfTendererInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, Role::PROJECT_OWNER) ) ) ? true : false;
$needValidation = ($withModel && $tender->recommendationOfTendererInformation->isBeingValidated() && in_array($user->id, $tender->recommendationOfTendererInformation->latestVerifier->lists('id'))) ? true : false;
?>

{{ Form::model($tender->recommendationOfTendererInformation, array('method' => 'PUT', 'route' => array('topManagementVerifiers.projects.tender.update_rot_information', $project->id, $tender->id), 'class' => 'smart-form')) }}

    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.proposedDateOfCallingTender') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->proposed_date_of_calling_tender) : '-' }}}
                    {{ $withModel ? Form::hidden('proposed_date_of_calling_tender') : null }}
                </label>
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.proposedCommercialTenderClosingDate') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->proposed_date_of_closing_tender) : '-' }}}
                    {{ $withModel ? Form::hidden('proposed_date_of_closing_tender') : null }}
                </label>
            </section>
        </div>
        <div class="row" hidden>
            <section class="col col-xs-12 col-md-6 col-lg-6\"></section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.proposedTechnicalTenderClosingDate') }} <span class="required">*</span>:</label>
                <label data-id="technical_tender_closing_date">
                    {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->technical_tender_closing_date) : '-' }}}
                    {{ $withModel ? Form::hidden('technical_tender_closing_date') : null }}
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-6 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.completionPeriod') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $withModel ? $tender->recommendationOfTendererInformation->completion_period : '-' }}}
                    {{ $withModel ? Form::hidden('completion_period') : null }}
                </label>
            </section>
            <section class="col col-xs-6 col-md-3 col-lg-3">
                <label class="label">&nbsp;</label>
                <label>
                    {{{ $withModel ? \PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation::getCompletionPeriodMetricText($tender->recommendationOfTendererInformation->completion_period_metric) : '' }}}
                    {{ $withModel ? Form::hidden('completion_period_metric') : null }}
                </label>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.projectIncentive') }} :</label>
                <label>
                    {{{ $withModel ? number_format($tender->recommendationOfTendererInformation->project_incentive_percentage, 2) : '-' }}}
                    {{ $withModel ? Form::hidden('project_incentive_percentage') : null }}
                </label>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.procurementMethod') }} :</label>
                <label>
                    <?php if($withModel) $tender->recommendationOfTendererInformation->load('procurementMethod'); ?>
                    {{{ $withModel ? ($tender->recommendationOfTendererInformation->procurementMethod->name ?? '-') : '-' }}}
                    {{ $withModel ? Form::hidden('procurement_method_id') : null }}
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.budget') }} ({{ trans('tenders.excludingContingencySum') }}) <span class="required">*</span>:</label>
                <label>
                    @if($withModel)
                        <div class="col col-xs-6 col-md-6 col-lg-6" style="padding-left:0;">
                        {{{ number_format($tender->recommendationOfTendererInformation->budget, 2) }}}
                        </div>
                        {{ Form::hidden('budget') }}
                    @else
                        {{{ '-' }}}
                    @endif
                </label>
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.consultantEstimates') }} ({{ trans('tenders.excludingContingencySum') }}) :</label>
                <label>
                    {{{ $withModel ? number_format($tender->recommendationOfTendererInformation->consultant_estimates, 2) : '-' }}}
                    {{ $withModel ? Form::hidden('consultant_estimates') : null }}
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.targetDateOfSitePosession') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->target_date_of_site_possession) : '-' }}}
                    {{ $withModel ? Form::hidden('target_date_of_site_possession') : null }}
                </label>
            </section>

            <section class="col col-xs-12 col-md-6 col-lg-6">
                <div class="row">
                    <div class="col col-md-12">
                        <label class="label">&nbsp;</label>
                        <label class="checkbox {{{ $errors->has('allow_contractor_propose_own_completion_period') ? 'state-error' : null }}}">
                            <?php $disabled = array(); $checkBoxValue = false; ?>
                            <?php $disabled = array('disabled' => 'disabled'); ?>

                            @if ( $withModel )
                                <?php $checkBoxValue = $tender->recommendationOfTendererInformation->allow_contractor_propose_own_completion_period; ?>
                            @endif

                            {{ Form::checkbox('allow_contractor_propose_own_completion_period', 1, Input::old('allow_contractor_propose_own_completion_period', $checkBoxValue), $disabled) }}
                            <i></i>{{ trans('tenders.allowContractorToSubmitOwnCompletionPeriod') }}
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-12">
                        <label class="checkbox">
                            <?php $disabled = array(); $checkBoxValue = false; ?>
                            <?php $disabled = array('disabled' => 'disabled'); ?>

                            @if ( $withModel )
                                <?php $checkBoxValue = $tender->recommendationOfTendererInformation->disable_tender_rates_submission; ?>
                            @endif

                            {{ Form::checkbox('disable_tender_rates_submission', 1, Input::old('disable_tender_rates_submission', $checkBoxValue), $disabled) }}
                            <i></i>{{ trans('tenders.disableTenderRatesSubmission') }}
                        </label>
                    </div>
                </div>

                @if($hasTechnicalEvaluationTemplate)
                    @include('tenders.partials.technical_evaluation_fields', array(
                        'isChecked' => $tender->recommendationOfTendererInformation->technical_evaluation_required ?? false,
                        'selectedContractLimitId' => $tender->recommendationOfTendererInformation->contract_limit_id ?? null,
                        'isTechnicalEvaluationReadOnly' => $readOnly
                    ))
                @endif
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">
                    {{{ trans('general.remarks') }}}:
                </label>
                <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                    @if($withModel && !empty($tender->recommendationOfTendererInformation->remarks))
                        <?php echo nl2br(e($tender->recommendationOfTendererInformation->remarks)); ?>
                    @else
                        -
                    @endif

                    @if ($withModel)
                        <input type="hidden" name="remarks">
                    @endif
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                @include('verifiers.verifier_list', array(
                    'verifiers' => $tender->recommendationOfTendererInformation ? $tender->recommendationOfTendererInformation->verifiers : array(),
                ))
            </section>
        </div>

        @if ( $tender->recommendationOfTendererInformation && $tender->recommendationOfTendererInformation->verifierLogs->count() )
            @include('tenders.partials.verification_logs', array(
                'model' => $tender->recommendationOfTendererInformation
            ))
        @endif

        @include('tenders.partials.rot_selected_contractors_table', array('disabled' => true))
</fieldset>

@if($needValidation)
<footer>
    {{ link_to_route('home.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
    <button type="button" class="btn btn-success" name="verification_confirm" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-check"></i> {{trans('forms.confirm')}}</button>
    <button type="button" class="btn btn-danger" name="verification_reject" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-times"></i> {{trans('forms.reject')}}</button>
    @include('tenders.partials.verifier_remark_modal')
</footer>
@endif

{{ Form::close() }}

<script type="text/javascript">
    $('button[name="verification_confirm"], button[name="verification_reject"]').on('click', function(){

        var name = $(this).prop('name');

        $('button#remark').prop('name', name);
    })
</script>