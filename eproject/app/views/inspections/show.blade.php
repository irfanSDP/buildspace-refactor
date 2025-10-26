@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('inspection.request', trans('requestForInspection.requestForInspection'), array($project->id)) }}</li>
        <li>{{ trans('inspection.inspectionX', array('no' => $inspection->revision+1)) }}</li>
    </ol>
@endsection

@section('content')
<?php use PCK\Inspections\InspectionListItem; ?>
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-search"></i> {{{ trans('inspection.inspection') }}}
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('inspection.inspectionX', array('no' => $inspection->revision+1)) }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        @if($editable)
                        {{ Form::model($inspection, array('route' => array('inspection.submit', $project->id, $requestForInspection->id, $inspection->id), 'method' => 'POST', 'class' => 'smart-form', 'id' => 'submit-form')) }}
                        @else
                        <div class="smart-form">
                        @endif
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('requestForInspection.location') }}}:</label>
                                        <label class="input">
                                            @foreach($locationsDescription as $description)
                                                <input type="text" value="{{ $description }}" disabled/>
                                            @endforeach
                                        </label>
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('requestForInspection.inspectionList') }}}:</label>
                                        <label class="input">
                                            @foreach($inspectionLists as $name)
                                                <input type="text" value="{{ $name }}" disabled/>
                                            @endforeach
                                        </label>
                                    </section>
                                </div>
                                <div class="well" data-id="list-category-info">
                                    <div class="row" data-id="dynamic-form">
                                        @foreach($additionalFields as $fieldInfo)
                                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                                <label class="label">{{ $fieldInfo['name'] }}</label>
                                                <label class="input">
                                                    <input type="text" value="{{ $fieldInfo['value'] }}" disabled>
                                                </label>
                                            </section>
                                        @endForeach
                                    </div>
                                    <div id="inspection-list-items-table"></div>
                                    <br/>
                                    <div class="row">
                                        <section class="col col-xs-12 col-md-6 col-lg-6">
                                            <label class="label">{{{ trans('requestForInspection.inspectionReadyDate') }}}:</label>
                                            <label class="input">
                                                <input type="text" value="{{ \Carbon\Carbon::parse($inspection->ready_for_inspection_date)->format(\Config::get('dates.created_and_updated_at_formatting')) }}" disabled/>
                                            </label>
                                        </section>
                                    </div>
                                </div>
                                <br/>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('inspection.comments') }}}:</label>
                                        @if($editable)
                                        <label class="textarea">
                                        {{ Form::textArea('comments', Input::old('comments') ?? $inspection->comments, ['class' => 'form-control', 'style' => 'width:100%;', 'rows' => 3]) }}
                                        </label>
                                        @else
                                        <div class="well">{{ nl2br(e($inspection->comments)) }}</div>
                                        @endif
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('inspection.decision') }}}:</label>
                                        <?php $options = ! $editable ? "disabled" : ""; ?>
                                        <div class="custom-control custom-radio">
                                            {{ Form::radio('decision', \PCK\Inspections\Inspection::DECISION_ALLOWED_TO_PROCEED, (Input::old('decision') ?? $inspection->decision) == \PCK\Inspections\Inspection::DECISION_ALLOWED_TO_PROCEED, ['id'=>'radio-decision_allowed_to_proceed', 'class'=>'custom-control-input', $options]) }}
                                            <label class="custom-control-label" for="radio-decision_allowed_to_proceed">{{ trans('inspection.allowedToProceed') }}</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            {{ Form::radio('decision', \PCK\Inspections\Inspection::DECISION_ALLOWED_TO_PROCEED_WITH_REMEDIAL_WORKS, (Input::old('decision') ?? $inspection->decision) == \PCK\Inspections\Inspection::DECISION_ALLOWED_TO_PROCEED_WITH_REMEDIAL_WORKS, ['id'=>'radio-decision_allowed_to_proceed_with_remedial_works', 'class'=>'custom-control-input', $options]) }}
                                            <label class="custom-control-label" for="radio-decision_allowed_to_proceed_with_remedial_works">{{ trans('inspection.allowedToProceedRemedial') }}</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            {{ Form::radio('decision', \PCK\Inspections\Inspection::DECISION_NOT_ALLOWED_TO_PROCEED, (Input::old('decision') ?? $inspection->decision) == \PCK\Inspections\Inspection::DECISION_NOT_ALLOWED_TO_PROCEED, ['id'=>'radio-decision_not_allowed_to_proceed', 'class'=>'custom-control-input', $options]) }}
                                            <label class="custom-control-label" for="radio-decision_not_allowed_to_proceed">{{ trans('inspection.notAllowedToProceed') }}</label>
                                        </div>
                                        {{ $errors->first('decision', '<em class="invalid">:message</em>') }}
                                    </section>
                                    @if($editable)
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('forms.attachments') }}}:</label>

                                        @include('file_uploads.partials.upload_file_modal')
                                    </section>
                                    @else
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <strong>{{{ trans('forms.attachments') }}}:</strong><br>
                                           @include('file_uploads.partials.uploaded_file_show_only', ['files' => $inspection->attachments, 'projectId' => $inspection->requestForInspection->project_id])
                                    </section>
                                    @endif
                                </div>
                            </fieldset>
                            <footer>
                                {{ link_to_route('inspection.request', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#verifier-log-modal">{{ trans('verifiers.verifierLogs') }}</button>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#submission-log-modal">{{ trans('inspection.submissionLogs') }}</button>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#inspections-overview-modal">{{ trans('inspection.overview') }}</button>
                                @if($editable)
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.submit'), ['type' => 'submit', 'name'=>'submit', 'class' => 'btn btn-primary', 'data-intercept' => 'confirmation'] )  }}
                                @endif
                                @if(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $inspection))
                                @include('verifiers.approvalForm', array('object' => $inspection, 'approveClass' => 'btn btn-success', 'rejectClass' => 'btn btn-danger'))
                                @endif
                            </footer>
                        @if($editable)
                        {{ Form::close() }}
                        @else
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.logs_table_modal', array('modalId' => 'verifier-log-modal', 'modalTitleId' => 'verifier-log-modal-title', 'tableId' => 'verifier-log-table'))
@include('templates.logs_table_modal', array('modalId' => 'submission-log-modal', 'modalTitleId' => 'submission-log-modal-title', 'tableId' => 'submission-log-table'))

@include('templates.attachmentsListModal')
@include('inspections.partials.overview_modal', array('requestForInspection' => $inspection->requestForInspection))
@endsection

@section('js')
    <script>
        var inspectionListItemTable = new Tabulator('#inspection-list-items-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", frozen:true, headerSort:false},
                {title:"{{ trans('requestForInspection.description') }}", field:"description", minWidth: 350, hozAlign:"left", frozen:true, headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                    var rowData     = cell.getRow().getData();
                    var paddingLeft = rowData.depth * 16;
                    var style       = 'padding-left: ' + paddingLeft + 'px;';;

                    if(rowData.type == "{{ InspectionListItem::TYPE_HEAD }}") {
                        style += 'font-weight: bold;';
                    }

                    return `<span style="${ style }">${ rowData.description }</span>`;
                }},
                @foreach($roles as $role)
                {
                    title:"{{{ $role->name }}}",
                    columns: [
                        {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status-{{ $role->id }}", minWidth: 30, width:110, hozAlign:"right", headerSort:false},
                        {title:"{{ trans('inspection.remarks') }}", field:"remarks-{{ $role->id }}", minWidth: 120, width:200, hozAlign:"center", headerSort:false},
                        {title:"{{ trans('forms.attachments') }}", minWidth: 30, width:110, hozAlign:"center", headerSort:false, formatter: function(cell, formatterParams, onRendered){
                            if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}") return null;

                            var rowData   = cell.getRow().getData();
                            var innerHtml = `<i class="fa fa-paperclip"></i>&nbsp;&nbsp;(${ rowData['attachmentCount-{{ $role->id }}'] })`;

                            return `<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#attachmentsListModal" data-action="item-attachments-list" data-id="{{ $role->id }}" data-uploads-list="${ rowData['route:getUploads'] }">${ innerHtml }</button>`;
                        }},
                    ]
                },
                @endforeach
            ]
        });
        inspectionListItemTable.setData(webClaim.listItemData);

        var verifiersLogTable = new Tabulator('#verifier-log-table', {
            height: 380,
            maxHeight:380,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            dataLoaded:function(data){
                if(data.length){
                    var lastItem = data.pop();
                    this.scrollToRow(lastItem.id, "top");
                }
            },
            placeholder: "{{ trans('verifiers.noVerifiers') }}",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', headerSort:false, cssClass:"text-center text-middle"},
                {title:"{{ trans('verifiers.verifier') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('verifiers.status') }}", field:"approved", minWidth: 80, width: 80, hozAlign:"left", headerSort:false, formatter: function(cell, formatterParams, onRendered){
                    var label;
                    switch(cell.getValue())
                    {
                        case true:
                            label = '<span class="text-success"><i class="fa fa-thumbs-up"></i> <strong>{{ trans("verifiers.approved") }}</strong></span>';
                            break;
                        case false:
                            label = '<span class="text-danger"><i class="fa fa-thumbs-down"></i> <strong>{{ trans("verifiers.rejected") }}</strong></span>';
                            break;
                        default:
                            label = '<span class="text-warning"><i class="fa fa-question"></i> <strong>{{ trans("verifiers.unverified") }}</strong></span>';
                    }
                    return label;
                }},
                {title:"{{ trans('verifiers.verifiedAt') }}", field:"verified_at", minWidth: 120, width: 140, hozAlign:"center", headerSort:false},
                {title:"{{ trans('verifiers.remarks') }}", field:"remarks", minWidth: 120, width: 300, hozAlign:"left", headerSort:false},
            ]
        });

        var submissionLogTable = new Tabulator('#submission-log-table', {
            height: 380,
            maxHeight:380,
            layout:"fitColumns",
            placeholder: "{{ trans('inspection.noSubmission') }}",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.role') }}", field:"role", minWidth: 120, width: 140, hozAlign:"left", headerSort:false},
                {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('inspection.submittedAt') }}", field:"submitted_at", minWidth: 120, width: 140, hozAlign:"center", headerSort:false},
            ]
        });
        $('#submission-log-modal-title').html("{{ trans('inspection.submissionLogs') }}");
        $('#submission-log-modal').on('show.bs.modal', function(){
            submissionLogTable.setData("{{ route('inspection.submissionLogs', array($project->id, $requestForInspection->id, $inspection->id)) }}");
        });

        $('#verifier-log-modal-title').html("{{ trans('verifiers.verifierLogs') }}");
        $('#verifier-log-modal').on('show.bs.modal', function(){
            verifiersLogTable.setData("{{ route('inspection.approvalLogs', array($project->id, $requestForInspection->id, $inspection->id)) }}");
        });

        $('#verifierForm [type=submit][name=approve]').attr('data-intercept', 'confirmation');
        $('#verifierForm [type=submit][name=approve]').attr('data-confirmation-with-remarks', 'verifier_remarks');

        $('#verifierForm [type=submit][name=reject]').attr('data-intercept', 'confirmation');
        $('#verifierForm [type=submit][name=reject]').attr('data-confirmation-with-remarks', 'verifier_remarks');

        var itemAttachmentsTable = new Tabulator("#attachmentsTable", {
            layout: "fitColumns",
            placeholder: "{{ trans('general.noAttachments') }}",
            columns: columns_attachmentsTable
        });

        $('#inspection-list-items-table').on('click', '[data-action=item-attachments-list]', function(){
            itemAttachmentsTable.setData($(this).data('uploads-list'), { role_id: $(this).data('id') });
        });
    </script>
@endsection