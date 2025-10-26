@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('inspection.request', trans('requestForInspection.requestForInspection'), array($project->id)) }}</li>
        <li>{{ trans('inspection.inspection') }}</li>
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
                        {{ Form::open(array('route' => array('inspection.inspect.update', $project->id, $requestForInspection->id, $inspection->id), 'method' => 'POST', 'class' => 'smart-form', 'id' => 'add-form')) }}
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
                            </fieldset>
                            <footer>
                                {{ link_to_route('inspection.request', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#submission-log-modal">{{ trans('inspection.submissionLogs') }}</button>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#inspections-overview-modal">{{ trans('inspection.overview') }}</button>
                                @if($editable)
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'submit', 'data-intercept' => 'confirmation', 'data-confirmation-message' => trans('inspection.submitInspectionWarning').' '.trans('general.areYourSureYouWantToDoThis')] )  }}
                                @endif
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(array('id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', array('id' => 'invoice-upload'))
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>

@include('inspections.partials.overview_modal', array('requestForInspection' => $inspection->requestForInspection))

@if($editable)
<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>
@endif
@include('templates.attachmentsListModal')

@include('templates.logs_table_modal', array('modalId' => 'submission-log-modal', 'modalTitleId' => 'submission-log-modal-title', 'tableId' => 'submission-log-table'))

@endsection

@section('js')
    <script>
        var editableCheck = function(cell) {
            var isTypeItem = (cell.getRow().getData().type == "{{ InspectionListItem::TYPE_ITEM }}");
            var isEditable = (cell.getRow().getData().editable);

            return (isTypeItem && isEditable);
        };

        var inspectionListItemTable = new Tabulator('#inspection-list-items-table', {
            height:450,
            layout:"fitColumns",
            placeholder: "{{ trans('inspection.listEmpty') }}",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", frozen:true, headerSort:false},
                {title:"{{ trans('requestForInspection.description') }}", field:"description", minWidth: 300, hozAlign:"left", frozen:true, headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                    var rowData     = cell.getRow().getData();
                    var paddingLeft = rowData.depth * 16;
                    var style       = 'padding-left: ' + paddingLeft + 'px;';;

                    if(rowData.type == "{{ InspectionListItem::TYPE_HEAD }}") {
                        style += 'font-weight: bold;';
                    }

                    return `<span style="${ style }">${ rowData.description }</span>`;
                }},
                {
                    title:"{{ trans('inspection.inspectionX', array('no' => $inspection->revision+1)) }}",
                    columns: [
                        {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status", minWidth: 30, width:110, hozAlign:"right", headerSort:false, editor: 'input', editable: editableCheck},
                        @if($editable)
                        {title:"{{ trans('inspection.completed') }}", minWidth: 30, width:110, hozAlign:"center", headerSort:false, formatter: function(cell, formatterParams, onRendered){
                            if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}") return null;

                            var checked = '';
                            if(cell.getData().progress_status == 100) checked = 'checked';
                            return '<input type="checkbox" data-action="toggle-progress" data-id="'+cell.getData().id+'" data-route="'+cell.getData()['route:update']+'" '+checked+'/>';
                        }},
                        @endif
                        {title:"{{ trans('inspection.remarks') }}", field:"remarks", minWidth: 120, hozAlign:"center", headerSort:false, editor: 'input', editable: editableCheck},
                        @if($editable)
                        {title:"{{ trans('forms.attachments') }}", minWidth: 30, width:110, hozAlign:"center", headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                            if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}") return null;

                            var rowData   = cell.getRow().getData();
                            var innerHtml = `<i class="fa fa-paperclip"></i>&nbsp;&nbsp;(${ rowData.attachmentCount })`;

                            return `<button type="button" class="btn btn-xs btn-info" data-action="upload-item-attachments" data-route="${ rowData['route:attachmentUpload'] }" data-attachment-list="${ rowData['route:attachmentRoute'] }" data-id="${ rowData.id }" data-updated-attachment-count-url="${ rowData['route:getUpdatedAttachmentCount'] }">${ innerHtml }</button>`;
                        }},
                        @else
                        {title:"{{ trans('forms.attachments') }}", minWidth: 30, width:110, hozAlign:"center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatter: function(cell, formatterParams, onRendered) {
                            if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}") return null;

                            var rowData   = cell.getRow().getData();
                            var innerHtml = `<i class="fa fa-paperclip"></i>&nbsp;&nbsp;(${ rowData.attachmentCount })`;

                            return `<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#attachmentsListModal" data-action="item-attachments-list" data-uploads-list="${ rowData['route:getUploads'] }">${ innerHtml }</button>`;
                        }},
                        @endif
                    ]
                },
                @if(isset($previousInspection))
                {
                    title:"{{ trans('inspection.inspectionX', array('no' => $previousInspection->revision+1)) }}",
                    columns: [
                        {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status-{{ $previousInspection->revision }}", minWidth: 30, width:110, hozAlign:"right", headerSort:false},
                        {title:"{{ trans('inspection.remarks') }}", field:"remarks-{{ $previousInspection->revision }}", minWidth: 120, hozAlign:"center", headerSort:false},
                    ]
                },
                @endif
            ],
            @if($editable)
            cellEdited:function(cell){
                var cellData = cell.getData();
                var table = cell.getTable();

                var input = {
                    _token: _csrf_token
                };
                input['field'] = cell.getField();
                input['value'] = cell.getValue();
                $.post(cellData['route:update'], input)
                .done(function(data){
                    if(data.success){
                        cell.getRow().update(data.rowData);
                        cell.getRow().reformat();
                        // CustomTabulator.onEnter_focus(cell);
                    }
                    else{
                        SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                        cell.restoreOldValue();
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            }
            @endif
        });
        inspectionListItemTable.setData(webClaim.listItemData);

        @if($editable)
        $('#inspection-list-items-table').on('change', 'input[type=checkbox][data-action=toggle-progress]', function(){
            var row = inspectionListItemTable.getRow($(this).data('id'));
            var newValue = (row.getData().progress_status!=100) ? 100 : 0;
            var input = {
                _token: _csrf_token,
                field: 'progress_status',
                value: newValue
            };
            $.post($(this).data('route'), input)
            .done(function(data){
                if(data.success){
                    row.update(data.rowData);
                    row.reformat();
                    // CustomTabulator.onEnter_focus(cell);
                }
                else{
                    cell.restoreOldValue();
                }
            })
            .fail(function(data){
                console.error('failed');
            });
        });

        function addRowToUploadModal(fileAttributes){
            var clone = $('[data-type=template] tr.template-download').clone();
            var target = $('#uploadFileTable tbody.files');
            $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
            $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
            $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
            $(clone).find("a[data-category=link]").html(fileAttributes['filename']);
            $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
            $(clone).find("[data-category=size]").html(fileAttributes['size']);
            $(clone).find("button[data-action=delete]").prop('data-route', fileAttributes['deleteRoute']);
            $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);
            target.append(clone);
        }

        $('#inspection-list-items-table').on('click', '[data-action=upload-item-attachments]', function(){
            var target = $('#uploadFileTable tbody.files').empty();
            var data = $.get($(this).data('attachment-list'), function(data){
                for(var i in data){
                    addRowToUploadModal({
                        download_url: data[i]['download_url'],
                        filename: data[i]['filename'],
                        imgSrc: data[i]['imgSrc'],
                        id: data[i]['id'],
                        size: data[i]['size'],
                        deleteRoute: data[i]['deleteRoute'],
                        createdAt: data[i]['createdAt'],
                    });
                }
            });

            $('[data-action=submit-attachments]').data('id', $(this).data('id'));
            $('[data-action=submit-attachments]').data('updated-attachment-count-url', $(this).data('updated-attachment-count-url'));
            $('#uploadAttachmentModal').modal('show');
            $('#attachment-upload-form').prop('action',$(this).data('route'));

        });

        $(document).on('click', '[data-action=submit-attachments]', function(){
            var rowId                     = $(this).data('id');
            var updatedAttachmentCountUrl = $(this).data('updated-attachment-count-url');
            var uploadedFilesInput        = [];

            $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                uploadedFilesInput.push($(this).val());
            });

            app_progressBar.show();

            $.post($('form#attachment-upload-form').prop('action'),{
                _token: _csrf_token,
                uploaded_files: uploadedFilesInput
            })
            .done(function(data){
                if(data.success){
                    $('#uploadAttachmentModal').modal('hide');

                    $.get(updatedAttachmentCountUrl, { inspectionListItemId : rowId },function(updatedAttachmentCount){
                        inspectionListItemTable.updateRow(rowId, { attachmentCount: updatedAttachmentCount });
                        inspectionListItemTable.getRows().filter(row => row.getData().id == rowId)[0].reformat();
                    });

                    app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                }
            })
            .fail(function(data){
                console.error('failed');
            });
        });
        @endif

        var itemAttachmentsTable = new Tabulator("#attachmentsTable", {
            layout: "fitColumns",
            placeholder: "{{ trans('general.noAttachments') }}",
            columns: columns_attachmentsTable
        });

        $('#inspection-list-items-table').on('click', '[data-action=item-attachments-list]', function(){
            itemAttachmentsTable.setData($(this).data('uploads-list'));
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
    </script>
@endsection