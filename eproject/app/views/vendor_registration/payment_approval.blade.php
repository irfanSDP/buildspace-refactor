@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}
    </style>
@endsection
<?php use PCK\ObjectField\ObjectField; ?>
@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->company->name, array($vendorRegistration->id)) }}</li>
        <li>{{ trans('vendorManagement.vendorRegistrationPayment') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('vendorManagement.vendorRegistrationPayment') }}
			</h1>
		</div>
	</div>
	<div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('vendorManagement.vendorRegistrationPayment') }}</h2>				
        </header>
        <div>
            <div class="widget-body">
                    @if(!empty($instructionSettings->payment))
                    <div class="padded label-success text-white"><strong>{{ nl2br($instructionSettings->payment) }}</strong></div>
                    <br>
                    @endif
                    @if(!empty($vendorRegistrationPaymentSection->amendment_remarks))
                    <div class="well @if($vendorRegistrationPaymentSection->amendmentsRequired()) border-danger @elseif($vendorRegistrationPaymentSection->amendmentsMade()) border-warning @endif">
                        {{ nl2br($vendorRegistrationPaymentSection->amendment_remarks) }}
                    </div>
                    @endif
                <div id="payments-table"></div>
                <div class="smart-form">
                    @if($vendorRegistrationPayment)
                    <form action="{{ route('vendorManagement.approval.payment.paidOrSuccessful.status.update', [$vendorRegistration->company->id, $vendorRegistrationPayment->id]) }}" method="POST">
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                        <fieldset>
                            <div class="row">
                                <section class="col col-6">
                                    <label class="label">{{ trans('vendorManagement.paymentSubmittedOn', ['dateTime' => $submittedDate]) }}</label>
                                </section>
                            </div>
                            @if(!$vendorRegistrationPayment->isRejected())
                                <div class="row">
                                    <section class="col col-6">
                                        @if($vendorRegistrationPayment->isPaid())
                                        <label class="label">{{ trans('vendorManagement.paymentPaidOn', ['dateTime' => $paidDate]) }}
                                            <button type="button" class="btn btn-primary" data-action="upload-item-attachments" 
                                                data-route-get-attachments-list="{{ route('vendorManagement.approval.payment.additional.attachments.get', [$vendorRegistration->company->id, $vendorRegistrationPayment->id, ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID]) }}"
                                                data-route-update-attachments="{{ route('vendorManagement.approval.payment.additional.attachments.upload', [$vendorRegistration->company->id, $vendorRegistrationPayment->id, ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID]) }}"
                                                data-route-get-attachments-count="{{ route('vendorManagement.approval.payment.additional.attachments.count.get', [$vendorRegistration->company->id, $vendorRegistrationPayment->id, ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID]) }}"
                                                data-field="{{ ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID }}">
                                                <?php 
                                                    $record = ObjectField::findRecord($vendorRegistrationPayment, ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID);
                                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                                ?>
                                                <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                            </button>
                                        </label>
                                        @else
                                        <label class="label">{{ trans('general.dateAndTime') }} <span class="required"> *</span></label>
                                        <label class="input">
                                            <input type="text" name="paid_date" class="datepicker" required>
                                        </label>
                                        @endif
                                    </section>
                                    @if(!$vendorRegistrationPayment->isPaid())
                                    <section class="col col-1">
                                        <label class="label">&nbsp;</label>
                                        <label class="label">
                                            <button type="submit" class="btn btn-success" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}">{{ trans('general.paid') }}</button>
                                        </label>
                                    </section>
                                    @endif
                                </div>
                                @if($vendorRegistrationPayment->isPaid())
                                <div class="row">
                                    <section class="col col-6">
                                        @if($vendorRegistrationPayment->isCompleted())
                                        <label class="label">{{ trans('vendorManagement.paymentCompletedOn', ['dateTime' => $completedDate]) }}
                                            <button type="button" class="btn btn-primary" data-action="upload-item-attachments" 
                                                data-route-get-attachments-list="{{ route('vendorManagement.approval.payment.additional.attachments.get', [$vendorRegistration->company->id, $vendorRegistrationPayment->id, ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL]) }}"
                                                data-route-update-attachments="{{ route('vendorManagement.approval.payment.additional.attachments.upload', [$vendorRegistration->company->id, $vendorRegistrationPayment->id, ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL]) }}"
                                                data-route-get-attachments-count="{{ route('vendorManagement.approval.payment.additional.attachments.count.get', [$vendorRegistration->company->id, $vendorRegistrationPayment->id, ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL]) }}"
                                                data-field="{{ ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL }}">
                                                <?php 
                                                    $record = ObjectField::findRecord($vendorRegistrationPayment, ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL);
                                                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                                ?>
                                                <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                            </button>
                                        </label>
                                        @else
                                        <label class="label">{{ trans('general.dateAndTime') }} <span class="required"> *</span></label>
                                        <label class="input">
                                            <input type="text" name="successful_date" class="datepicker" required>
                                        </label>
                                        @endif
                                    </section>
                                    @if(!$vendorRegistrationPayment->isCompleted())
                                    <section class="col col-1">
                                        <label class="label">&nbsp;</label>
                                        <label class="label">
                                            <button type="submit" class="btn btn-success" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}">{{ trans('general.completed') }}</button>
                                        </label>
                                    </section>
                                    @endif
                                </div>
                                @endif
                            @endif
                        </fieldset>
                    </form>
                    @endif
                </div>
                @if($vendorRegistration->isProcessing() && ($vendorRegistrationPaymentSection->status_id != \PCK\VendorRegistration\Section::STATUS_REJECTED) && ($vendorRegistrationPayment && !$vendorRegistrationPayment->isPaid() && !$vendorRegistrationPayment->isRejected()))
                <div>
                    <form action="{{ route('vendorManagement.approval.paymentSection.reject', [$vendorRegistration->company->id])}}" method="POST">
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                        <button type="submit" class="btn btn-danger pull-right" data-intercept="confirmation" data-confirmation-with-remarks="amendment_remarks" data-confirmation-with-remarks-required="true" data-confirmation-with-remarks-required-message="{{ trans('forms.remarksRequired') }}"><i class="fa fa-times"></i> {{ trans('forms.reject') }}</button>
                    </form>
                </div>
                @endif
                @if($vendorRegistrationPaymentSection->amendmentsMade() || $vendorRegistrationPaymentSection->amendmentsRequired())
                <form action="{{ route('vendorManagement.approval.paymentSection.resolve', [$vendorRegistration->company->id])}}" method="POST" id="resolve-form">
                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                    <button type="submit" class="btn btn-warning pull-right"><i class="fa fa-check"></i> {{ trans('forms.markAsResolved') }}</button>
                </form>
                @endif
                {{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', trans('forms.back'), array($vendorRegistration->id), array('class' => 'btn btn-default pull-right spaced')) }}
            </div>
        </div>
    </div>

    <div data-type="template" hidden>
        <table>
            @include('file_uploads.partials.uploaded_file_row_template')
        </table>
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

    @include('templates.generic_table_modal', [
        'modalId'    => 'paymentProofModal',
        'title'      => trans('vendorManagement.proofOfPayment'),
        'tableId'    => 'paymentProofTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var paymentsTable = null;
            var paymentProofTable = null;

            $('.datepicker').datetimepicker({
                format: 'DD-MMM-YYYY',
                stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                showTodayButton: false,
                allowInputToggle: true,
            });

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var getUploadedFilesButton = document.createElement('a');
                getUploadedFilesButton.dataset.toggle = 'tooltip';
                getUploadedFilesButton.title = "{{ trans('vendorManagement.viewProofOfPayment') }}";
                getUploadedFilesButton.className = 'btn btn-xs btn-primary';
                getUploadedFilesButton.innerHTML = '<i class="fas fa-paperclip"></i> (' + data.uploaded_file_count + ')';
                getUploadedFilesButton.style['margin-right'] = '5px';

                getUploadedFilesButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#paymentProofModal').data('url', data.route_get_uploaded_files);
                    $('#paymentProofModal').modal('show');
                });

                return getUploadedFilesButton;
            }

            paymentsTable = new Tabulator('#payments-table', {
                height:250,
				pagination:"local",
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('general.bank') }}", field: 'bank_name', headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('vendorManagement.virtualAccountNumber') }}", field: 'virtual_bank_account_number', width: 280, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('vendorManagement.proofOfPayment') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
				],
                layout:"fitColumns",
				ajaxURL: "{{ route('vendorManagement.approval.payment.records.all.get', [$vendorRegistration->company->id]) }}",
                movableColumns:true,
                placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                columnHeaderSortMulti:false,
            });

            var attachmentDownloadButtonFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var downloadButton = document.createElement('a');
                downloadButton.dataset.toggle = 'tooltip';
                downloadButton.className = 'btn btn-xs btn-primary';
                downloadButton.innerHTML = '<i class="fas fa-download"></i>';
                downloadButton.style['margin-right'] = '5px';
                downloadButton.href = data.download_url;
                downloadButton.download = data.filename;

                return downloadButton;
            }

            $('#paymentProofModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                paymentProofTable = new Tabulator('#paymentProofTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.download') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: attachmentDownloadButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
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

            $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
                e.preventDefault();

                var target = $('#uploadFileTable tbody.files').empty();
                var data   = $.get($(this).data('route-get-attachments-list'), function(data){
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

                $('[data-action=submit-attachments]').data('updated-attachment-count-url', $(this).data('route-get-attachments-count'));
                $('#uploadAttachmentModal').modal('show');
                $('#attachment-upload-form').prop('action',$(this).data('route-update-attachments'));
            });

            $(document).on('click', '[data-action=submit-attachments]', function(){
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

                        $.get(updatedAttachmentCountUrl, {},function(resp) {
                            $(document).find('[data-field="' + resp.field + '"]').find('[data-component="attachment_upload_count"]').text(resp.attachmentCount);
                        });

                        app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            });
        });
    </script>
@endsection