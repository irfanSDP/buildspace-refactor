@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show',  str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('finance.submitClaims') }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-upload"></i> {{ trans('finance.submitClaims') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2 class="font-md">{{ trans('finance.claims') }}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="claims-table"></div>
                    </div>
                </div>
            </div>

            @if($canSubmitClaim)
                @include('contractorClaims.claimSubmissionFormWidget')
            @endif
        </div>
    </div>

    <div class="modal fade" id="uploadDocumentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('finance.invoice') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {{ Form::open(array('id' => 'invoice-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                        <section>
                            <label class="label">{{{ trans('forms.upload') }}}:</label>
                            {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                            @include('file_uploads.partials.upload_file_modal', array('id' => 'invoice-upload'))
                        </section>
                    {{ Form::close() }}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-action="form-submit" data-target-id="invoice-upload-form" data-intercept="confirmation" data-confirmation-message="{{ trans('finance.invoiceUploadConfirmation') }}"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    @include('templates.attachmentsListModal')
    @include('templates.attachmentsListModal', array(
        'modalId' => 'invoiceAttachmentsListModal',
        'tableId' => 'invoiceAttachmentsTable',
        'title' => trans('finance.invoice')
    ))
    @include('templates.log_table_modal')

@endsection

@section('js')
    <script>
        $(document).ready(function() {

            var invoiceAttachmentsTable = new Tabulator("#invoiceAttachmentsTable", {
                layout: "fitColumns",
                placeholder: "{{ trans('general.noAttachments') }}",
                columns: columns_attachmentsTable
            });
            
            var claimsTable = new Tabulator("#claims-table", {
                data: webClaim.claimsData,
                layout: "fitColumns",
                height: 360,
                placeholder: "{{ trans('finance.noClaims') }}",
                columns: [
                    {title:"{{ trans('finance.claimNo') }}", width:80, field: 'claimVersion', cssClass:"text-center", frozen: true, headerSort:false},
                    {
                        title: '<div class="text-center">{{ trans('finance.submittedClaim') }}</div>',
                        columns: [
                            {title:"{{ trans('finance.bqWorkDone') }}", width:160, field: 'bqWorkDoneAmount', cssClass:"text-right", headerSort:false},
                            {title:"{{ trans('finance.voWorkDone') }}", width:160, field: 'voWorkDoneAmount', cssClass:"text-right", headerSort:false},
                            {title:"{{ trans('finance.materialOnSite') }}", width:160, field: 'materialOnSiteAmount', cssClass:"text-right", headerSort:false},
                            {title:"{{ trans('finance.totalWorkDone') }}", width:160, field: 'totalWorkDone', cssClass:"text-right", headerSort:false},
                            {title:"{{ trans('forms.submittedBy') }}", width:160, field: 'submittedBy', cssClass:"text-center", headerSort:false},
                            {title:"{{ trans('forms.submittedAt') }}", width:160, field: 'submittedAt', cssClass:"text-center", headerSort:false},
                            {title:"{{ trans('general.attachments') }}", width:160, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                                formatterParams: {
                                    innerHtml: '<i class="fa fa-paperclip"></i>',
                                    tag: 'button',
                                    attributes: {class: 'btn btn-xs btn-default', type: 'button', 'data-action':'showAttachments'},
                                    rowAttributes: {"data-version": 'claimVersion'}
                                }
                            },
                            {title:"{{ trans('general.log') }}", cssClass:"text-center", width:40, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                                formatterParams: {
                                    tag: 'button',
                                    attributes: {'title': '{{ trans("finance.unlockClaimSubmissionsLog") }}', 'class': 'btn btn-xs btn-default', 'data-trigger': 'log-modal'},
                                    rowAttributes: {'data-route': 'route:claimSubmissionLog'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {'class': 'fa fa-clipboard'}
                                    }
                                }
                            },
                            {title:"{{ trans('finance.invoice') }}", cssClass:"text-center", width:80, headerSort: false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                                formatterParams: {
                                    innerHtml: function(rowData){
                                        var output;
                                        if(rowData['route:invoiceAttachments']){
                                            output = '<button data-action="showInvoiceAttachmentModal" data-route="'+rowData['route:invoiceAttachments']+'" class="btn btn-xs btn-warning" title="{{ trans("general.attachments") }}"><i class="fa fa-download"></i></a>';
                                        }
                                        else{
                                            @if($currentUser->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR))
                                                var disabled = '';
                                                if(!rowData['isCertApproved']){
                                                    disabled = 'disabled unclickable';
                                                }

                                                output = '<button type="button" title="{{ trans("forms.upload") }}" class="btn btn-xs btn-default '+disabled+'" data-action="upload-invoice" data-route="'+rowData['route:invoiceUpload']+'"><i class="fa fa-upload"></i></button>';
                                            @else
                                                output = '<a href="javascript:void(0);" class="btn btn-xs btn-warning disabled" title="{{ trans("general.download") }}"><i class="fa fa-download"></i></a>';
                                            @endif
                                        }
                                        return output;
                                    },
                                }
                            }
                        ]
                    },
                    {title:"{{ trans('finance.certifiedAmount') }}", width:160, field: 'certifiedAmount', cssClass:"text-right", headerSort:false},
                    {title:"{{ trans('finance.certifiedClaims') }}", width:120, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: function(rowData){
                                var disabled = rowData.isCertApproved ? "" : "disabled";

                                var output = '<a href="'+rowData.exportClaimsRoute+'" target="_blank" rel="tooltip" title="{{ trans('general.download') }}" class="btn btn-xs btn-warning"><i class="fa fa-download"></i></a>';

                                if(disabled){
                                    output = '<a href="javascript:void(0)" rel="tooltip" title="{{ trans('general.download') }}" class="btn btn-xs btn-warning" disabled><i class="fa fa-download"></i></a>';
                                }

                                return output;
                            }
                        }
                    },
                    {title:"{{ trans('finance.claimCertificate') }}", width:120, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: function(rowData){
                                var disabled = rowData.isCertApproved ? "" : "disabled";

                                var output = '<a href="'+rowData.printRoute+'" target="_blank" rel="tooltip" title="{{ trans('general.print') }}" class="btn btn-xs btn-success"><i class="fa fa-print"></i></a>';

                                if(disabled)
                                {
                                    output = '<a href="javascript:void(0)" rel="tooltip" title="{{ trans('general.print') }}" class="btn btn-xs btn-success" disabled><i class="fa fa-print"></i></a>';
                                }

                                return output;   
                            }
                        }
                    },
                    @if(!$currentUser->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR))
                    {title:"{{ trans('finance.resubmissions') }}", cssClass:"text-center", width: 120, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: [
                                {
                                    innerHtml: function(rowData){
                                        var disabled = rowData.canUnlockResubmission ? "" : "disabled";

                                        var output = '<button type="button" data-trigger="form" data-action="'+rowData['route:unlockResubmission']+'" data-method="POST" data-csrf_token="{{{ csrf_token() }}}" rel="tooltip" title="{{ trans('finance.unlockClaimSubmissions') }}" class="btn btn-xs btn-default"><i class="fa fa-unlock"></i></button>';

                                        if(disabled)
                                        {
                                            output = '<a href="javascript:void(0)" rel="tooltip" title="{{ trans('finance.unlockClaimSubmissions') }}" class="btn btn-xs btn-default" disabled><i class="fa fa-unlock"></i></a>';
                                        }
                                        return output;
                                    }
                                },
                                {innerHtml: function(){ return '&nbsp'; } },
                                {
                                    tag: 'button',
                                    attributes: {'title': '{{ trans("finance.unlockClaimSubmissionsLog") }}', 'class': 'btn btn-xs btn-default', 'data-trigger': 'log-modal'},
                                    rowAttributes: {'data-route': 'route:unlockResubmissionLog'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {'class': 'fa fa-clipboard'}
                                    }
                                }
                            ]
                        }
                    }
                    @endif
                ]
            });

            var logModalTable = new Tabulator('#logModal-table', {
                placeholder: "{{ trans('general.noMatchingResults') }}",
                layout: "fitColumns",
                height:280,
                columns: [
                    {title:"{{ trans('general.no') }}", width:20, cssClass:"text-center", headerSort:false, formatter:'rownum'},
                    {title:"{{ trans('general.updatedBy') }}", minWidth:220, field: 'created_by', cssClass:"text-left", headerSort:false},
                    {title:"{{ trans('general.updatedAt') }}", width:140, field: 'created_at', cssClass:"text-center", headerSort:false},
                ]
            });

            $(document).on('click', '[data-trigger=log-modal]', function(){
                logModalTable.setData($(this).data('route'));
                $('#logModal').modal('show');
            });

            var attachmentsTable = new Tabulator("#attachmentsTable", {
                placeholder: "{{ trans('general.noAttachments') }}",
                columns: columns_attachmentsTable,
            });

            $('#claims-table').on('click', '[data-action=showAttachments]', function(){
                attachmentsTable.setData("{{ route('projects.contractorClaims.attachments', array($project->id)) }}", {version: $(this).data('version')});
                $('#attachmentsListModal [data-type=title').html("{{ trans('finance.claim') }}: "+$(this).data('version'));
                $('#attachmentsListModal').modal().show();
            });

            $('#claim-submission-form').on('submit', function(e){
                app_progressBar.show();
                app_progressBar.maxOut();
            });

            $(document).on('click', '[data-action=upload-invoice]', function(){
                $('#uploadDocumentModal').modal('show');
                $('#invoice-upload-form').prop('action',$(this).data('route'));
            });

            $(document).on('click', '[data-action=showInvoiceAttachmentModal]', function(){
                invoiceAttachmentsTable.setData($(this).data('route'));
                $('#invoiceAttachmentsListModal').modal().show();
            });

            $('#invoice-upload-form').on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });

            @if($errors->has('uploaded_files'))
                $('#uploadDocumentModal').modal('show');
            @endif
        });
    </script>
@endsection