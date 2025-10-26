<?php
use PCK\Filters\TenderFilters;
$withModel = $tender->listOfTendererInformation ? true : false;
$readOnly = ( ( $withModel && ( $tender->listOfTendererInformation->isBeingValidated() || $tender->listOfTendererInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false;
$isTechnicalEvaluationReadOnly = ( ( $withModel && ( $tender->listOfTendererInformation->isTechnicalEvaluationReadOnly() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false;
$needValidation = ($withModel && $tender->listOfTendererInformation->isBeingValidated() && in_array($user->id, $tender->listOfTendererInformation->latestVerifier->lists('id'))) ? true : false;
?>
{{ Form::model($tender->listOfTendererInformation, array('method' => 'PUT', 'route' => array('projects.tender.update_lot_information', $project->id, $tender->id), 'class' => 'smart-form')) }}
    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.dateOfCallingTender') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('date_of_calling_tender') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->date_of_calling_tender) }}}
                    @else
                        <?php
                            $date = Input::old('date_of_calling_tender') ?? $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->date_of_calling_tender ?? null);
                            $dateOfCallingTender = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        <input type="datetime-local" name="date_of_calling_tender" value="{{ $dateOfCallingTender }}">
                    @endif
                </label>
                {{ $errors->first('date_of_calling_tender', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.commercialTenderClosingDate') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('date_of_closing_tender') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->date_of_closing_tender) }}}
                    @else
                        <?php
                            $date = Input::old('date_of_closing_tender') ?? $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->date_of_closing_tender ?? null);
                            $dateOfClosingTender = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        <input type="datetime-local" name="date_of_closing_tender" value="{{ $dateOfClosingTender }}">
                    @endif
                </label>
                {{ $errors->first('date_of_closing_tender', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row" hidden>
            <section class="col col-xs-12 col-md-6 col-lg-6\"></section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.technicalClosingDate') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('technical_tender_closing_date') ? 'state-error' : null }}}" data-id="technical_tender_closing_date">
                    @if ( $readOnly )
                        {{{ $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->technical_tender_closing_date) }}}
                    @else
                        <?php
                            $date = Input::old('technical_tender_closing_date') ?? $tender->project->getProjectTimeZoneTime($tender->listOfTendererInformation->technical_tender_closing_date ?? null);
                            $technicalTenderClosingDate = date('Y-m-d\TH:i:s', strtotime($date));
                        ?>
                        <input type="datetime-local" name="technical_tender_closing_date" value="{{ $technicalTenderClosingDate }}">
                    @endif
                </label>
                {{ $errors->first('technical_tender_closing_date', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">{{ trans('tenders.completionPeriod') }} ({{{ $tender->project->completion_period_metric }}}) <span class="required">*</span>:</label>
                <label class="input {{ $errors->has('budget') ? 'state-error' : null }}">
                    @if ( $readOnly )
                        {{{ $withModel ? $tender->listOfTendererInformation->completion_period : '-' }}}

                        {{ $withModel ? Form::hidden('completion_period') : null }}
                    @else
                        {{ Form::text('completion_period', Input::old('completion_period')) }}
                    @endif
                </label>
                {{ $errors->first('completion_period', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{trans('tenders.projectIncentive')}} :</label>
                <label class="input {{{ $errors->has('project_incentive_percentage') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        {{{ $withModel ? number_format($tender->listOfTendererInformation->project_incentive_percentage, 2) : '-' }}}

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
                        <?php $tender->listOfTendererInformation->load('procurementMethod'); ?>
                        {{{ $withModel ? ($tender->listOfTendererInformation->procurementMethod->name ?? '-') : '-' }}}

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
                <label class="checkbox {{{ $errors->has('allow_contractor_propose_own_completion_period') ? 'state-error' : null }}}">
                    <?php $disabled = array(); $checkBoxValue = false; ?>

                    @if ( $readOnly )
                        <?php $disabled = array('disabled' => 'disabled'); ?>

                        @if ( $withModel )
                            <?php $checkBoxValue = $tender->listOfTendererInformation->allow_contractor_propose_own_completion_period; ?>
                        @endif
                    @endif

                    {{ Form::checkbox('allow_contractor_propose_own_completion_period', 1, Input::old('allow_contractor_propose_own_completion_period', $checkBoxValue), $disabled) }}

                    <i></i>{{ trans('tenders.allowContractorToSubmitOwnCompletionPeriod') }}.
                </label>
                {{ $errors->first('allow_contractor_propose_own_completion_period', '<em class="invalid">:message</em>') }}

                <label class="checkbox">
                    <?php $disabled = array(); $checkBoxValue = false; ?>

                    @if ( $readOnly )
                        <?php $disabled = array('disabled' => 'disabled'); ?>

                        @if ( $withModel )
                            <?php $checkBoxValue = $tender->listOfTendererInformation->disable_tender_rates_submission; ?>
                        @endif
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
                <label class="textarea {{{ $errors->has('lot_remarks') ? 'state-error' : null }}}">
                    @if ( $readOnly )
                        @if($withModel && !empty($tender->listOfTendererInformation->remarks))
                            <?php echo nl2br(e($tender->listOfTendererInformation->remarks)); ?>
                        @else
                            -
                        @endif

                        {{ $withModel ? Form::hidden('lot_remarks') : null }}
                    @else
                        {{ Form::textarea('lot_remarks', Input::old('lot_remarks') ?? $tender->listOfTendererInformation->remarks, array('rows' => '1')) }}
                    @endif
                </label>
                {{ $errors->first('lot_remarks', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-5 col-lg-5">
                @if($readOnly)
                    @include('verifiers.verifier_list', array(
                        'verifiers' => $tender->listOfTendererInformation->verifiers,
                    ))
                @else
                    @include('verifiers.select_verifiers', array(
                        'verifiers'         => $verifiers,
                        'selectedVerifiers' => $tender->listOfTendererInformation->verifiers,
                        'showDesignation'   => true,
                    ))
                @endif
                {{ $errors->first('verifiers', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        @if ( $tender->listOfTendererInformation && $tender->listOfTendererInformation->verifierLogs->count() )
            @include('tenders.partials.verification_logs', array(
                'model' => $tender->listOfTendererInformation
            ))
        @endif

        @include('tenders.partials.lot_selected_contractors_table', array('disabled' => $readOnly))
    </fieldset>

    @if ( ! $readOnly  )
        <footer>
            {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}

            {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), array('type'=>'submit', 'class' => 'btn btn-success', 'name' => 'send_to_verify', 'data-intercept' => 'confirmation', 'data-intercept-condition' => 'noVerifier','data-confirmation-message' => trans('general.submitWithoutVerifier'))) }}

            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), array('type'=>'submit', 'class' => 'btn btn-primary')) }}

            {{ Form::button('<i class="fa fa-user-check"></i> '.trans('tenders.assignContractors'), array('class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#'.PCK\TenderListOfTendererInformation\TenderListOfTendererInformation::MODAL_ID)) }}

            @if ($tender->getTenderStage() === PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER)
            {{ Form::button('<i class="fa fa-envelope"></i> '.trans('tenders.expressionOfInterest'), array(
                'class'=>'btn btn-info compose-email-button',
                'data-title' => trans('tenders.expressionOfInterest'),
                'data-send-button-id' => 'sendLOTNotificationToContractorsButton',
                'style'=>'float:left')) }}
            @endif

            @if($publicTenderEnabled)
                <a href="{{{ route('projects.tender.open_tender.get',array($project->id,$tender->id,'tenderInfo')) }}}">
                    @if($project->open_tender) 
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning']) }}
                    @else
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning', 'data-intercept' => 'confirmation', 'data-confirmation-message' => trans('openTender.openTenderConfirmation')] )  }}
                    @endif
                </a>
            @endif
        </footer>
    @elseif ( $needValidation  )
        <footer>
            {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}

            <button type="button" class="btn btn-success" name="verification_confirm" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-check"></i> {{trans('forms.confirm')}}</button>

            <button type="button" class="btn btn-danger" name="verification_reject" data-toggle="modal" data-target="#verifier_remark_modal"><i class="fa fa-times"></i> {{trans('forms.reject')}}</button>

            @if (($currentUser->id == $tender->listOfTendererInformation->latestVerifier->first()->id) && ( ! \PCK\Forum\ObjectThread::objectHasThread($tender->listOfTendererInformation)))
                <button type="button" class="btn btn-warning" data-action="form-submit" data-target-id="listOfTenderersApprovalForumForm"><i class="fa fa-comment"></i> {{ trans('verifiers.comments') }}</button>
            @else
                @include('forum.partials.object_thread_link', array('object' => $tender->listOfTendererInformation))
            @endif

            @include('tenders.partials.verifier_remark_modal')

            @if($publicTenderEnabled)
                <a href="{{{ route('projects.tender.open_tender.get',array($project->id,$tender->id,'tenderInfo')) }}}">
                    @if($project->open_tender)
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning']) }}
                    @else
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning', 'data-intercept' => 'confirmation', 'data-confirmation-message' => trans('openTender.openTenderConfirmation')] )  }}
                    @endif
                </a>
            @endif
        </footer>
    @elseif ($tender->listOfTendererInformation && \PCK\Forum\Thread::hasForumThreadAccess($currentUser, $tender->listOfTendererInformation))
        <footer>
            @if($publicTenderEnabled)
                <a href="{{{ route('projects.tender.open_tender.get',array($project->id,$tender->id,'tenderInfo')) }}}">
                    @if($project->open_tender)
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning']) }}
                    @else
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning', 'data-intercept' => 'confirmation', 'data-confirmation-message' => trans('openTender.openTenderConfirmation')] )  }}
                    @endif
                </a>
            @endif
            @include('forum.partials.object_thread_link', array('object' => $tender->listOfTendererInformation))
        </footer>
    @else
        <footer>
            @if($publicTenderEnabled)
                @if($project->open_tender)
                    <a href="{{{ route('projects.tender.open_tender.get',array($project->id,$tender->id,'tenderInfo')) }}}">
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning']) }}
                    </a>
                    @if($tender->openTenderPageInformation)
                        @if($tender->openTenderPageInformation->special_permission)
                            {{ Form::button('<i class="fa fa-envelope"></i> '.trans('openTender.requestForPayment'), ['type' => 'button', 'class' => 'btn btn-info', 'id' => 'request-for-payment']) }}
                        @endif
                    @endif
                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), array('type'=>'submit', 'class' => 'btn btn-primary', 'id' => 'update-contractor-status')) }}
                @else
                    <a href="{{{ route('projects.tender.open_tender.get',array($project->id,$tender->id,'tenderInfo')) }}}">
                        {{ Form::button('<i class="fa fa-building"></i> '.trans('openTender.openTender'), ['type' => 'button', 'class' => 'btn btn-warning', 'data-intercept' => 'confirmation', 'data-confirmation-message' => trans('openTender.openTenderConfirmation')] )  }}
                    </a>
                @endif
            @endif
        </footer>
    @endif

{{ Form::close() }}
{{ Form::open(array('route' => array('lot_information.forum.threads.initialise', $project->id, $tender->id), 'id' => 'listOfTenderersApprovalForumForm', 'hidden' => true)) }}
{{ Form::close() }}

@if ( ! $readOnly  )
    @include('templates.generic_table_modal', [
        'modalId'          => PCK\TenderListOfTendererInformation\TenderListOfTendererInformation::MODAL_ID,
        'title'            => trans('tenders.contractorsList'),
        'tableId'          => 'selectContractorsTable',
        'showCancel'       => true,
        'cancelText'       => trans('forms.close'),
        'showSubmit'       => true,
        'modalDialogClass' => 'modal-xl',
    ])
@endif

<script type="text/javascript">
    $('button[name="verification_confirm"], button[name="verification_reject"]').on('click', function(){

        var name = $(this).prop('name');

        $('button#remark').prop('name', name);
    })

    $('#request-for-payment').click(function(){
        $.ajax({
            url: "{{route('open_tender.lot_pending_contractor_email_notification', array($project->id, $tender->id))}}",
            type: 'POST',
            data: {_token:'{{{csrf_token()}}}'},
            success: function(response) {
                console.log(response);
                $.smallBox({
                    title : "<?php echo trans('general.success'); ?>",
                    content : "<i class='fa fa-check'></i> <i><?php echo trans('forms.emailSentSuccessful'); ?></i>",
                    color : "#739E73",
                    sound: true,
                    iconSmall : "fa fa-envelope",
                    timeout : 5000
                });
            },
            error: function(error) {
                console.error(error);
            }
        });
    });
</script>