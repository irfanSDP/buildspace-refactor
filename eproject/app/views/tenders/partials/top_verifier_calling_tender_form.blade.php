<?php $withModel = $tender->callingTenderInformation ? true : false; ?>

{{ Form::model($tender->callingTenderInformation, array('method' => 'PUT', 'route' => array('topManagementVerifiers.projects.tender.update_calling_tender_information', $project->id, $tender->id), 'class' => 'smart-form')) }}

    <?php 
    
    $readOnly = ( ( $withModel && ( $tender->callingTenderInformation->isBeingValidated() OR $tender->callingTenderInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) ) ) ? true : false;
    
    $needValidation = ( $withModel && $tender->callingTenderInformation->isBeingValidated() && in_array($user->id, $tender->callingTenderInformation->latestVerifier->lists('id')) ) ? true : false;

    $extendAble = ( !$project->onPostContractStages() && !$tender->hasBeenReTender() && $withModel && $tender->callingTenderInformation->isSubmitted() && $isEditor && $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) ) ? true : false;
    
    ?>

    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.dateOfCallingTender') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender) }}}
                </label>
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.commercialTenderClosingDate') }} <span class="required">*</span>:</label>
                <label>
                    {{{ $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender) }}}
                </label>
            </section>
        </div>

        <div class="row" hidden>
            <section class="col col-xs-12 col-md-6 col-lg-6"></section>
            @if($tender->listOfTendererInformation->technical_evaluation_required)
                <section class="col col-xs-12 col-md-6 col-lg-6">
                    <label class="label">{{ trans('tenders.technicalTenderClosingDate') }} <span class="required">*</span>:</label>
                    <label>
                        {{{ $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date) }}}
                    </label>
                </section>
            @endif
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="checkbox color-grey-7">
                    {{ Form::checkbox('allow_contractor_propose_own_completion_period', 1, $tender->callingTenderInformation->allow_contractor_propose_own_completion_period) }}

                    <i></i>{{ trans('tenders.allowContractorToSubmitOwnCompletionPeriod') }}.
                </label>
                <label class="checkbox">
                    <?php $disabled = array(); $checkBoxValue = false; ?>
                    <?php $disabled = array('disabled' => 'disabled'); ?>

                    {{ Form::checkbox('disable_tender_rates_submission', 1, Input::old('disable_tender_rates_submission', $checkBoxValue), $disabled) }}

                    <i></i>{{ trans('tenders.disableTenderRatesSubmission') }}
                </label>
                @if($hasTechnicalEvaluationTemplate)
                    <div class="row">
                        <section class="col col-md-5">
                            <label class="checkbox color-grey-7">
                                <input type="checkbox" value="1" name="technical_evaluation_required" disabled {{{ $tender->configuredToHaveTechnicalEvaluation() ? "checked" : '' }}}/>

                                <i></i>{{ trans('technicalEvaluation.technicalEvaluation') }}
                            </label>
                        </section>

                        <section class="col col-md-7">
                            <label class="label color-grey-7" for="contract-limit">{{ trans('contractLimit.contractLimit') }}:</label>
                            <?php
                            $selectedContractLimit = (!empty($tender->listOfTendererInformation->contract_limit_id)) ? PCK\ContractLimits\ContractLimit::find($tender->listOfTendererInformation->contract_limit_id) : null;
                            ?>
                            <p>{{ !empty($selectedContractLimit) ? $selectedContractLimit->limit : trans("general.none") }}</p>
                        </section>
                    </div>
                @endif
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-5 col-lg-5">
                @include('verifiers.verifier_list', array(
                    'verifiers' => $tender->callingTenderInformation->verifiers,
                ))
            </section>
        </div>
        @if ( $tender->callingTenderInformation && $tender->callingTenderInformation->verifierLogs->count() )
            @include('tenders.partials.verification_logs', array(
                'model' => $tender->callingTenderInformation
            ))
        @endif

        @include('tenders.partials.calling_tender_contractors_table', ['disabled' => true])
    </fieldset>


    @if( $needValidation  )
        <footer>
            {{ link_to_route('home.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
            <button type="button" class="btn btn-danger" name="verification_reject" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-times"></i> {{trans('forms.reject')}}</button>
            <button type="button" class="btn btn-success" name="verification_confirm" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-check"></i> {{trans('forms.confirm')}}</button>
            @include('tenders.partials.verifier_remark_modal')
        </footer>
    @endif
{{ Form::close() }}

<script src="{{ asset('js/summernote-master/dist/summernote.min.js') }}"></script>
<script type="text/javascript">

    $('button[name="verification_confirm"], button[name="verification_reject"]').on('click', function(){

        var name = $(this).prop('name');

        $('button#remark').prop('name', name);
    });

</script>