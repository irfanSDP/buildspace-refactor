@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show',  str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.submitTender',  trans('navigation/projectnav.submitTender'), array($project->id)) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php $tenderClosed = $tender->isTenderClosed(); ?>

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-book"></i> {{ trans('tenders.submitTender') }}
            </h1>
        </div>
    </div>

    <div class="jarviswidget">
        <header>
            @if($tender->pivot->tenderSubmissionIsComplete())
                <span class="widget-icon"> <i class="fa fa-check-square"></i> </span>
                <h2 class="font-md">{{ trans('tenders.tenderSubmissionComplete') }}</h2>
            @else
                <span class="widget-icon"> <i class="fa fa-square-o"></i> </span>
                <h2 class="font-md">{{ trans('tenders.tenderSubmissionIncomplete') }}</h2>
            @endif
        </header>
        <div>
            <div class="widget-body">
                <?php
                $metCriteria = count(array_filter($tender->pivot->getSubmitTenderChecklist()));
                $totalCriteria = count($tender->pivot->getSubmitTenderChecklist());
                $barPercentage = ($totalCriteria == 0) ? 100 : ($metCriteria / $totalCriteria * 100);
                $fullBar = ( $barPercentage >= 100 );
                ?>
                <div class="text-center {{{ $fullBar ? 'text-success' : 'text-warning' }}}">
                    <strong>
                        {{{ $metCriteria }}} / {{{ $totalCriteria }}}
                    </strong>
                </div>
                <div class="progress progress-micro progress-striped active">
                    <div class="progress-bar {{{ $fullBar ? 'bg-color-green' : 'bg-color-orange' }}}" role="progressbar" style="width: {{{ $barPercentage }}}%"></div>
                </div>
                <ul class="list-group">
                @foreach($tender->pivot->getSubmitTenderChecklist() as $checklistItem => $checked)
                    <li class="list-group-item {{{ $checked ? 'list-group-item-success' : 'list-group-item-warning' }}}">
                        <a href="#{{{ $checklistItem }}}" style="color: inherit">
                            <i class="fa {{{ $checked ? 'fa-check' : 'fa-times-circle' }}}"></i> {{{ $checklistItem }}}
                        </a>
                    </li>
                @endforeach
                </ul>
                @if(isset($acknowledgementLetter) && $acknowledgementLetter->enable_letter)
                    @if($tender->pivot->tenderSubmissionIsComplete())
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <a target="_blank" href="{{ route('projects.submitTender.acknowledgementLetter.printDraft', array($project->id,$tender->id))}}">
                                    <button class="btn btn-primary btn-md">
                                        <span class="glyphicon glyphicon-print"></span>&nbsp;&nbsp;{{ trans('tenders.printAcknowledgementLetter') }}
                                    </button>
                                </a>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if($tender->configuredToHaveTechnicalEvaluation())
        <div class="jarviswidget">

            <!-- Anchor -->
            <a name="{{ trans('technicalEvaluation.technicalEvaluation') }}"></a>
            <a name="{{ trans('technicalEvaluation.technicalEvaluationAttachments') }}"></a>

            <header data-action="expandToggle" data-target="technicalEvaluationForm">
                @if(count(array_filter($tender->pivot->getSubmitTenderChecklist())) > 0)
                    @if($tender->pivot->getSubmitTenderChecklist())
                        <span class="widget-icon"> <i class="fa fa-check-square text-success"></i> </span>
                    @else
                        <span class="widget-icon"> <i class="fa fa-square-o text-warning"></i> </span>
                    @endif
                @endif
                <h2 class="font-md">{{{ trans('technicalEvaluation.technicalEvaluation') }}} ({{ trans('tenders.technicalSubmission') }})</h2>
            </header>
            <div data-type="expandable" data-id="technicalEvaluationForm">
                <div class="widget-body text-center" style="min-height: 0;">
                    <h5 class="text-left">
                        {{ trans('tenders.submissionDeadline') }}:
                        <strong>
                            {{{ $tender->project->getProjectTimeZoneTime($tender->technical_tender_closing_date) }}}
                        </strong>
                    </h5>
                    <hr class="simple">
                    <a class="btn btn-primary" data-action="view-technical-evaluation-form">
                        <i class="fa fa-pencil-alt"></i>
                        {{ trans('technicalEvaluation.form') }}
                    </a>
                    @if(isset($setReference))
                        @if(!$setReference->attachmentListItems->isEmpty())
                            <a class="btn btn-warning" data-toggle="modal" data-target="#technicalEvaluationAttachmentUploadModal">
                                <i class="fa fa-paperclip"></i>
                                {{ trans('technicalEvaluation.attachments') }}
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if((!$tender->callingTenderInformation->disable_tender_rates_submission) || $tender->pivot->isSubmitted())
    <?php
    $bsProjectMainInformation = $project->getBsProjectMainInformation();
    $awardedTenderAlternative = $bsProjectMainInformation->projectStructure->getAwardedTenderAlternative();
    $companyTender = PCK\Tenders\CompanyTender::find($tender->pivot->id);
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header data-action="expandToggle" data-target="tenderRates">
                    @if(isset($tender->pivot->getSubmitTenderChecklist()[trans('tenders.tenderRates')]))
                        <span class="widget-icon"> <i class="fa fa-check-square text-success"></i> </span>
                    @else
                        <span class="widget-icon"> <i class="fa fa-square-o text-warning"></i> </span>
                    @endif
                    <h2 class="font-md">{{{ trans('tenders.tenderRates') }}} ({{ trans('tenders.commercialSubmission') }})</h2>
                </header>

                <div data-type="expandable" data-id="tenderRates">
                    @if ( $tender->pivot->isSubmitted() )
                        <div class="widget-body">
                            <h5>{{ trans('tenders.submittedDate') }} - {{{ $tender->project->getProjectTimeZoneTime($tender->pivot->submitted_at) }}}</h5>

                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                    <dl class="no-margin">
                                        <dt>{{ trans('tenders.attachments') }}:</dt>
                                        <dd>
                                            <a href="#" data-toggle="modal"
                                               data-target="#submitTender-{{{ $contractor->id }}}"
                                               class="btn btn-xs btn-info">
                                               <i class="fa fa-sm fa-paperclip"></i> {{{ $tender->pivot->attachments->count() }}}
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                    <dl class="no-margin">
                                        <dt>{{ trans('tenders.tenderRates') }}:</dt>
                                        <dd>
                                            <a href="{{ route('projects.submitTender.downloadRatesFile', array($project->id, $tender->id, $contractor->id)) }}"
                                               class="btn btn-xs btn-primary">
                                                <i class="fa fa-sm fa-download"></i> {{ trans('general.download') }}
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                    <dl class="no-margin">
                                        <dt>{{ trans('tenders.printFormOfTender') }}:</dt>
                                        <dd>
                                            <a href="{{ route('form_of_tender.contractorInput.print', array($project->id, $tender->id, $contractor->id)) }}" target="_blank"
                                               class="btn btn-xs btn-primary">
                                               <i class="fa fa-sm fa-print"></i> {{ trans('general.print') }}
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <hr class="simple">
                            @if($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count())
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                    @foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
                                        @if($companyTender->tenderAlternatives->count())
                                            @foreach($companyTender->tenderAlternatives as $companyTenderTenderAlternative)
                                                @if($companyTenderTenderAlternative->tender_alternative_id == $tenderAlternative->id)
                                                <th>@if($awardedTenderAlternative && $tenderAlternative->id == $awardedTenderAlternative->id) <i class="fa-fw fa fa-award fa-2x text-warning"></i> @endif {{ $tenderAlternative->title }}</th>
                                                @endif
                                            @endforeach
                                        @else
                                        <th>{{ $tenderAlternative->title }}</th>
                                        @endif
                                    @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    @if($companyTender->tenderAlternatives->count())
                                        @foreach($companyTender->tenderAlternatives as $companyTenderTenderAlternative)
                                            <td>@include('projects.partials.tender_rates_company_tender_tender_alternative_information', ['tenderAlternative'=>$companyTenderTenderAlternative])</td>
                                        @endforeach
                                    @else
                                    <td style="text-align:center;" class="warning" colspan="{{$bsProjectMainInformation->projectStructure->tenderAlternatives->count()}}">
                                        <i class="fa-fw fa fa-exclamation-triangle"></i> {{ trans('general.noRecordsFound') }}
                                    </td>
                                    @endif
                                    </tr>
                                </tbody>
                            </table>

                            @else

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('tenders.tenderAmount') }} :</dt>
                                        <dd>
                                            <strong>
                                                {{{ $project->modified_currency_code }}} {{{ number_format($tender->pivot->tender_amount, 2, ".", ",") }}}
                                            </strong>
                                        </dd>
                                        <dd><hr/></dd>
                                    </dl>
                                    @if ( $tender->pivot->discounted_amount )
                                        <?php $generatedTopHeader = true; ?>

                                        <dl class="dl-horizontal no-margin">
                                            <dt>{{ trans("tenders.discountPercentage") }}:</dt>
                                            <dd>{{{ $tender->pivot->discounted_percentage }}} %</dd>
                                            <dd><hr/></dd>
                                        </dl>

                                        <dl class="dl-horizontal no-margin">
                                            <dt>{{ trans("tenders.discountAmount") }}:</dt>
                                            <dd>{{{ $project->modified_currency_code }}} {{{ number_format($tender->pivot->discounted_amount, 2, ".", ",") }}}</dd>
                                            <dd><hr/></dd>
                                        </dl>
                                    @endif

                                    @if ( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
                                        <?php $generatedTopHeader = true; ?>

                                        <dl class="dl-horizontal no-margin">
                                            <dt>{{ trans("tenders.proposedCompletionPeriod") }}:</dt>
                                            <dd>{{{ $tender->pivot->completion_period  + 0}}} {{{ $tender->project->completion_period_metric }}}</dd>
                                            <dd><hr/></dd>
                                        </dl>

                                        @if(!((float)$tender->pivot->contractor_adjustment_amount))
                                            <dl class="dl-horizontal no-margin">
                                                <dt>{{ trans("tenders.adjustmentPercentage") }}:</dt>
                                                <dd>{{{ number_format($tender->pivot->contractor_adjustment_percentage, 2) }}} %</dd>
                                                <dd><hr/></dd>
                                            </dl>
                                        @endif

                                        @if(!((float)$tender->pivot->contractor_adjustment_percentage))
                                            <dl class="dl-horizontal no-margin">
                                                <dt>{{ trans("tenders.adjustmentAmount") }}:</dt>
                                                <dd>{{{ $project->modified_currency_code }}} {{{ number_format($tender->pivot->contractor_adjustment_amount, 2, ".", ",") }}}</dd>
                                                <dd><hr/></dd>
                                            </dl>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if ( isset($generatedTopHeader) )
                                <hr class="simple">
                            @endif

                            @if( count($tenderAlternativeData) > 0)
                                <br />
                                <div class="row">
                                    <div class="col col-lg-12">
                                        @include('tender_alternatives.table', array(
                                            'data' => $tenderAlternativeData,
                                            'currencySymbol' => $currencySymbol,
                                        ))
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr/>
                    @endif

                    <div class="widget-body">

                        <!-- Anchor -->
                        <a name="{{ trans('tenders.tenderRates') }}"></a>

                        <div class="row">
                            <div class="col col-md-10 col-lg-10">
                                <h5>
                                    {{ trans('tenders.submissionDeadline') }}:
                                    <strong>
                                        {{{ $tender->project->getProjectTimeZoneTime($tender->tender_closing_date) }}}
                                    </strong>
                                </h5>
                            </div>

                            @if($tenderClosed)
                                <div class="col col-md-2 col-lg-2 text-right" style="padding-top:10px;">
                                    <span class="label label-danger">{{ trans('tenders.tenderClosed') }}</span>
                                </div>
                            @endif
                        </div>

                        <hr class="simple">

                        @if ( ! $tenderClosed && (!$tender->callingTenderInformation->disable_tender_rates_submission))
                            <fieldset>
                                <div class="row padded-bottom">
                                    <div class="col col-xs-12 col-md-6 col-lg-6">
                                        <div class="well padded">
                                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form', 'files' => true)) }}
                                        @include('projects.partials.tenderRatesForm')
                                        <footer>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-upload"></i>
                                                {{ trans('tenders.submitTenderRates') }}
                                            </button>
                                        </footer>
                                        {{ Form::close() }}
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        @endif
                    </div>

                    @if(! $tenderClosed && $tender->pivot->isSubmitted())
                    <hr>
                    {{ Form::open(array('class' => 'smart-form', 'id' => 'info-form', 'route' => array('projects.submitTenderRates.information', $project->id, $tender->id))) }}
                    <div class="widget-body">
                        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                            <h1 class="txt-color-blueDark">{{ trans('tenders.updateTenderRatesInformation') }}</h1>
                        </div>
                        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                            <button type="submit" class="btn btn-primary pull-right">
                                <i class="fa fa-save"></i>
                                {{ trans('forms.save') }}
                            </button>
                        </div>
                        <fieldset>
                            <div class="row padded-bottom">
                            @if($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count())
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        @foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
                                            @if($companyTender->tenderAlternatives->count())
                                                @foreach($companyTender->tenderAlternatives as $companyTenderTenderAlternative)
                                                    @if($companyTenderTenderAlternative->tender_alternative_id == $tenderAlternative->id)
                                                    <th>{{ $tenderAlternative->title }}</th>
                                                    @endif
                                                @endforeach
                                            @else
                                            <th>{{ $tenderAlternative->title }}</th>
                                            @endif
                                        @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                        @if($companyTender->tenderAlternatives->count())
                                            @foreach($companyTender->tenderAlternatives as $companyTenderTenderAlternative)
                                                <td>@include('projects.partials.tenderRatesInformationForm', ['tenderAlternative'=>$companyTenderTenderAlternative])</td>
                                            @endforeach
                                        @else
                                        <td style="text-align:center;" class="warning" colspan="{{$bsProjectMainInformation->projectStructure->tenderAlternatives->count()}}">
                                            <i class="fa-fw fa fa-exclamation-triangle"></i> {{ trans('general.noRecordsFound') }}
                                        </td>
                                        @endif
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                @include('projects.partials.tenderRatesInformationForm', ['companyTender'=>$companyTender])
                            @endif
                            </div>
                        </fieldset>
                    </div>
                    {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ( $tender->pivot->isSubmitted() AND ! $tenderClosed )
        @include('projects.partials.submit_rate_attachments_form')
    @endif

    @include('projects.partials.tender_rates_attachments_modal_box', array(
        'project' => $project,
        'tenderer' => $contractor,
        'attachments' => $tender->pivot->attachments,
    ))

    @if($tender->listOfTendererInformation->technical_evaluation_required)
        @include('technical_evaluation.form_modal', [
            'editable' => $tender->technicalEvaluationIsOpen(),
        ])

        @if(isset($setReference))
            @if(!$setReference->attachmentListItems->isEmpty())
                @include('technical_evaluation.attachments.upload_modal', array(
                    'company' => $contractor,
                    'setReference' => $setReference,
                    'editable' => $tender->technicalEvaluationIsOpen(),
                ))
            @endif
        @endif
    @endif

@endsection

@section('js')
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script src="{{ asset('js/summernote-master/dist/summernote.min.js') }}"></script>
    <script type="text/javascript">
        Vue.config.delimiters = ['(%', '%)'];

        var submitTenderForm = new Vue({
            el: '#info-form'
        });

        $.ajax({
            url: "{{ route('projects.submitTender.acknowledgementLetter.checkTenderSubmission', array($project->id, $tender->id)) }}",
            method: 'GET',
            data: null,
            success:function(data){
                if(data.success){
                    $.smallBox({
                        title : "{{ trans('tenders.printNotification') }}",
                        content : "<i class='fa fa-check'></i> <i>{{ trans('tenders.acknowledgementLetterReady') }}.</i>",
                        color : "#739E73",
                        sound: true,
                        timeout : 5000
                    });
                }
            },
            error: function(error){
                console.log(error);
            }
        });

        $('[data-action="view-technical-evaluation-form"]').click(function(){
            $.get("{{ route('technicalEvaluation.formResponses', [$project->id, $tender->id, $contractor->id]) }}", function(data){
                formModal.init(data);
            });
        });
    </script>
@endsection