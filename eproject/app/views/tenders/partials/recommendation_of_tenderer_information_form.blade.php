<?php
use Carbon\Carbon;
use PCK\ContractGroups\Types\Role;
$withModel = $tender->recommendationOfTendererInformation ? true : false;
$readOnly = ( ( $withModel && ( $tender->recommendationOfTendererInformation->isBeingValidated() OR $tender->recommendationOfTendererInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, Role::PROJECT_OWNER) ) ) ? true : false;
$needValidation = ($withModel && $tender->recommendationOfTendererInformation->isBeingValidated() && in_array($user->id, $tender->recommendationOfTendererInformation->latestVerifier->lists('id'))) ? true : false;
?>

{{ Form::model($tender->recommendationOfTendererInformation, array('method' => 'PUT', 'route' => array('projects.tender.update_rot_information', $project->id, $tender->id), 'class' => 'smart-form')) }}

    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.proposedDateOfCallingTender') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('proposed_date_of_calling_tender') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->proposed_date_of_calling_tender) : '-' }}}

                        {{ $withModel ? Form::hidden('proposed_date_of_calling_tender') : null }}
                    @else
                        <?php
                            $date = Input::old('proposed_date_of_calling_tender') ?? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->proposed_date_of_calling_tender ?? date('Y-m-d'));
                            $proposedDateOfCallingTender = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        <input type="datetime-local" name="proposed_date_of_calling_tender" value="{{ $proposedDateOfCallingTender }}">
                    @endif
                </label>
                {{ $errors->first('proposed_date_of_calling_tender', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.proposedCommercialTenderClosingDate') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('proposed_date_of_closing_tender') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->proposed_date_of_closing_tender) : '-' }}}

                        {{ $withModel ? Form::hidden('proposed_date_of_closing_tender') : null }}
                    @else
                        <?php
                            $date = Input::old('proposed_date_of_closing_tender') ?? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->proposed_date_of_closing_tender ?? date('Y-m-d'));
                            $proposedDateOfClosingTender = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        <input type="datetime-local" name="proposed_date_of_closing_tender" value="{{ $proposedDateOfClosingTender }}">
                    @endif
                </label>
                {{ $errors->first('proposed_date_of_closing_tender', '<em class="invalid">:message</em>') }}
            </section>
        </div>
        <div class="row" hidden>
            <section class="col col-xs-12 col-md-6 col-lg-6\"></section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.proposedTechnicalTenderClosingDate') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('technical_tender_closing_date') ? 'state-error' : null }}}" data-id="technical_tender_closing_date">
                    @if ( $readOnly )
                        {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->technical_tender_closing_date) : '-' }}}

                        {{ $withModel ? Form::hidden('technical_tender_closing_date') : null }}
                    @else
                        <?php
                            $date = Input::old('technical_tender_closing_date') ?? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->technical_tender_closing_date ?? date('Y-m-d'));
                            $proposedTechnicalTenderClosingDate = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        <input type="datetime-local" name="technical_tender_closing_date" value="{{ $proposedTechnicalTenderClosingDate }}">
                    @endif
                </label>
                {{ $errors->first('technical_tender_closing_date', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-6 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.completionPeriod') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('completion_period') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? $tender->recommendationOfTendererInformation->completion_period : '-' }}}

                        {{ $withModel ? Form::hidden('completion_period') : null }}
                    @else
                        {{ Form::text('completion_period', Input::old('completion_period')) }}
                    @endif
                </label>
                {{ $errors->first('completion_period', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-6 col-md-3 col-lg-3">
                <label class="label">&nbsp;</label>
                <label class="select {{{ $errors->has('completion_period_metric') ? 'state-error' : null }}} fill">
                    @if ( $readOnly )
                        {{{ $withModel ? \PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation::getCompletionPeriodMetricText($tender->recommendationOfTendererInformation->completion_period_metric) : '' }}}

                        {{ $withModel ? Form::hidden('completion_period_metric') : null }}
                    @else
                        {{ Form::select('completion_period_metric', $completionPeriodMetricOptions, Input::old('completion_period_metric'), array('class' => 'select2')) }}
                    @endif
                </label>
                {{ $errors->first('completion_period_metric', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.projectIncentive') }} :</label>
                <label class="input {{{ $errors->has('project_incentive_percentage') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? number_format($tender->recommendationOfTendererInformation->project_incentive_percentage, 2) : '-' }}}

                        {{ $withModel ? Form::hidden('project_incentive_percentage') : null }}
                    @else
                        {{ Form::text('project_incentive_percentage', Input::old('project_incentive_percentage')) }}
                    @endif
                </label>
                {{ $errors->first('project_incentive_percentage', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{ trans('tenders.procurementMethod') }} :</label>
                <label class="select {{{ $errors->has('procurement_method_id') ? 'state-error' : null }}} fill">
                    @if ( $readOnly )
                        <?php if($withModel) $tender->recommendationOfTendererInformation->load('procurementMethod'); ?>
                        {{{ $withModel ? ($tender->recommendationOfTendererInformation->procurementMethod->name ?? '-') : '-' }}}

                        {{ $withModel ? Form::hidden('procurement_method_id') : null }}
                    @else
                        {{ Form::select('procurement_method_id', $procurementMethodOptions, Input::old('procurement_method_id'), array('class' => 'select2')) }}
                    @endif
                </label>
                {{ $errors->first('procurement_method_id', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.budget') }} ({{ trans('tenders.excludingContingencySum') }}) <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('budget') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        @if($withModel)
                            <div class="col col-xs-6 col-md-6 col-lg-6" style="padding-left:0;">
                            {{{ number_format($tender->recommendationOfTendererInformation->budget, 2) }}}
                            </div>
                            @if($tender->project->onPostContractStages() && $tender->recommendationOfTendererInformation->budget == 0)
                            <div class="col col-xs-6 col-md-6 col-lg-6">
                            {{ Form::button('<i class="fa fa-dollar-sign"></i> '.trans('forms.update'), [
                                'class'=>'btn btn-sm btn-success',
                                'data-toggle'=>"modal",
                                'data-target'=>"#update_zero_budget-modal"
                                ])
                            }}
                            </div>
                            @endif
                            {{ Form::hidden('budget') }}
                        @else
                            {{{ '-' }}}
                        @endif
                    @else
                        {{ Form::text('budget', Input::old('budget')) }}
                    @endif
                </label>
                {{ $errors->first('budget', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.consultantEstimates') }} ({{ trans('tenders.excludingContingencySum') }}) :</label>
                <label class="input {{{ $errors->has('consultant_estimates') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? number_format($tender->recommendationOfTendererInformation->consultant_estimates, 2) : '-' }}}

                        {{ $withModel ? Form::hidden('consultant_estimates') : null }}
                    @else
                        {{ Form::text('consultant_estimates', Input::old('consultant_estimates')) }}
                    @endif
                </label>
                {{ $errors->first('consultant_estimates', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.targetDateOfSitePosession') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('target_date_of_site_possession') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->target_date_of_site_possession) : '-' }}}

                        {{ $withModel ? Form::hidden('target_date_of_site_possession') : null }}
                    @else
                        <?php
                            $date = Input::old('target_date_of_site_possession') ?? $tender->project->getProjectTimeZoneTime($tender->recommendationOfTendererInformation->target_date_of_site_possession ?? date('Y-m-d'));
                            $proposedTechnicalTenderClosingDate = Carbon::parse($date)->format('Y-m-d');
                        ?>
                        <input type="date" name="target_date_of_site_possession" value="{{ $proposedTechnicalTenderClosingDate }}">
                    @endif
                </label>
                {{ $errors->first('target_date_of_site_possession', '<em class="invalid">:message</em>') }}
            </section>

            <section class="col col-xs-12 col-md-6 col-lg-6">
                <div class="row">
                    <div class="col col-md-12">
                        <label class="label">&nbsp;</label>
                        <label class="checkbox {{{ $errors->has('allow_contractor_propose_own_completion_period') ? 'state-error' : null }}}">
                            <?php $disabled = array(); $checkBoxValue = false; ?>

                            @if ( $readOnly )
                                <?php $disabled = array('disabled' => 'disabled'); ?>

                                @if ( $withModel )
                                    <?php $checkBoxValue = $tender->recommendationOfTendererInformation->allow_contractor_propose_own_completion_period; ?>
                                @endif
                            @endif

                            {{ Form::checkbox('allow_contractor_propose_own_completion_period', 1, Input::old('allow_contractor_propose_own_completion_period', $checkBoxValue), $disabled) }}

                            <i></i>{{ trans('tenders.allowContractorToSubmitOwnCompletionPeriod') }}
                        </label>
                        {{ $errors->first('allow_contractor_propose_own_completion_period', '<em class="invalid">:message</em>') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-12">
                        <label class="checkbox">
                            <?php $disabled = array(); $checkBoxValue = false; ?>

                            @if ( $readOnly )
                                <?php $disabled = array('disabled' => 'disabled'); ?>

                                @if ( $withModel )
                                    <?php $checkBoxValue = $tender->recommendationOfTendererInformation->disable_tender_rates_submission; ?>
                                @endif
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
                    @if ( $readOnly )
                        @if($withModel && !empty($tender->recommendationOfTendererInformation->remarks))
                            <?php echo nl2br(e($tender->recommendationOfTendererInformation->remarks)); ?>
                        @else
                            -
                        @endif

                        @if ($withModel)
                            <input type="hidden" name="remarks">
                        @endif
                    @else
                        {{ Form::textarea('remarks', Input::old('remarks'), array('rows' => '1')) }}
                    @endif
                </label>
                {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                @if($readOnly)
                    @include('verifiers.verifier_list', array(
                        'verifiers' => $tender->recommendationOfTendererInformation ? $tender->recommendationOfTendererInformation->verifiers : array(),
                    ))
                @else
                    @include('verifiers.select_verifiers', array(
                        'verifiers'         => $verifiers,
                        'selectedVerifiers' => $tender->recommendationOfTendererInformation ? $tender->recommendationOfTendererInformation->verifiers : array(),
                        'showDesignation'   => true,
                    ))
                @endif
                {{ $errors->first('verifiers', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        @if ( $tender->recommendationOfTendererInformation && $tender->recommendationOfTendererInformation->verifierLogs->count() )
            @include('tenders.partials.verification_logs', array(
                'model' => $tender->recommendationOfTendererInformation
            ))
        @endif

        @include('tenders.partials.rot_selected_contractors_table', array('disabled' => $readOnly))
</fieldset>

@if ( ! $readOnly  )
    <footer>
        {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}

        @if ( $tender->recommendationOfTendererInformation )
            {{ Form::button('<i class="fa fa-file-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'send_to_verify', 'data-intercept' => 'confirmation', 'data-intercept-condition' => 'noVerifier', 'data-confirmation-message' => trans('general.submitWithoutVerifier')] )  }}
        @endif

        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}

        @if ( $tender->recommendationOfTendererInformation )
            {{ Form::button('<i class="fa fa-users"></i> '.trans('tenders.assignContractors'), array('class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#'.PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation::MODAL_ID)) }}
        @endif

        @if ($tender->getTenderStage() === PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER)
            {{ Form::button('<i class="fa fa-envelope"></i> '.trans('tenders.expressionOfInterest'), array(
                'class'=>'btn btn-warning compose-email-button',
                'data-title' => trans('tenders.expressionOfInterest'),
                'data-send-button-id' => 'sendROTNotificationToContractorsButton',
                'style'=>'float:left')) }}
        @endif
    </footer>
@elseif ( $needValidation  )
    <footer>
        {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
        <button type="button" class="btn btn-success" name="verification_confirm" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-check"></i> {{trans('forms.confirm')}}</button>

        <button type="button" class="btn btn-danger" name="verification_reject" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-times"></i> {{trans('forms.reject')}}</button>

        @include('tenders.partials.verifier_remark_modal')

        @if( ( ! \PCK\Forum\ObjectThread::objectHasThread($tender->recommendationOfTendererInformation) ) && $currentUser->id == $tender->recommendationOfTendererInformation->latestVerifier->first()->id)
            <button type="button" class="btn btn-warning" data-action="form-submit" data-target-id="recommendationOfTenderersApprovalForumForm"><i class="fa fa-comment"></i> {{ trans('verifiers.comments') }}</button>
        @else
            @include('forum.partials.object_thread_link', array('object' => $tender->recommendationOfTendererInformation))
        @endif
    </footer>
@elseif($readOnly && $withModel && \PCK\Forum\Thread::hasForumThreadAccess($currentUser, $tender->recommendationOfTendererInformation))
    <footer>
        @include('forum.partials.object_thread_link', array('object' => $tender->recommendationOfTendererInformation))
    </footer>
@endif

{{ Form::close() }}

{{ Form::open(array('route' => array('rot_information.forum.threads.initialise', $project->id, $tender->id), 'id' => 'recommendationOfTenderersApprovalForumForm', 'hidden' => true)) }}
{{ Form::close() }}

@if ( ! $readOnly  )
    @include('templates.generic_table_modal', [
        'modalId'          => PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation::MODAL_ID,
        'title'            => trans('tenders.contractorsList'),
        'tableId'          => 'selectContractorsTable',
        'showCancel'       => true,
        'cancelText'       => trans('forms.close'),
        'showSubmit'       => true,
        'modalDialogClass' => 'modal-xl',
    ])
@endif

@if ( $readOnly && $withModel && $tender->project->onPostContractStages() && $tender->recommendationOfTendererInformation->budget == 0)

<div class="modal fade" id="update_zero_budget-modal" tabindex="-1" role="dialog" aria-labelledby="verifier_remark_modal" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('general.confirmation') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                        Please be informed that this is a <strong>one-time update</strong>. Once you have updated the budget amount, it <strong>cannot be changed</strong> anymore.
                        <br /><strong>Please make sure that the value is correct before saving it.</strong>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="well well-sm well-primary">
                            {{ Form::model($tender->recommendationOfTendererInformation, array('method' => 'POST', 'route' => array('projects.tender.update_rot_budget', $project->id, $tender->id), 'class' => 'smart-form form-inline')) }}
                            <div class="form-group">
                                <label class="label">{{ trans('tenders.budget') }} ({{ trans('tenders.excludingContingencySum') }}) <span class="required">*</span>:</label>
                                <label class="input">
                                {{ Form::text('budget', $tender->recommendationOfTendererInformation->budget, ['required' => 'required']) }}
                                </label>
                            </div>
                            <div class="ms-4 form-group">
                                <label class="label">&nbsp;</label>
                                <label class="input">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-save"></i> {{trans('forms.save')}}
                                    </button>
                                </label>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('forms.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

@endif


<script type="text/javascript">
    $('button[name="verification_confirm"], button[name="verification_reject"]').on('click', function(){

        var name = $(this).prop('name');

        $('button#remark').prop('name', name);
    })
</script>