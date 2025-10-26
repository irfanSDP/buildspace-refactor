<?php $withModel = $tender->callingTenderInformation ? true : false; ?>

{{ Form::model($tender->callingTenderInformation, array('method' => 'PUT', 'route' => array('projects.tender.update_calling_tender_information', $project->id, $tender->id), 'class' => 'smart-form')) }}

    <?php 
    
    $readOnly = ( ( $withModel && ( $tender->callingTenderInformation->isBeingValidated() OR $tender->callingTenderInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) ) ) ? true : false;
    
    $needValidation = ( $withModel && $tender->callingTenderInformation->isBeingValidated() && in_array($user->id, $tender->callingTenderInformation->latestVerifier->lists('id')) ) ? true : false;

    $extendAble = ( !$project->onPostContractStages() && !$tender->hasBeenReTender() && $withModel && $tender->callingTenderInformation->isSubmitted() && $isEditor && $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) ) ? true : false;
    
    ?>

    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.dateOfCallingTender') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('date_of_calling_tender') ? 'state-error' : null }}}">
                    <?php
                        $date = Input::old('date_of_calling_tender') ?? $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender ?? null);
                        $dateOfCallingTender = date('Y-m-d\TH:i:s', strtotime($date));
                    ?>
                    @if ( $extendAble )
                        <input type="datetime-local" name="date_of_calling_tender" value="{{ $dateOfCallingTender }}">
                    @elseif ( $readOnly )
                        {{{ $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender) }}}
                    @else
                        <input type="datetime-local" name="date_of_calling_tender" value="{{ $dateOfCallingTender }}">
                    @endif
                </label>
                {{ $errors->first('date_of_calling_tender', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.commercialTenderClosingDate') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('date_of_closing_tender') ? 'state-error' : null }}}">
                    <?php
                        $date = Input::old('date_of_closing_tender') ?? $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender ?? null);
                        $dateOfClosingTender = date('Y-m-d\TH:i:s', strtotime($date));
                    ?>
                    @if ( $extendAble )
                        <input type="datetime-local" name="date_of_closing_tender" value="{{ $dateOfClosingTender }}">
                    @elseif ( $readOnly )
                        {{{ $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender) }}}
                    @else
                        <input type="datetime-local" name="date_of_closing_tender" value="{{ $dateOfClosingTender }}">
                    @endif
                </label>
                {{ $errors->first('date_of_closing_tender', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row" hidden>
            <section class="col col-xs-12 col-md-6 col-lg-6"></section>
            @if($tender->listOfTendererInformation->technical_evaluation_required)
                <section class="col col-xs-12 col-md-6 col-lg-6">
                    <label class="label">{{ trans('tenders.technicalTenderClosingDate') }} <span class="required">*</span>:</label>
                    <label class="input {{{ $errors->has('technical_tender_closing_date') ? 'state-error' : null }}}" data-id="technical_tender_closing_date">
                        <?php
                            $date = Input::old('technical_tender_closing_date') ?? $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
                            $technicalTenderClosingDate = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        @if ( $extendAble )
                            <input type="datetime-local" name="technical_tender_closing_date" value="{{ $technicalTenderClosingDate }}">
                        @elseif ( $readOnly )
                            {{{ $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date) }}}
                        @else
                            <input type="datetime-local" name="technical_tender_closing_date" value="{{ $technicalTenderClosingDate }}">
                        @endif
                    </label>
                    {{ $errors->first('technical_tender_closing_date', '<em class="invalid">:message</em>') }}
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

                    @if ( $extendAble )
                    @elseif ( $readOnly )
                        <?php $disabled = array('disabled' => 'disabled'); ?>
                    @endif

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
                @if(isset($extendAble) AND $extendAble)
                    @include('verifiers.select_verifiers', array(
                        'verifiers' => $verifiers,
                        'selectedVerifiers' => $tender->callingTenderInformation->verifiers,
                    ))
                @elseif ( $readOnly )
                    @include('verifiers.verifier_list', array(
                        'verifiers' => $tender->callingTenderInformation->verifiers,
                    ))
                @else
                    @include('verifiers.select_verifiers', array(
                        'verifiers' => $verifiers,
                        'selectedVerifiers' => $tender->callingTenderInformation->verifiers,
                    ))
                @endif
                {{ $errors->first('verifiers', '<em class="invalid">:message</em>') }}
            </section>
        </div>
        @if ( $tender->callingTenderInformation && $tender->callingTenderInformation->verifierLogs->count() )
            @include('tenders.partials.verification_logs', array(
                'model' => $tender->callingTenderInformation
            ))
        @endif

        @include('tenders.partials.calling_tender_contractors_table', ['disabled' => (!$tender->callingTenderInformation->allowEditableContractorStatus($user))])

        <div class="row">
            <section class="col col-xs-12">
                <button type="button" class="btn btn-info" name="acknowledgement_letter" id="acknowledgement_letter" data-toggle="modal" data-target="#acknowledgementLetterModal">
                    <i class="fa fa-envelope"></i> {{ trans('tenders.acknowledgementLetter') }}
                </button>
            </section>
        </div>
    </fieldset>

    @if ( $extendAble )
        <footer>

            {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}

            {{ Form::button(trans('forms.extend'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'dates_extension', 'data-intercept' => 'confirmation', 'data-intercept-condition' => 'noVerifier'] )  }}

            {{ Form::button('<i class="fa fa-comments"></i>&nbsp;'.trans('tenders.tenderInterview'), array('class' => 'btn btn-warning', 'data-toggle' => 'modal', 'data-target' => '#tenderInterviewModal')) }}

        </footer>
    @elseif ( ! $readOnly  )
        <footer>
            {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}

            {{ Form::button('<i class="fa fa-file-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'send_to_verify', 'data-intercept' => 'confirmation', 'data-intercept-condition' => 'noVerifier', 'data-confirmation-message' => trans('general.submitWithoutVerifier')] )  }}

            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}

            {{ Form::button('<i class="fa fa-envelope"></i> '.trans('tenders.tenderInvitation'), array(
                'class'=>'btn btn-default compose-email-button',
                'data-title' => trans('tenders.tenderInvitation'),
                'data-send-button-id' => 'sendCallingTenderNotificationToContractorsButton',
                'style'=>'float:left')) }}

            {{ Form::button('<i class="fa fa-comments"></i>&nbsp;'.trans('tenders.tenderInterview'), array('class' => 'btn btn-warning', 'data-toggle' => 'modal', 'data-target' => '#tenderInterviewModal')) }}

        </footer>
    @elseif ( $needValidation  )
        <footer>
            {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}

            <button type="button" class="btn btn-danger" name="verification_reject" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-times"></i> {{trans('forms.reject')}}</button>

            <button type="button" class="btn btn-success" name="verification_confirm" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-check"></i> {{trans('forms.confirm')}}</button>

            @include('tenders.partials.verifier_remark_modal')

            {{ Form::button('<i class="fa fa-comments"></i>&nbsp;'.trans('tenders.tenderInterview'), array('class' => 'btn btn-warning', 'data-toggle' => 'modal', 'data-target' => '#tenderInterviewModal')) }}

        </footer>
    @endif

    @if($readOnly && !$needValidation && !$extendAble)
        <footer>
            {{ Form::button('<i class="fa fa-comments"></i>&nbsp;'.trans('tenders.tenderInterview'), array('class' => 'btn btn-warning', 'data-toggle' => 'modal', 'data-target' => '#tenderInterviewModal')) }}
        </footer>
    @endif

{{ Form::close() }}

@include('tenders.partials.acknowledgement_letter_modal')

<script src="{{ asset('js/summernote-master/dist/summernote.min.js') }}"></script>
<script type="text/javascript">

    $('button[name="verification_confirm"], button[name="verification_reject"]').on('click', function(){

        var name = $(this).prop('name');

        $('button#remark').prop('name', name);
    });

    $.ajax({
        url: "{{ route('projects.tender.acknowledgementLetter.checkEnableStatus', array($project->id, $tender->id)) }}",
        method: 'GET',
        data: null,
        success:function(data){
            if(data.result)
            {
                $('[data-action=acknowledgement-letter-enable]').prop('checked', 'true');
            }
        },
        error: function(error){
            console.log(error);
        }
    });
    $('.summernote').summernote({
        placeholder: 'Email content',
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['paragraph']],
            ['view', ['fullscreen']],
            ['help', ['help']],
            ['codeview', ['codeview']]
        ]
    });

    $('button#acknowledgement-letter-save-as-draft').on('click',function(){
        $('#acknowledgementLetterModal').modal('hide');
        $('#acknowledgementLetterPreviewModal').modal('hide');
        $.ajax({
            url: "{{ route('projects.tender.acknowledgementLetter.saveDraft', array($project->id, $tender->id)) }}",
            method: 'POST',
            data: {
                _token: '{{{ csrf_token() }}}',
                message: $('#acknowledgement-letter-message-input').code(),
                enable: $('#acknowledgement-letter-enable').prop('checked')
            },
            success:function(data){
                $.smallBox({
                    title : "{{ trans('general.success') }}",
                    content : "<i class='fa fa-check'></i> <i>{{ trans('forms.draftSaved') }}</i>",
                    color : "#739E73",
                    sound: true,
                    iconSmall : "fa fa-save",
                    timeout : 5000
                });
            },
            error: function(error){
                $.smallBox({
                    title : "{{ trans('forms.anErrorOccured') }}",
                    content : "<i class='fa fa-times-circle'></i> <i>{{ trans('forms.draftCouldNotBeSaved') }}</i>",
                    color : "#C46A69",
                    sound: true,
                    iconSmall : "fa fa-exclamation-triangle shake animated"
                });
            }
        });
    });

    $(document).on('shown.bs.modal', '#acknowledgementLetterPreviewModal', function() {
        $('#acknowledgementLetterModal').modal('hide');
        $('[data-id=acknowledgementLetterContent]').html($('#acknowledgement-letter-message-input').code());
    });

</script>