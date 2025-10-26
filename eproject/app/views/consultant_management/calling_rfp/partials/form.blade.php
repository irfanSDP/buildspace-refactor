<?php
$callingRfpDate = ($callingRfp && $callingRfp->calling_rfp_date) ? $callingRfp->calling_rfp_date : $listOfConsultant->calling_rfp_date;
$closingRfpDate = ($callingRfp && $callingRfp->closing_rfp_date) ? $callingRfp->closing_rfp_date : $listOfConsultant->closing_rfp_date;

?>
{{ Form::open(['route' => ['consultant.management.calling.rfp.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
    <div class="row">
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.callingRfpDate') }}} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('calling_rfp_date') ? 'state-error' : null }}}">
                <?php
                $date = Input::old('calling_rfp_date', $consultantManagementContract->getContractTimeZoneTime($callingRfpDate));
                $callingRfpDateValue = date('Y-m-d\TH:i:s', strtotime($date));
                ?>
                <input id="calling_rfp_date" name="calling_rfp_date" type="datetime-local" value="{{ $callingRfpDateValue }}" required>
            </label>
            {{ $errors->first('calling_rfp_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <label class="label">{{{ trans('general.closingRfpDate') }}} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('closing_rfp_date') ? 'state-error' : null }}}">
               <?php
                $date = Input::old('closing_rfp_date', $consultantManagementContract->getContractTimeZoneTime($closingRfpDate));
                $closingRfpDateValue = date('Y-m-d\TH:i:s', strtotime($date));
                ?>
                <input id="closing_rfp_date" name="closing_rfp_date" type="datetime-local" value="{{ $closingRfpDateValue }}" required>
            </label>
            {{ $errors->first('closing_rfp_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4"></section>
    </div>
    <div class="row">
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('general.costType') }}:</dt>
                <dd>{{{$vendorCategoryRfp->getCostTypeText()}}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </section>
        <section class="col col-xs-4 col-md-4 col-lg-4">
            <dl class="dl-horizontal no-margin">
                <dt>{{{ trans('general.proposedFee') }}} ({{{$currencyCode}}}):</dt>
                <dd>{{{number_format($listOfConsultant->proposed_fee, 2, '.', ',')}}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt>{{{ trans('vendorManagement.remarks') }}}:</dt>
                <dd><div class="well">{{ nl2br($listOfConsultant->remarks) }}</div></dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
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
            <h1 class="page-title txt-color-blueDark"><i class="fa fa-users"></i> Consultant(s)</h1>
            <div id="selected_consultants-table"></div>
        </section>
    </div>
    <footer>
        {{ Form::hidden('id', $callingRfp->id) }}
        {{ link_to_route('consultant.management.calling.rfp.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
        {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
        {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'name'=>'send_to_verify', 'class' => 'btn btn-success'] )  }}
        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
    </footer>
{{ Form::close() }}