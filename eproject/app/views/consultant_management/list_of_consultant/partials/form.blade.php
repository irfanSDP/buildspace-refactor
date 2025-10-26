<?php use PCK\ObjectField\ObjectField; ?>

{{ Form::open(['route' => ['consultant.management.loc.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
    <div class="row">
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.callingRfpDate') }}} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('calling_rfp_date') ? 'state-error' : null }}}">
                <?php
                $date = Input::old('calling_rfp_date', (isset($listOfConsultant)) ? $consultantManagementContract->getContractTimeZoneTime($listOfConsultant->calling_rfp_date) : date('Y-m-d\TH:i'));
                $callingRfpProposedDate = date('Y-m-d\TH:i', strtotime($date));
                ?>
                <input id="calling_rfp_date" name="calling_rfp_date" type="datetime-local" value="{{ $callingRfpProposedDate }}" required>
            </label>
            {{ $errors->first('calling_rfp_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.closingRfpDate') }}} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('closing_rfp_date') ? 'state-error' : null }}}">
                <?php
                $date = Input::old('closing_rfp_date', (isset($listOfConsultant)) ? $consultantManagementContract->getContractTimeZoneTime($listOfConsultant->closing_rfp_date) : date('Y-m-d\TH:i'));
                $closingRfpProposedDate = date('Y-m-d\TH:i', strtotime($date));
                ?>
                <input id="closing_rfp_date" name="closing_rfp_date" type="datetime-local" value="{{ $closingRfpProposedDate }}" required>
            </label>
            {{ $errors->first('closing_rfp_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4"></section>
    </div>
    <div class="row">
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.costType') }}} :</label>
            <label>
                {{{$vendorCategoryRfp->getCostTypeText()}}}
            </label>
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.proposedFee') }}} ({{{$currencyCode}}}) <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('proposed_fee') ? 'state-error' : null }}}">
                {{ Form::number('proposed_fee', Input::old('proposed_fee', number_format($listOfConsultant->proposed_fee, 2, '.', '')), ['required'=>'required', 'step' => '0.01']) }}
            </label>
            {{ $errors->first('proposed_fee', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <button type="button" class="btn btn-md btn-info" id="upload_loc_attachment-btn"
                data-route-get-attachments-list="{{ route('consultant.management.list.of.consultant.attachment.list', [$listOfConsultant->id, ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL]) }}"
                data-route-update-attachments="{{ route('consultant.management.list.of.consultant.attachment.store', [$listOfConsultant->id, ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL]) }}"
                data-route-get-attachments-count="{{ route('consultant.management.list.of.consultant.attachment.count', [$listOfConsultant->id, ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL]) }}"
                data-field="{{ ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL }}"
                data-phase-id="{{ $listOfConsultant->id }}">
                <?php 
                    $record = ObjectField::findRecord($listOfConsultant, ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL);
                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                ?>
                <i class="fas fa-paperclip fa-md"></i> {{{trans('forms.attachments')}}} (<span data-component="{{ $listOfConsultant->id }}_{{ ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL }}_count">{{ $attachmentCount }}</span>)
            </button>
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
            <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                {{ Form::textarea('remarks', Input::old('remarks', $listOfConsultant->remarks), ['rows' => 3]) }}
            </label>
            {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <hr class="simple">
    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            @include('verifiers.select_verifiers', array(
                'verifiers' => $verifiers,
                'selectedVerifiers' => $selectedVerifiers,
            ))
            <label class="input {{{ $errors->has('verifiers') ? 'state-error' : null }}}"></label>
            {{ $errors->first('verifiers', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <hr class="simple">
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark"><i class="fa fa-users"></i> Selected Consultant(s)</h1>
            @if($errors->has('empty_consultant'))
            <label class="input state-error"></label>
            {{ $errors->first('empty_consultant', '<em class="invalid">:message</em>') }}
            @endif
            <div id="selected_consultants-table"></div>
        </section>
    </div>
    <footer>
        {{ Form::hidden('id', $listOfConsultant->id) }}
        {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
        {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
        {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'name'=>'send_to_verify', 'class' => 'btn btn-success'] )  }}
        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
        {{ Form::button('<i class="fa fa-users"></i> '.trans('general.assignConsultants'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#listOfConsultant-select_consultant-modal']) }}
    </footer>
{{ Form::close() }}