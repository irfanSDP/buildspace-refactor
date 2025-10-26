@extends('layout.main')
<?php
    $bsProjectMainInformation = $project->getBsProjectMainInformation();

    $hasTenderAlternatives = ($bsProjectMainInformation) ? $bsProjectMainInformation->projectStructure->tenderAlternatives->count() : false;
?>
@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus; ?>

    <?php $isLatestTender = ( $project->latestTender->id === $tender->id ) ? true : false; ?>

    <?php $isAClosed_openTender = ( $tender->isTenderClosed() && $tender->isTenderOpen() ); ?>

    <?php $toPostContract = ( $isLatestTender && !$project->onPostContractStages() && $isEditor && $isAClosed_openTender ) ? true : false; ?>

    <?php $showVerifierLogs = ( $tender->hasBeenReTender() AND !$tender->reTenderVerifierLogs->isEmpty() ) ? true : false; ?>

    <?php $tenderValidUntilDate = \Carbon\Carbon::parse($tender->project->getProjectTimeZoneTime($tender->validUntil()))->format(Config::get('dates.standard_spaced')); ?>

    <?php $canSelectTenderer = $canEdit && ($toPostContract && $awardRecommendation && ($awardRecommendation->status == OpenTenderAwardRecommendationStatus::EDITABLE)) || ($toPostContract && !$awardRecommendation); ?>

    <?php $isEditor = \Confide::user()->isEditor($project);?>

    <?php $canUpdateEarnestMoney = $currentUser->hasCompanyProjectRole($project, PCK\Filters\OpenTenderFilters::editorRoles($project)) && $currentUser->isEditor($project); ?>

    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> Tenderer Rates
            </h1>
        </div>
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
            <!-- Header buttons -->
            <div class="btn-group pull-right header-btn">
                @include('open_tenders.partials.actions_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="well">
                <div class="smart-form">
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label" style="color">{{{ trans('tenders.dateOfCallingTender') }}}</label>
                            <div>{{ $tender->callingTenderInformation ? $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender) : '-' }}</div>
                        </section>
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label" style="color">{{{ trans('tenders.commercialTenderClosingDate') }}}</label>
                            <div>{{ $tender->callingTenderInformation ? $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender) : '-' }}</div>
                        </section>
                    </div>
                    @if($tender->listOfTendererInformation->technical_evaluation_required)
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label" style="color">{{{ trans('tenders.technicalEvaluationApproval') }}}</label>
                                @if($isTechnicalAssessmentFormApproved)
                                <div class="badge label-success">{{{ trans('contractManagement.approved') }}}</div>
                                @else
                                <div class="badge label-warning">{{{ trans('general.pending') }}}</div>
                                @endif
                        </section>
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label" style="color">{{{ trans('tenders.technicalTenderClosingDate') }}}</label>
                            <div>{{ $tender->callingTenderInformation ? $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date) : '-' }}</div>
                        </section>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('tenders.tendererRateListing') }}</h2>
                </header>
                <div>
                    <div class="widget-body {{{ count($tender->selectedFinalContractors) ? 'no-padding' : '' }}}">
                        @if(count($tender->selectedFinalContractors))
                            <div id="tenderers-table"></div>
                        @else
                            <div class="well">
                                {{ trans('general.nothingToSeeHere') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach ($tender->selectedFinalContractors as $contractor)
        @include('projects.partials.tender_rates_attachments_modal_box', array(
            'companyName' => $contractor->name,
            'project' => $project,
            'tenderer' => $contractor,
            'attachments' => $contractor->pivot->attachments,
        ))
    @endforeach

    @include('open_tenders.partials.remarksModal')

    @if ( $isAClosed_openTender )
        @include('open_tenders.partials.tenderValidityPeriodModal')
    @endif

    @if ( $showVerifierLogs )
        <?php $retenderRemarks = (!is_null($tender->request_retender_remarks) && (trim($tender->request_retender_remarks) != '')) ? trim($tender->request_retender_remarks) : null; ?>
        @include('open_tenders.partials.verifier_logs', array(
            'title'          => 'View '.trans("tenders.tenderRevision").' Verifier Logs',
            'logs'           => $tender->reTenderVerifierLogs,
            'messageRemarks' => $retenderRemarks,
        ))
    @endif
@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>

    <script>
        $(document).ready(function() {
            pageSetUp();

            var columns;
            var submittedDateFormatter = function(cell, formatterParams, onRendered){
                var rowData    = cell.getRow().getData();
                var textClass = "text-danger";
                var text = "{{ trans('tenders.notSubmitted') }}";

                if(rowData.submitted_at){
                    textClass = "text-success";
                    text = rowData.submitted_at;
                }
                return "<p class='"+textClass+"'>"+text+"</p>";
            };

            var validateInputFormatter = function(cell, formatterParams, onRendered){
                var rowData = cell.getRow().getData();
                if(rowData.submitted_at){
                   return app_tabulator_utilities.variableHtmlFormatter(cell, formatterParams, onRendered);
                }
            };

            @if(($isAClosed_openTender && !$tender->listOfTendererInformation->technical_evaluation_required) or ($isAClosed_openTender && $tender->listOfTendererInformation->technical_evaluation_required && (!is_null($tender->technicalEvaluation) && $isTechnicalAssessmentFormApproved)))
            columns = [
                @if($canSelectTenderer)
                {title:"", cssClass:"text-center", field: 'id', width: 10, frozen: true, headerSort:false, formatter:validateInputFormatter,
                    formatterParams: {
                        tag: 'input',
                        rowAttributes: {'value': 'id', 'checked': 'is_currently_selected_tenderer'},
                        attributes: {'type': 'radio', 'name': 'contractor'},
                    }},
                @endif
                @if(!$hasTenderAlternatives)
                {title:"{{ trans('general.no') }}", cssClass:"text-center", width: 18, frozen: true, headerSort:false, formatter:"rownum"},
                @endif
                {title:"{{ trans('tenders.tenderer') }}", field: @if($hasTenderAlternatives) 'tender_alternative_title' @else 'tenderer' @endif, cssClass:"text-left", minWidth: 380, frozen: true, headerFilter: "input", headerSort:false, formatter:function(cell, formatterParams){
                    cell.getElement().style.whiteSpace = "pre-wrap";
                    var cellValue = cell.getValue();
                    var rowData = cell.getRow().getData();
                    var textColor = rowData.is_final_selected_tenderer ? '#fd3995' : '#333';

                    if(({{{ $isTechnicalAssessmentFormApproved ? 1 : 0 }}} && rowData.is_shortlisted) || (rowData.is_currently_selected_tenderer && {{ $awardRecommendation && in_array($awardRecommendation->status, [OpenTenderAwardRecommendationStatus::APPROVED]) ? 1 : 0 }})){
                        cell.getRow().getElement().style.backgroundColor = '#b3ffb3';
                    }

                    var awardedIcon = (rowData.is_currently_selected_tenderer) ? '<i class="fa fa-lg fa-trophy text-warning"></i>' : '';
                    var syncIcon = (parseInt(rowData.tenderer_id) > 0) ? '<span id="sync_progress-'+rowData.tenderer_id+'" class="ajax-loading-animation" style="display:none;"><i class="fa fa-sync fa-spin"></i> Syncing...</span>' : '';
                    return this.emptyToSpace(syncIcon+'<p style="text-align:left;color:'+textColor+';padding-bottom:4px;">'+this.sanitizeHTML(cellValue)+' '+awardedIcon+'</p>');
                }},
                {title:"{{ trans('tenders.submittedDate') }}", field: 'submitted_at', cssClass:"text-center", width:160, frozen: true, headerFilter: "input", headerSort:false, formatter:submittedDateFormatter},
                @foreach($includedTenderAlternatives as $index => $includedTenderAlternative)
                {title:"{{{ \PCK\FormOfTender\TenderAlternative::getTenderAlternativeLabel($index+1) }}}", cssClass: 'text-nowrap', columns:[
                    {title:"{{ trans('tenders.amount') }} ({{{ $project->modified_currency_code }}})", field: "tender_alternative_{{{ $index }}}_amount", width:120, cssClass:"text-right", align:"center", headerSort:false, formatter: function(cell){
                        var rowData = cell.getRow().getData();
                        var cellValue = cell.getValue();

                        if({{{ $isTechnicalAssessmentFormApproved ? 1 : 0 }}} && !rowData.is_shortlisted){
                            return "-";
                        }

                        return cellValue;
                    }},
                    {title:"{{{ $project->completion_period_metric }}}", field: "tender_alternative_{{{ $index }}}_period", cssClass:"text-center", width: 80, align:"center", headerSort:false},
                ]},
                @endforeach
                {title:"{{ trans('tenders.contractorsOwnCompletionPeriod') }}", columns:[
                    {title:"{{{ $project->completion_period_metric }}}", field: 'contractors_completion_period', width:80, cssClass:"text-center", headerSort:false},
                    {title:"{{ trans('tenders.contractorsAdjustment') }}", field: 'contractors_adjustment', width:160, cssClass:"text-right", headerSort:false},
                ]},
                {title:"{{ trans('tenders.earnestMoney') }}", cssClass:"text-center", width:120, headerSort:false, formatter:validateInputFormatter,
                    formatterParams: {
                        tag: 'input',
                        rowAttributes: {'data-id': 'id', 'checked': 'earnest_money'},
                        attributes: {'type': 'checkbox', 'class': 'earnest_money', 'name': 'earnest_money', {{{ !$canUpdateEarnestMoney ? "disabled:1" : "" }}} },
                    }
                },
                {title:"{{ trans('general.remarks') }}", cssClass:"text-center", width:280, headerSort:false, formatter:validateInputFormatter,
                    formatterParams: {
                        tag: 'div',
                        rowAttributes: {'data-id': 'id', },
                        attributes: {'class': 'fill tenderer_remarks', 'data-type': 'remarks_view', 'data-tooltip': 'data-tooltip', 'title': "{{ trans('forms.remarks') }}", 'data-placement': 'left', 'data-action': 'remark_input_toggle'},
                        innerHtml: function(rowData){
                            var defaultTxt = @if($canUpdateEarnestMoney) 'Click to enter remarks' @else '' @endif;
                            return rowData.remarks.length ? rowData.remarks : defaultTxt;
                        },
                    }
                },
                {title:"{{ trans('general.attachments') }}", field: 'attachments', width:120, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        innerHtml: function(rowData){
                            return '<a href="#" data-toggle="modal" data-target="#submitTender-'+rowData.tenderer_id+'" class="btn btn-xs btn-success">'+rowData.attachments_count+'</a>';
                        }
                    }
                },
                {title:"{{ trans('formOfTender.formOfTender') }}", width:120, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        tag: 'a',
                        innerHtml: '<i class="fa fa-print"></i>',
                        rowAttributes: {'href': 'form_of_tender_print_route'},
                        attributes: {'target': '_blank', 'rel': 'tooltip', 'title': "{{ trans('general.print') }}", 'class': 'btn btn-xs btn-warning'},
                    }
                },
                {title:"{{ trans('tenders.tenderRates') }}", width:100, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        tag: 'a',
                        innerHtml: '<i class="far fa-file-archive"></i>',
                        rowAttributes: {'href': 'tender_rates'},
                        attributes: {'rel': 'tooltip', 'title': "{{ trans('general.download') }}", 'class': 'btn btn-xs btn-default'},
                    }
                },
                {title:"{{ trans('tenders.contractorsDiscount') }} ({{{ $project->modified_currency_code }}})", field: 'contractors_discount', width:180, cssClass:"text-right", headerSort:false},
                {title:"{{ trans('tenders.projectIncentive') }}", field: 'project_incentive', width:140, cssClass:"text-right", headerSort:false},
            ];
            @else
            columns = [
                {title:"{{ trans('general.no') }}", cssClass:"text-center", width:18, frozen: true, headerSort:false, formatter:"rownum"},
                {title:"{{ trans('tenders.tenderer') }}", field: 'tenderer', cssClass:"text-left", minWidth:280, headerFilter: "input", headerSort:false},
                {title:"{{ trans('tenders.submittedDate') }}", cssClass:"text-center", width:160, headerFilter: "input", headerSort:false, formatter:submittedDateFormatter},
            ];
            @endif

            var tenderersTable = new Tabulator("#tenderers-table", {
                data: webClaim.tendererData,
                placeholder: "{{ trans('tenders.noTenderers') }}",
                
                @if($hasTenderAlternatives)
                groupBy:function(data){
                    return data.tenderer_id;
                },
                groupHeader:function(value, count, data, group){
                    var title = data.find(x => parseInt(x.tenderer_id) === parseInt(value)).tenderer;
                    return title + "<span class='text-error' style='margin-left:10px;'>( " + count + " Tender Alternatives )</span>";
                },
                @endif

                @if ( $isAClosed_openTender )
                footerElement:'<a href="{{route('projects.openTender.record', array($project->id, $tender->id))}}" target="_blank" class="btn btn-success"><i class="fa fa-print"></i>&nbsp;{{ trans('general.print') }}</a>',
                @endif
                columns: columns,
                layout: 'fitColumns',
                height: 520,
                renderComplete:function(){

                    @if($project->canSyncBuildSpaceContractorRates())
                    syncRatesProgress(this);
                    @endif

                    @if($canSelectTenderer)
                        var tableData = this.getData();
                        for(i in tableData){
                            if(tableData[i]['is_currently_selected_tenderer']){
                                $('#btnAwardRecommendation').removeClass('disabled');
                                break;
                            }
                        }

                        //submit new value on selection
                        $("input[name=contractor][type=radio]").on("change", function(e){
                            e.preventDefault();
                            if($(this).length && $(this).prop("checked")){
                                $('#progressBarModal').modal('show');
                                var values = $(this).val().split("_");
                                $.ajax({
                                    url: "{{ route('projects.openTender.currentlySelectedTenderer.save', [$project->id, $tender->id]) }}",
                                    method: 'POST',
                                    data: {
                                        _token: '{{{ csrf_token() }}}',
                                        projectId: '{{{ $project->id }}}',
                                        tenderId: '{{{ $tender->id }}}',
                                        contractorId: parseInt(values[0]),
                                        tenderAlternativeId: parseInt(values[1])
                                    },
                                    success: function(data){
                                        $('#progressBarModal').modal('hide');
                                        if(data['success']) {
                                            $('#btnAwardRecommendation').removeClass('disabled');

                                            if (data['showEnableEbidding']) {
                                                $('.divider.enableEbidding').show();
                                                $('.btn.enableEbidding').show();
                                            }
                                        }
                                    },
                                    error: function(jqXHR,textStatus, errorThrown ){
                                        $('#progressBarModal').modal('hide');
                                    }
                                });
                            }
                        });
                    @endif

                    @if($canUpdateEarnestMoney)
                        $("input[name=earnest_money][type=checkbox]").on("change", function(e){
                            e.preventDefault();

                            if($(this).length && $(this).data('id')){
                                var values = $(this).data('id').split("_");
                                $('#progressBarModal').modal('show');
                                $.ajax({
                                    url: '{{ route('projects.openTender.submitTenderRate.earnestMoney.update', array($project->id, $tender->id)) }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{{ csrf_token() }}}',
                                        data: {
                                            id: parseInt(values[0]),
                                            taid: parseInt(values[1]),
                                            earnestMoney: $(this).prop("checked")
                                        }
                                    },
                                    success: function(data){
                                        $('#progressBarModal').modal('hide');
                                    },
                                    error: function(jqXHR,textStatus, errorThrown ){
                                        $('#progressBarModal').modal('hide');
                                    }
                                });
                            }
                        });

                        $('.tenderer_remarks[data-action=remark_input_toggle]').on('click', function(e){
                            e.preventDefault();

                            var saveButton =$('#remarks-save-button');
                            saveButton.removeData('id');
                            saveButton.attr('data-id', $(this).data('id'));

                            //populate the textarea with current remark
                            var textView = $('[data-type=remarks_view][data-id='+$(this).data('id')+']');
                            var currentRemarks = textView.text().trim();
                            if(currentRemarks.toLowerCase() == 'click to enter remarks'){
                                currentRemarks = "";
                            }
                            var textArea = $('#remarks-input');
                            textArea.val(currentRemarks);

                            //show modal
                            $('#remarkInputModal').modal('show');
                        });

                        $('#remarks-save-button').on('click', function(e){
                            e.preventDefault();
                            var remarks = $('#remarks-input').val();
                            var values = $(this).data('id').split("_");
                            var textView = $('[data-type=remarks_view][data-id='+$(this).data('id')+']');
                            $.ajax({
                                url: '{{ route('projects.openTender.submitTenderRate.remarks.update', array($project->id, $tender->id)) }}',
                                method: 'POST',
                                data: {
                                    _token: '{{{ csrf_token() }}}',
                                    data: {
                                        id: parseInt(values[0]),
                                        taid: parseInt(values[1]),
                                        remarks: remarks.trim()
                                    }
                                },
                                success: function(data){
                                    $('#remarkInputModal').modal('hide');
                                    //update the view
                                    textView.text(remarks);
                                },
                                error: function(jqXHR,textStatus, errorThrown ){
                                    $('#remarkInputModal').modal('hide');
                                }
                            });
                        });
                    @endif
                }
            });

            $('#add-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });
        });

        @if($project->canSyncBuildSpaceContractorRates())
        function syncRatesProgress(table){
            $.ajax({
                url: "{{ getenv('BUILDSPACE_URL') }}syncRatesProgress",
                method: 'GET',
                data: {
                    id: '{{{ $project->id }}}'
                },
                success: function(data){
                    if(parseInt(data.id) === {{$project->id}} && data.syncing){
                        $('span[id^="sync_progress-"]').filter(
                            function(){
                                var id = this.id.split('-').pop();
                                if(data.companies.includes(parseInt(id))){
                                    $("#sync_progress-"+id).show("slow");
                                }else{
                                    $("#sync_progress-"+id).hide("slow");
                                }
                            }
                        );

                        $(".sync-buildspace-contractor-rates").hide();
                        setTimeout(function(){syncRatesProgress(table);}, 5000);
                    }else{
                        $('span[id^="sync_progress-"]').filter(
                            function(){
                                $(this).hide();
                            }
                        );
                        $(".sync-buildspace-contractor-rates").show();
                    }
                },
                error: function(jqXHR,textStatus, errorThrown ){
                }
            });
        }
        @endif

        @if($isAClosed_openTender)
        new Vue({
            el: '#tenderValidityPeriodModal',

            data: {
                dateView: '{{{ $tenderValidUntilDate }}}'
            },

            methods: {
                onKeyUp: function(){
                    var tenderClosingDate = '{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($tender->tender_closing_date))->format(Config::get('dates.standard_spaced')) }}}';
                    this.dateView = moment(tenderClosingDate, "DD / MM / YYYY").add(this.numberOfDays, 'days').format('DD / MM / YYYY');
                }
            }
        });
        @endif

        $('[data-tooltip]').tooltip();
    </script>
@endsection