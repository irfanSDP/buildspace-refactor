<?php
use PCK\Filters\TenderFilters;
$withModel = $tender->listOfTendererInformation ? true : false;
$readOnly = ( ( $withModel && ( $tender->listOfTendererInformation->isBeingValidated() || $tender->listOfTendererInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false;
$isTechnicalEvaluationReadOnly = ( ( $withModel && ( $tender->listOfTendererInformation->isTechnicalEvaluationReadOnly() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false;
$needValidation = ($withModel && $tender->listOfTendererInformation->isBeingValidated() && in_array($user->id, $tender->listOfTendererInformation->latestVerifier->lists('id'))) ? true : false;
?>
{{ Form::model($tender->listOfTendererInformation, array('method' => 'PUT', 'route' => array('topManagementVerifiers.projects.tender.update_lot_information', $project->id, $tender->id), 'class' => 'smart-form')) }}
    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.dateOfCallingTender') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->date_of_calling_tender) }}}
                </label>
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.commercialTenderClosingDate') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->date_of_closing_tender) }}}
                </label>
            </section>
        </div>

        <div class="row" hidden>
            <section class="col col-xs-12 col-md-6 col-lg-6"></section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.technicalClosingDate') }} <span class="required">*</span>:</label>
                <label>
                        {{{ $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->technical_tender_closing_date) }}}
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.completionPeriod') }} ({{{ $tender->project->completion_period_metric }}}) <span class="required">*</span>:</label>
                <label>
                    {{{ $withModel ? $tender->listOfTendererInformation->completion_period : '-' }}}
                    {{ $withModel ? Form::hidden('completion_period') : null }}
                </label>
                {{ $errors->first('completion_period', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{trans('tenders.projectIncentive')}} :</label>
                <label>
                    {{{ $withModel ? number_format($tender->listOfTendererInformation->project_incentive_percentage, 2) : '-' }}}
                    {{ $withModel ? Form::hidden('project_incentive_percentage') : null }}
                </label>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.procurementMethod') }} :</label>
                <label>
                    <?php $tender->listOfTendererInformation->load('procurementMethod'); ?>
                    {{{ $withModel ? ($tender->listOfTendererInformation->procurementMethod->name ?? '-') : '-' }}}
                    {{ $withModel ? Form::hidden('procurement_method_id') : null }}
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="checkbox">
                    <?php $disabled = array(); $checkBoxValue = false; ?>
                    <?php $disabled = array('disabled' => 'disabled'); ?>
                    @if ( $withModel )
                        <?php $checkBoxValue = $tender->listOfTendererInformation->allow_contractor_propose_own_completion_period; ?>
                    @endif
                    {{ Form::checkbox('allow_contractor_propose_own_completion_period', 1, Input::old('allow_contractor_propose_own_completion_period', $checkBoxValue), $disabled) }}
                    <i></i>{{ trans('tenders.allowContractorToSubmitOwnCompletionPeriod') }}.
                </label>
                <label class="checkbox">
                    <?php $disabled = array(); $checkBoxValue = false; ?>
                    <?php $disabled = array('disabled' => 'disabled'); ?>
                    @if ( $withModel )
                        <?php $checkBoxValue = $tender->listOfTendererInformation->disable_tender_rates_submission; ?>
                    @endif
                    {{ Form::checkbox('disable_tender_rates_submission', 1, Input::old('disable_tender_rates_submission', $checkBoxValue), $disabled) }}
                    <i></i>{{ trans('tenders.disableTenderRatesSubmission') }}
                </label>
                @if($hasTechnicalEvaluationTemplate)
                    @include('tenders.partials.technical_evaluation_fields', array(
                        'isChecked' => $tender->listOfTendererInformation->technical_evaluation_required,
                        'selectedContractLimitId' => $tender->listOfTendererInformation->contract_limit_id,
                    ))
                @endif
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">
                    {{{ trans('general.remarks') }}}:
                </label>
                <label class="textarea">
                    @if($withModel && !empty($tender->listOfTendererInformation->remarks))
                        <?php echo nl2br(e($tender->listOfTendererInformation->remarks)); ?>
                    @else
                        -
                    @endif

                    {{ $withModel ? Form::hidden('lot_remarks') : null }}
                </label>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-5 col-lg-5">
                @include('verifiers.verifier_list', array(
                    'verifiers' => $tender->listOfTendererInformation->verifiers,
                ))
            </section>
        </div>

        @if ( $tender->listOfTendererInformation && $tender->listOfTendererInformation->verifierLogs->count() )
            @include('tenders.partials.verification_logs', array(
                'model' => $tender->listOfTendererInformation
            ))
        @endif

        @include('tenders.partials.lot_selected_contractors_table', array('disabled' => true))
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