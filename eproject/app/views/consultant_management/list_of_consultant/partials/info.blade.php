<?php use PCK\ObjectField\ObjectField; ?>

<div class="row">
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.callingRfpDate') }}:</dt>
            <dd>{{{ $consultantManagementContract->getContractTimeZoneTime($listOfConsultant->calling_rfp_date) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.closingRfpDate') }}:</dt>
            <dd>{{{ $consultantManagementContract->getContractTimeZoneTime($listOfConsultant->closing_rfp_date) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
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
            <dt></dt>
            <dd>

                @if(Confide::user()->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
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
                @else
                    <button type="button" class="btn btn-md btn-info" id="view_upload_loc_attachment-btn" 
                        data-route-get-attachments-list="{{ route('consultant.management.list.of.consultant.attachment.list', [$listOfConsultant->id, ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL]) }}">
                        <?php 
                            $record = ObjectField::findRecord($listOfConsultant, ObjectField::CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL);
                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                        ?>
                        <i class="fas fa-paperclip fa-md"></i> {{{trans('forms.attachments')}}} &nbsp;({{ $attachmentCount }})
                    </button>
                @endif

            </dd>
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
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark"><i class="fa fa-users"></i> Selected Consultant(s)</h1>
        <div id="selected_consultants-table"></div>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <div class="pull-right">
            {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
            {{ link_to_route('consultant.management.loc.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
        </div>
    </section>
</div>
