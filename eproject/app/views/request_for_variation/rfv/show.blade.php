@extends('layout.main')
<?php $isVerifierWithoutProjectAccess = isset($isVerifierWithoutProjectAccess) ? $isVerifierWithoutProjectAccess : false; ?>
@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        @if( ! $isVerifierWithoutProjectAccess )
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ link_to_route('requestForVariation.index', trans('requestForVariation.requestForVariation'), [$project->id]) }}</li>
        @endif
        <li>{{ trans('requestForVariation.requestForVariationForm') }}</li>
	</ol>
	@include('projects.partials.project_status')
@endsection
<?php use \PCK\RequestForVariation\RequestForVariation as RequestForVariation; ?>
@if($requestForVariation)
<?php $uploadedFilesRoute    = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.requestForVariation.uploaded.files.get', [$project->id, $requestForVariation->id]) : route('requestForVariation.uploaded.files.get', [$project->id, $requestForVariation->id]); ?>
<?php $costEstimateListRoute = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.requestForVariation.cost.estimate.list', [$project->id, $requestForVariation->id]) : route('requestForVariation.cost.estimate.list', [$project->id, $requestForVariation->id]); ?>
<?php $actionLogsRoute       = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.requestForVariation.logs.get', [$project->id, $requestForVariation->id]) : route('requestForVariation.logs.get', [$project->id, $requestForVariation->id]); ?>
@endif
<?php $rfvSubmitRoute        = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.requestForVariation.submit', [$project->id]) : route('requestForVariation.submit', [$project->id]); ?>
@section('css')
    @if($canUserUploadDeleteFiles)
        <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
        <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
        <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
    @endif
@endsection

@section('content')
<?php $exceededLimit = ($showKpiLimitTable && ($currentKpiLimit > $maxKpiLimit)); ?>
<section id="widget-grid" class="">
    <div class="row">
        <article class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget" id="request_for_variation-main_content" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false" data-widget-collapsed="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-fw fa-list"></i></span>
                    <h2>{{ trans('requestForVariation.requestForVariation') }} - {{{ (isset($requestForVariation) && $requestForVariation) ? $requestForVariation->getStatusText() : RequestForVariation::STATUS_NEW_RFV_TEXT}}}</h2>
                </header>
                <div class="no-padding">
                    <ul id="toDoListTab" class="nav nav-tabs">
                        <li class="active">
                            <a href="#requestForVariationFormTabContent" data-toggle="tab" id="requestForVariationTarget"><i class="far fa-fw fa-lg fa-file"></i> {{ trans('requestForVariation.rfvDetails') }}</a>
                        </li>
                        @if ($requestForVariation)
                        <li>
                            <a href="#costEstimateTabContent" data-toggle="tab" id="costEstimateTarget"><i class="far fa-fw fa-lg fa-list-alt"></i> {{ trans('requestForVariation.costEstimate') }}</a>
                        </li>
                        @endif
                    </ul>
                    <div id="requestForVariationTabContentPane" class="tab-content padding-10" style="height: 100%;">
                        <div class="tab-pane fade in active" id="requestForVariationFormTabContent">
                            <div class="widget-body">
                                <form id="rfvForm" action="{{ $rfvSubmitRoute }}" method="POST" class="smart-form">
                                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                    @if ($requestForVariation)
                                        <input type="hidden" name="requestForVariationId" value="{{{ $requestForVariation->id }}}">
                                        <input type="hidden" name="requestForVariationStatusId" value="{{{ $requestForVariation->status }}}">
                                    @endif
                                    @include('request_for_variation.rfv.partials.rfvFormDesign')
                                    <footer>
                                        <div class="pull-right">
                                            @if($isVerifierWithoutProjectAccess)
                                            {{ link_to_route('home.index', trans('forms.back'), [], array('class' => 'btn btn-default')) }}
                                            @else
                                            {{ link_to_route('requestForVariation.index', trans('forms.back'), [$project->id], array('class' => 'btn btn-default')) }}
                                            @endif

                                            @if($requestForVariation)
                                                <button type="button" class="btn btn-info" id="btnShowRfvActionLogs" data-toggle="modal" data-target="#rfvCommitmentStatusLogModal"><i class="fa fa-search"></i> {{trans('requestForVariation.viewLogs')}}</button>
                                            @endif

                                            @if($requestForVariation && $editableCostEstimate)
                                                <button type="button" class="btn btn-danger" data-action="form-submit" data-target-id="delete-form" data-intercept="confirmation"><i class="fa fa-trash"></i> {{ trans('forms.delete') }}</button>
                                            @endif

                                            @if ($canApprovePendingVerification)
                                                <button type="button" class="btn btn-danger" name="rfv_confirmOmissionAdditionAmount_reject" data-toggle="modal" data-target="#rfvVerifierRejectRemarksModal"><i class="fa fa-times-circle"></i> {{trans('forms.reject')}}</button>
                                                <button type="button" class="btn btn-success" name="rfv_confirmOmissionAdditionAmount_confirm" data-toggle="modal" data-target="#rfvVerifierApproveRemarksModal"><i class="fa fa-check-circle"></i> {{trans('forms.confirm')}}</button>
                                            @elseif ($canVerifyPendingApproval)
                                                <button type="button" class="btn btn-danger" name="rfv_verification_reject" data-toggle="modal" data-target="#rfvVerifierRejectRemarksModal"><i class="fa fa-times-circle"></i> {{trans('forms.reject')}}</button>
                                                <button type="button" class="btn btn-success" name="rfv_verification_confirm" data-toggle="modal" data-target="#rfvVerifierApproveRemarksModal"><i class="fa fa-check-circle"></i> {{trans('forms.confirm')}}</button>
                                                @if (\PCK\Verifier\Verifier::canInitiateForumThread($currentUser, $requestForVariation) && ( ! $isVerifierWithoutProjectAccess ))
                                                    <button type="button" class="btn btn-warning" data-action="form-submit" data-target-id="rfvThreadForm"><i class="fa fa-comment"></i> {{ trans('verifiers.comments') }}</button>
                                                @endif
                                            @elseif ($canAssignVerifiers)
                                                <?php
                                                    $title = $exceededLimit ? trans('general.warning') : trans('general.confirmation');
                                                    $message = $exceededLimit ? (trans('requestForVariation.kpiLimitExceededMessage') . ' ' . trans('general.stillWantToProceed')) : trans('general.submitWithoutVerifier');
                                                ?>
                                                <button type="submit" id="btnSubmit" class="btn btn-success" data-intercept="confirmation" @if(!$exceededLimit) data-intercept-condition="noVerifier" @endif data-confirmation-message="{{{ $message }}}" data-confirmation-title="{{{ $title }}}"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
                                            @elseif (!$requestForVariation || $editableCostEstimate)
                                                <button type="submit" id="btnSubmit" class="btn btn-success" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
                                            @endif

                                            @if($requestForVariation && ( ! $isVerifierWithoutProjectAccess ))
                                                @include('forum.partials.object_thread_link', array('object' => $requestForVariation))
                                            @endif
                                        </div>
                                    </footer>
                                </form>
                                @if($requestForVariation)
                                    {{ Form::open(['id' => 'delete-form', 'route' => ['requestForVariation.delete', $project->id, $requestForVariation->id], 'method' => 'POST', 'hidden' => 'hidden']) }}
                                        <input name="_method" value="delete"/>
                                    {{ Form::close() }}
                                @endif
                            </div>
                        </div>
                        @if ($requestForVariation)
                            <div class="tab-pane fade in" id="costEstimateTabContent">
                                <div class="widget-body">
                                    @include('request_for_variation.rfv.partials.cost_variation')
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- end tabs -->
                    <br />
                </div>
            </div>
        </article>
    </div>
</section>

@if ($requestForVariation && \PCK\Verifier\Verifier::canInitiateForumThread($currentUser, $requestForVariation))
    {{ Form::open(array('route' => array('approval.forum.threads.initialise', $project->id), 'id' => 'rfvThreadForm', 'hidden' => true)) }}
        <input type="hidden" name="object_id" value="{{ $requestForVariation->id }}"/>
        <input type="hidden" name="object_type" value="{{ get_class($requestForVariation) }}"/>
    {{ Form::close() }}
@endif

@if($canVerifyPendingApproval || $canApprovePendingVerification)
    @include('request_for_variation.rfv.partials.rfv_verifier_remark_modal')
@endif

@if($canUserUploadDeleteFiles)
    @include('templates.uploadModal')
    @include('layout.file_upload.template-upload')
@endif

@if ($requestForVariation)
    @include('request_for_variation.rfv.partials.rfv_action_log_modal')
@endif

@if($editableCostEstimate)
    @include('request_for_variation.rfv.cost_estimates_import_modal')
@endif

@if($showKpiLimitTable)
    @include('request_for_variation.rfv.partials.kpi_limit_exceeded_modal')
@endif

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>

    @if($canUserUploadDeleteFiles)
        <script src="{{ asset('js/jquery_file_upload/tmpl.min.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/load-image.all.min.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/canvas-to-blob.min.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.iframe-transport.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-process.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-image.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-audio.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-video.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-validate.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-ui.js') }}"></script>
    @endif
    <script>
        $(document).ready(function() {
            'use strict';

            var token = $('meta[name=_token]').attr("content");

            @if($canUserUploadDeleteFiles)
                $('#uploadDocumentModal').modal({
                    keyboard: false,
                    show: false
                });

                // Initialize the jQuery File Upload widget:
                $('#fileupload').fileupload({
                    url: '{{ route("requestForVariation.document.upload", array($project->id, $requestForVariation->id)) }}',
                    formData: {
                        _token :token
                    },
                    maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                    maxChunkSize: null, // 10 MB
                    // Enable image resizing, except for Android and Opera,
                    // which actually support image resizing, but fail to
                    // send Blob objects via XHR requests:
                    disableImageResize: /Android(?!.*Chrome)|Opera/
                            .test(window.navigator.userAgent)
                })
                .bind('fileuploaddone', function (e, data){
                    $('#rfvUploadedFilesTable').DataTable().draw();
                })
                .bind('fileuploaddestroyed', function (e, data){
                    $('#rfvUploadedFilesTable').DataTable().draw();
                });
            @endif

            @if ($requestForVariation)
                $('#rfvUploadedFilesTable').DataTable({
                    "sDom": "tpi",
                    "autoWidth" : false,
                    scrollCollapse: true,
                    "iDisplayLength":10,
                    bServerSide:true,
                    "ordering": false,
                    "language": {
                        "infoFiltered": "",
                        "zeroRecords": "No files uploaded"
                    },
                    "sAjaxSource":"{{ $uploadedFilesRoute }}",
                    "fnServerParams": function ( aoData ) {
                        aoData.push( { name: 'requestForVariation', value: "{{{ $requestForVariation->id }}}" } );
                    },
                    "aoColumnDefs": [{
                        "aTargets": [ 0 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            return '<a href="' + source['download_route'] + '">' + source['fileName'] + '</a>';
                        },
                        "sClass": "text-left text-center text-nowrap squeeze"
                    },{
                        "aTargets": [ 1 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            return source['uploader'];
                        },
                        "sClass": "text-center text-nowrap squeeze"
                    },{
                        "aTargets": [ 2 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            var fileDeleteButton = "";
                            if (source['canUserDeleteFile']) {
                                fileDeleteButton = '<div>'
                                    + '<button type="button" data-action="deleteFile" data-url="' + source['delete_route'] + '" type="tooltip" class="btn btn-xs btn-danger">'
                                    + '<i class="fa fa-times"></i>'
                                    + '</div>';
                            }
                            return fileDeleteButton;
                        },
                        "sClass": "text-center text-nowrap squeeze"
                    }]
                });
            @endif
        });

        $(document).on('click', '#btnUploadFiles', function(e) {
            $('#uploadDocumentModal').modal('show');
        });

        function deleteUpload(url){
            $.post(url, {
                 '_token': $('meta[name=_token]').attr("content")
             })
             .done(function(data) {
                 // code for result
             })
             .fail(function(data) {
                 // handle failed request
             });
        }

        $(document).on('click', '[data-action="deleteFile"]', function (e) {
            app_progressBar.toggle();
            $.ajax({
                url: $(this).data('url'),
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}'
                },
                success: function (data) {
                    var table = $('#rfvUploadedFilesTable');
                    table.DataTable().draw();
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                    app_progressBar.reset();
                }
            });
        });

        @if($canVerifyPendingApproval || $canApprovePendingVerification)
        $('button#verifier_approve_rfv-submit_btn, button#verifier_reject_rfv-submit_btn').on('click', function(){
            $(this).prop('disabled', true);

            var remarksId;

            switch($(this).attr('id')){
                case 'verifier_approve_rfv-submit_btn':
                    var input = $("<input>")
                    .attr("type", "hidden")
                    .attr("name", "approve").val(1);
                    $('#rfvForm').append(input);
                    remarksId = 'approve_verifier_remarks';
                    break;
                case 'verifier_reject_rfv-submit_btn':
                    remarksId = 'reject_verifier_remarks';
                    break;
            }

            if($('#'+remarksId)){
                $('#rfvForm').append($("<input>")
                .attr("type", "hidden")
                .attr("name", "remarks").val($('#'+remarksId).val()));
            }

            $('#rfvForm').submit();
        });
        @endif

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();
            return !input.some(function(element){
                return (element.value > 0);
            });
        }

        @if($exceededLimit)
            $('#btnSubmit').on('click', function(e) {
                if(noVerifier(e)) {
                    $('#kpiLimitExceededModal').modal('show');
                    return false;
                }
            });
        @endif

        @if($requestForVariation)
            $('#rfvCommitmentStatusLogModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                var modal = $(this);
                modal.find('#action_log-content ol').empty();
                modal.find('#action_log-content .message').empty();

                $.ajax({
                    url: "{{ $actionLogsRoute }}",
                    success: function(data){
                        var logEntry;
                        var logText;
                        var updatedAt;
                        var remarks;

                        if(data.length < 1){
                            modal.find('#action_log-content .message').append('<div class="alert alert-warning fade in">{{ trans('requestForVariation.noRecordInActionLog') }}</div>');
                        }

                        for(var dataIndex in data){
                            logText = '<span style="color:blue">' + data[dataIndex].formattedLog + '</span>';
                            updatedAt = '<span style="color:red">' + data[dataIndex].formattedDateTime + '</span>';
                            remarks = (data[dataIndex].remarks == undefined) ? '' : '<span style="color:black"> {{ trans('requestForVariation.remarks') }} : ' + data[dataIndex].remarks + '</span>';
                            logEntry = logText + updatedAt + remarks;
                            modal.find('#action_log-content ol').append('<li style="margin: 0 0 0.5rem 0;">' + logEntry + '</li>');
                        }
                    }
                });
            });
        @endif

        @if($requestForVariation)
            var typeFormatter = function(cell, formatterParams){
                var data = cell.getRow().getData();
                switch (parseInt(data.type)) {
                    case {{{\PCK\Buildspace\VariationOrderItem::TYPE_HEADER}}}:
                        var headerNumber = isNaN(parseInt(data.level)) ? 1 : parseInt(data.level) + 1;
                        cellValue = '{{{\PCK\Buildspace\VariationOrderItem::TYPE_HEADER_TEXT}}}&nbsp;'+headerNumber;
                        break;
                    default:
                        cellValue = '{{{\PCK\Buildspace\VariationOrderItem::TYPE_WORK_ITEM_TEXT}}}';
                        break;
                }
                return cellValue;
            };

            var unitFormatter = function(cell, formatterParams){
                var data = cell.getRow().getData();
                var val;
                switch (parseInt(data.type)) {
                    case {{{\PCK\Buildspace\VariationOrderItem::TYPE_HEADER}}}:
                        val = "";
                        break;
                    default:
                        val = data.uom_symbol;
                        break;
                }
                return val;
            };

            var descriptionFormatter = function(cell, formatterParams){
                cell.getElement().style.whiteSpace = "pre-wrap";
                var data = cell.getRow().getData();
                switch (parseInt(data.type)) {
                    case {{{\PCK\Buildspace\VariationOrderItem::TYPE_HEADER}}}:
                        cell.getElement().style.fontWeight = "bold";
                        break;
                    default:
                        cell.getElement().style.fontWeight = "normal";
                        break;
                }
                return this.emptyToSpace(this.sanitizeHTML(cell.getValue()));
            };

            var customMoneyFormatter = function(cell, formatterParams, onRendered) {
                var floatVal = parseFloat(cell.getValue()),number,integer,decimal,rgx;
                var decimalSym = formatterParams.decimal || ".";
                var thousandSym = formatterParams.thousand || ",";
                var symbol = formatterParams.symbol || "";
                var after = !!formatterParams.symbolAfter;
                var precision = typeof formatterParams.precision !== "undefined" ? formatterParams.precision : 2;
                if (isNaN(floatVal) || floatVal == 0) {
                    return this.emptyToSpace();
                }
                var fixedPrecisionNumber = precision !== false ? floatVal.toFixed(precision) : floatVal;
                number = String(fixedPrecisionNumber).split(".");
                integer = number[0];
                decimal = number.length > 1 ? decimalSym + number[1] : "";
                rgx = /(\d+)(\d{3})/;
                var absInt = Math.abs(integer);
                while (rgx.test(absInt)) {
                    absInt = absInt.toString().replace(rgx, "$1" + thousandSym + "$2");
                }
                var ret;
                if(fixedPrecisionNumber > 0){
                    cell.getElement().style.color = "";
                    ret = absInt + decimal;
                }else{
                    cell.getElement().style.color = "red";
                    ret = "("+absInt + decimal+")";
                }
                return after ? ret + symbol : symbol + ret;
            };

            @if($editableCostEstimate)
            var descriptionEditor = function(cell, onRendered, success, cancel){

                var cellValue = cell.getValue();
                input = document.createElement("textarea");

                input.style.resize = 'none';
                input.style.padding = "5px";
                input.style.width = "100%";
                input.style.overflow = 'hidden';
                input.style.boxSizing = "border-box";
                input.value = cellValue;

                onRendered(function(){
                    input.focus();
                    input.style.height = "100%";
                });

                function autoGrow(){
                    var scrollHeight = input.scrollHeight;

                    input.style.height =  scrollHeight + 'px';

                    cell.getRow().normalizeHeight();
                }

                function onChange(){
                    if(input.value != cellValue){
                        success(input.value);
                    }else{
                        cancel();
                    }
                }

                input.addEventListener("change", onChange);
                input.addEventListener("blur", onChange);
                input.addEventListener("keydown", function(e){
                    switch(e.keyCode){
                        case 13:
                            onChange();
                            break;
                        case 27:
                            cancel();
                            break;
                    }
                });

                $(input).on('input', function() {
                    autoGrow();
                });

                return input;
            };

            var editCheck = function(cell){
                var data = cell.getRow().getData();
                return (data.type=={{{\PCK\Buildspace\VariationOrderItem::TYPE_WORK_ITEM}}});
            }

            var columns = [{
                title:'&nbsp;', align:"center",
                columns: [{
                    title:'<div class="text-center">No.</div>', field: "id", formatter:"rownum", align:"center", width:40, resizable:false, headerSort:false
                },{
                    title:'<div class="text-center">{{ trans('requestForVariation.billRef') }}</div>', formatter:descriptionFormatter, field:"bill_ref", align:"center", width:100, visible: true, variableHeight:true, resizable:false, headerSort:false, editor:descriptionEditor
                },{
                    title:"{{ trans('requestForVariation.description') }}", formatter:descriptionFormatter, field:"description", visible: true, variableHeight:true, resizable:false, headerSort:false, editor:descriptionEditor
                },{
                    title:'<div class="text-center">{{ trans('requestForVariation.type') }}</div>', field:"type", width:120, align:"center", resizable:false, headerSort:false, formatter:typeFormatter, editor:"select", editorParams:{values:<?php echo json_encode($voItemTypes); ?>}
                },{
                    title:'<div class="text-center">{{ trans('requestForVariation.unit') }}</div>', field:"uom_id", width:120, align:"center", resizable:false, headerSort:false, formatter:unitFormatter, editor:"select", editable:editCheck, editorParams:{values:<?php echo json_encode($unitOfMeasurements); ?>}
                }]
            },{
                title:'<div class="text-center">{{ trans('requestForVariation.budget') }}</div>', align:"center",
                columns:[{
                    title:'<div class="text-right">{{ trans('requestForVariation.rate') }}</div>', formatter:customMoneyFormatter, field:"reference_rate", align:"right", width:120, resizable:false, headerSort:false, editor: "input", editable:editCheck
                },{
                    title:'<div class="text-right">{{ trans('requestForVariation.qty') }}</div>', formatter:customMoneyFormatter, field:"reference_quantity", align:"right", width:120, resizable:false, headerSort:false, editor: "input", editable:editCheck
                },{
                    title:'<div class="text-right">{{ trans('requestForVariation.total') }}</div>', formatter:customMoneyFormatter, field:"reference_amount", align:"right", width:120, resizable:false, headerSort:false
                }]
            },{
                title:'&nbsp;', align:"center",
                columns: [{
                    title:'<div class="text-center">{{ trans('general.remarks') }}</div>', formatter: 'textarea', field: "remarks", width: 350, resizable: false, headerSort: false, editor: 'textarea', editable:editCheck
                }]
            }];

            var formMoneyFormatter = function(value, elemName) {
                var elem = $(elemName);
                var floatVal = parseFloat(value),number,integer,decimal,rgx;
                var decimalSym = ".";
                var thousandSym = ",";
                var symbol = "{{{$project->getModifiedCurrencyCodeAttribute($project->modified_currency_code)}}}";
                var precision = 2;
                if (isNaN(floatVal) || floatVal == 0) {
                    elem.html(symbol + ' 0.00');
                }
                number = precision !== false ? floatVal.toFixed(precision) : floatVal;
                number = String(number).split(".");
                integer = number[0];
                decimal = number.length > 1 ? decimalSym + number[1] : "";
                rgx = /(\d+)(\d{3})/;
                var absInt = Math.abs(integer);
                while (rgx.test(absInt)) {
                    absInt = absInt.toString().replace(rgx, "$1" + thousandSym + "$2");
                }
                var ret;
                if(parseInt(integer) > 0){
                    elem.css('color', '#666');
                    ret = absInt + decimal;
                }else{
                    elem.css('color', 'red');
                    ret = "("+absInt + decimal+")";
                }
                elem.html(symbol +" "+ret);
            };
            @else
            var columns = [{
                title:'&nbsp;', align:"center",
                columns: [{
                    title:'<div class="text-center">No.</div>', field: "id", formatter:"rownum", align:"center", width:40, resizable:false, headerSort:false
                },{
                    title:'<div class="text-center">{{ trans('requestForVariation.billRef') }}</div>', formatter:descriptionFormatter, field:"bill_ref", align:"center", width:100, visible: true, variableHeight:true, resizable:false, headerSort:false
                },{
                    title:"{{ trans('requestForVariation.description') }}", formatter:descriptionFormatter, field:"description", visible: true, variableHeight:true, resizable:false, headerSort:false
                },{
                    title:'<div class="text-center">{{ trans('requestForVariation.type') }}</div>', field:"type", width:120, align:"center", resizable:false, headerSort:false, formatter:typeFormatter
                },{
                    title:'<div class="text-center">{{ trans('requestForVariation.unit') }}</div>', field:"uom_id", width:120, align:"center", resizable:false, headerSort:false, formatter:unitFormatter
                }]
            },{
                title:'<div class="text-center">{{ trans('requestForVariation.budget') }}</div>', align:"center",
                columns:[{
                    title:'<div class="text-right">{{ trans('requestForVariation.rate') }}</div>', formatter:customMoneyFormatter, field:"reference_rate", align:"right", width:120, resizable:false, headerSort:false
                },{
                    title:'<div class="text-right">{{ trans('requestForVariation.qty') }}</div>', formatter:customMoneyFormatter, field:"reference_quantity", align:"right", width:120, resizable:false, headerSort:false
                },{
                    title:'<div class="text-right">{{ trans('requestForVariation.total') }}</div>', formatter:customMoneyFormatter, field:"reference_amount", align:"right", width:120, resizable:false, headerSort:false
                }]
            },{
                title:'&nbsp;', align:"center",
                columns: [{
                    title:'<div class="text-center">{{ trans('general.remarks') }}</div>', formatter: 'textarea', field: "remarks", width: 350, resizable: false, headerSort: false
                }]
            }];
            @endif

            var table = new Tabulator("#cost_estimate-table", {
                ajaxURL:"{{ $costEstimateListRoute }}",
                height:"500px",
                responsiveLayout:true,
                layout:"fitColumns",
                selectable:1,
                columns:columns
                @if($editableCostEstimate)
                ,cellEdited:function(cell){
                    var row = cell.getRow();
                    var item = row.getData();
                    var field = cell.getField();
                    var value = cell.getValue();
                    var table = cell.getTable();

                    var prevRow = row.getPrevRow();
                    var prevItemId = (prevRow) ? prevRow.getData().id : -1;
                    var itemID = parseInt(item.id);
                    var url = itemID > 0 ?  "{{ route('requestForVariation.cost.estimate.update', [$project->id]) }}" : "{{ route('requestForVariation.cost.estimate.add', [$project->id]) }}";
                    var rowIdx = row.getIndex();

                    table.modules.ajax.showLoader();

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {id:item.id, field: field, val:value,  rfv_id: {{{$requestForVariation->id}}}, prev_item_id:prevItemId, _token:'{{{csrf_token()}}}'},
                        dataType: "json",
                        success: function(data){
                            table.deselectRow();
                            if(itemID > 0){
                                table.updateData(data.items);
                                row.reformat();
                            }else{
                                table.addRow(data.item, true, rowIdx)
                                .then(function(row){
                                    table.selectRow(rowIdx);
                                    table.updateData(data.items);
                                    var rows = table.searchRows("priority", "=", -1);
                                    rows.forEach(function(r){
                                        r.reformat();
                                    });
                                })
                                .catch(function(error){
                                    table.deselectRow();
                                });
                            }
                            formMoneyFormatter(data.nett_omission_addition, '.rfv_nett_omission_addition-txt');
                            table.scrollToRow(rowIdx, top, false);
                            table.selectRow(rowIdx);
                            table.modules.ajax.hideLoader();
                        },
                        error: function(){
                            table.deselectRow();
                            table.modules.ajax.hideLoader();
                        }
                    });
                },rowSelected:function(row){
                    var item = row.getData();
                    if($( "#cost_estimate-add_row-btn" ).hasClass( "disabled" )){
                        $( "#cost_estimate-add_row-btn" ).removeClass( "disabled" );
                    }
                    if(item && parseInt(item.id) > 0 && $( "#cost_estimate-del_row-btn" ).hasClass( "disabled")){
                        $( "#cost_estimate-del_row-btn" ).removeClass( "disabled" );
                    }else if(item && parseInt(item.id) < 0 && !$( "#cost_estimate-del_row-btn" ).hasClass( "disabled")){
                        $( "#cost_estimate-del_row-btn" ).addClass( "disabled" );
                    }
                }
                @endif
                ,rowClick:function(e, row){
                    table.deselectRow();
                    row.select();
                }
                @if($editableCostEstimate)
                ,cellEditing:function(cell){
                    table.deselectRow();
                    cell.getRow().select();
                }
                @endif
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if(e.target.id=="costEstimateTarget"){
                    table.redraw();
                }
            });

            @if($editableCostEstimate)
            $("#cost_estimate-add_row-btn").click(function(e){
                e.preventDefault();
                var selectedRows = table.getSelectedRows();
                if(selectedRows.length){
                    var selectedRow = selectedRows[0];
                    var itemBefore = selectedRow.getData();
                    if(itemBefore){
                        var rowIdx = selectedRow.getIndex();
                        var content;
                        if(parseInt(itemBefore.id) > 0){
                            content = {before_id:itemBefore.id, rfv_id: {{{$requestForVariation->id}}}, _token:'{{{csrf_token()}}}'};
                        }else{
                            var prevRow = selectedRow.getPrevRow();
                            var prevItemId = (prevRow) ? prevRow.getData().id : 0;
                            content = {id:-1, field:null, val:null, rfv_id: {{{$requestForVariation->id}}}, prev_item_id:prevItemId, _token:'{{{csrf_token()}}}'};
                        }
                        table.modules.ajax.showLoader();
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('requestForVariation.cost.estimate.add', [$project->id]) }}",
                            data: content,
                            dataType: "json",
                            success: function(data){
                                table.deselectRow();
                                table.addRow(data.item, true, rowIdx)
                                .then(function(row){
                                    table.selectRow(rowIdx);
                                    table.updateData(data.items);
                                    var rows = table.searchRows("priority", ">=", data.item.priority);
                                    rows.forEach(function(r){
                                        r.reformat();
                                    });
                                    rows = table.searchRows("priority", "=", -1);
                                    rows.forEach(function(r){
                                        r.reformat();
                                    });
                                    formMoneyFormatter(data.nett_omission_addition, '.rfv_nett_omission_addition-txt');
                                })
                                .catch(function(error){
                                    table.deselectRow();
                                });
                                table.modules.ajax.hideLoader();
                            },
                            error: function(){
                                table.deselectRow();
                                table.modules.ajax.hideLoader();
                            }
                        });
                    }
                }
            });
            $("#cost_estimate-del_row-btn").click(function(e){
                e.preventDefault();
                var selectedRows = table.getSelectedRows();
                if(selectedRows.length){
                    var selectedRow = selectedRows[0];
                    var item = selectedRow.getData();
                    if(item && parseInt(item.id)>0){
                        $('#costEstimateDeleteModal').modal("show");
                    }
                }
            });
            $('#costEstimateDeleteModal').on('click', '.btn-ok', function(e) {
                var selectedRows = table.getSelectedRows();
                if(selectedRows.length){
                    var selectedRow = selectedRows[0];
                    var item = selectedRow.getData();
                    if(item && parseInt(item.id)>0){
                        var $modalDiv = $(e.delegateTarget);
                        table.modules.ajax.showLoader();
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('requestForVariation.cost.estimate.delete', [$project->id]) }}",
                            data: {id:item.id, _token:'{{{csrf_token()}}}'},
                            dataType: "json",
                            success: function(data){
                                table.deselectRow();
                                table.deleteRow(item.id)
                                .then(function(){
                                    table.updateData(data.items);
                                    var rows = table.searchRows("priority", ">=", item.priority);
                                    rows.forEach(function(r){
                                        r.reformat();
                                    });
                                    rows = table.searchRows("priority", "=", -1);
                                    rows.forEach(function(r){
                                        r.reformat();
                                    });
                                })
                                .catch(function(error){
                                    table.deselectRow();
                                });
                                formMoneyFormatter(data.nett_omission_addition, '.rfv_nett_omission_addition-txt');
                                table.modules.ajax.hideLoader();
                                $modalDiv.modal('hide');
                            },
                            error: function(){
                                table.deselectRow();
                                table.modules.ajax.hideLoader();
                                $modalDiv.modal('hide');
                            }
                        });
                    }
                }
            });
            @endif

            @if($errors->has('cost_estimates'))
                $('a[href="#costEstimateTabContent]"').tab('show');
                $('#costEstimatesImportModal').modal('show');
            @endif
            $('form#cost-estimate-import-form').on('submit', function(){
                $('#costEstimatesImportModal button[data-action="form-submit"]').prop('disabled', true);
            });
        @endif
    </script>

@endsection
