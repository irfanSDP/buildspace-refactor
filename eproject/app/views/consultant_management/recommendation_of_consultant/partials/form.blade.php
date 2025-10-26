@if(!Confide::user()->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT))
<div class="row">
    <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="alert alert-danger text-center">
            <i class="fa-fw fa fa-info"></i>
            <strong>Info!</strong> You don't have privilege to create or edit this form. 
        </div>
    </section>
</div>
@else

{{ Form::open(['route' => ['consultant.management.roc.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
    <div class="row">
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.callingRfpProposedDate') }}} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('calling_rfp_proposed_date') ? 'state-error' : null }}}">
                <?php
                $date = Input::old('calling_rfp_proposed_date', (isset($recommendationOfConsultant)) ? $consultantManagementContract->getContractTimeZoneTime($recommendationOfConsultant->calling_rfp_proposed_date) : date('Y-m-d\TH:i'));
                $callingRfpProposedDate = date('Y-m-d\TH:i', strtotime($date));
                ?>
                <input id="calling_rfp_proposed_date" name="calling_rfp_proposed_date" type="datetime-local" value="{{ $callingRfpProposedDate }}" required>
            </label>
            {{ $errors->first('calling_rfp_proposed_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.closingRfpProposedDate') }}} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('closing_rfp_proposed_date') ? 'state-error' : null }}}">
                <?php
                $date = Input::old('closing_rfp_proposed_date', (isset($recommendationOfConsultant)) ? $consultantManagementContract->getContractTimeZoneTime($recommendationOfConsultant->closing_rfp_proposed_date) : date('Y-m-d\TH:i'));
                $closingRfpProposedDate = date('Y-m-d\TH:i', strtotime($date));
                ?>
                <input id="closing_rfp_proposed_date" name="closing_rfp_proposed_date" type="datetime-local" value="{{ $closingRfpProposedDate }}" required>
            </label>
            {{ $errors->first('closing_rfp_proposed_date', '<em class="invalid">:message</em>') }}
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
                {{ Form::number('proposed_fee', Input::old('proposed_fee', (isset($recommendationOfConsultant)) ? number_format($recommendationOfConsultant->proposed_fee, 2, '.', '') : "0.00"), ['required'=>'required', 'step' => '0.01']) }}
            </label>
            {{ $errors->first('proposed_fee', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
            <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                {{ Form::textarea('remarks', Input::old('remarks', (isset($recommendationOfConsultant)) ? $recommendationOfConsultant->remarks : null), ['rows' => 3]) }}
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
            <h1 class="page-title txt-color-blueDark"><i class="fa fa-users"></i> Proposed Consultant(s)</h1>
            <div id="selected_consultants-table"></div>
        </section>
    </div>
    <footer>
        {{ Form::hidden('id', (isset($recommendationOfConsultant)) ? $recommendationOfConsultant->id : -1) }}
        {{ Form::hidden('contract_id', $consultantManagementContract->id) }}
        {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
        @if(isset($recommendationOfConsultant))
        {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
        @endif
        {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'name'=>'send_to_verify', 'class' => 'btn btn-success'] )  }}
        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
        {{ Form::button('<i class="fa fa-users"></i> '.trans('general.assignConsultants'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#recommendationOfConsultant-select_consultant-modal']) }}
    </footer>
{{ Form::close() }}

@endif