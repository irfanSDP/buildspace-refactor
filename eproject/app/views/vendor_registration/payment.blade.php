@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{ trans('vendorManagement.vendorRegistrationPayment') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-credit-card"></i> {{ trans('vendorManagement.vendorRegistrationPayment') }}
			</h1>
		</div>
	</div>
	<div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body">
                <form id="vendor_registration_payment_form" class="smart-form">
                    <div>
                        @if ($paymentGatewayData['allow_payment'])
                            <div class="row">
                                <div class="col col-xs-12 col-md-4">
                                    <label class="label">{{ trans('payment.paymentMethods') }}</label>
                                    <label class="select">
                                        <select class="select2" id="payment-method-selection">
                                            <option value="">{{ trans('general.selectAnOption') }}</option>
                                            @foreach($paymentGatewayData['selections'] as $key => $label)
                                                <option value="{{ $key }}" {{ $key === $paymentGatewayData['selected'] ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                            </div>
                        @else
                            {{ $paymentGatewayData['html'] }}
                        @endif
                    </div>

                    <hr style="margin-top:20px; margin-bottom: 20px;">

                    <div id="payment-gateway-container" style="display: none;">
                        @if ($paymentGatewayData['allow_payment'])
                            <div class="row">
                                <div class="col col-xs-12 col-md-4">
                                    <label class="label">{{ trans('payment.paymentGateway') }}</label>
                                    {{ $paymentGatewayData['html'] }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div id="manual-payment-container" style="display: none;">
                        @if(!empty($instructionSettings->payment))
                            <div class="label padded label-success text-white"><strong>{{ nl2br($instructionSettings->payment) }}</strong></div>
                        @endif
                        @if(!empty($vendorRegistrationPaymentSection->amendment_remarks))
                            <div class="well @if($vendorRegistrationPaymentSection->amendmentsRequired()) border-danger @elseif($vendorRegistrationPaymentSection->amendmentsMade()) border-warning @endif">
                                {{ nl2br($vendorRegistrationPaymentSection->amendment_remarks) }}
                            </div>
                        @endif
                        <fieldset>
                            <div class="row">
                                <section class="col col-xs-12 col-md-4">
                                    <label class="label">{{ trans('vendorManagement.banks') }}</label>
                                    <label class="select">
                                        <select class="input-sm select2" id="manual-payment-opt">
                                            <option value="">{{ trans('general.selectAnOption') }}</option>
                                            @foreach($paymentMethods as $pm)
                                            <option value="{{ $pm['id'] }}" @if($currentlySelectedPaymentMethodRecord && ($pm['id'] == $currentlySelectedPaymentMethodRecord->payment_setting_id)) selected @endif>{{ $pm['name'] }}</option>
                                            @endforeach
                                        </select> <i></i>
                                    </label>
                                </section>
                                @if($currentlySelectedPaymentMethodRecord)
                                <section class="col col-xs-12 col-md-4">
                                    <label class="label">{{ trans('vendorManagement.virtualAccountNumber') }}</label>
                                    <label class="input">
                                        <?php $virtualAccountNumber = is_null($currentlySelectedPaymentMethodRecord) ? null : $currentlySelectedPaymentMethodRecord->getVirtualAccountNumber(); ?>
                                        <input type="text" name="virtualAccountNumber" value="{{ $virtualAccountNumber }}" readonly>
                                    </label>
                                </section>
                                <section class="col col-xs-12 col-md-4">
                                    <label class="label">{{ trans('vendorManagement.uploadProofOfPayment') }}</label>
                                    <label class="input">
                                        <button type="button" class="btn btn-primary" data-action="upload-item-attachments"><i class="fas fa-paperclip fa-lg"></i>&nbsp;<span data-component="attachment_upload_count">{{ $attachmentsCount }}</span> {{ trans('formBuilder.filesUploaded') }}</button>
                                    </label>
                                </section>
                                @endif
                            </div>
                        </fieldset>
                    </div>
                    <footer>
                        {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                    </footer>
                </form>
                @include('payments.gateway.partials.pg-form-container')
            </div>
        </div>		
    </div>

    @if($currentlySelectedPaymentMethodRecord)
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
    @endif
@endsection

@section('js')
    @include('common.scripts')
    @include('payments.gateway.partials.script-pg-btn')
    <script>
        $(document).ready(function() {
            let selectedMethod = "{{ $paymentGatewayData['selected'] }}";

            $('#payment-method-selection').on('change', function(e) {
                e.preventDefault();
                selectedMethod = $(this).val();

                if (selectedMethod) {
                    getSelection(selectedMethod);
                }
            }).trigger('change'); // This triggers the change event on page load

            function getSelection(selectedMethod) {
                let paymentGatewayContainer = $('#payment-gateway-container');
                let manualPaymentContainer = $('#manual-payment-container');

                switch (selectedMethod) {
                    case "{{ \PCK\PaymentGateway\PaymentGatewaySetting::GATEWAY_MANUAL }}":
                        paymentGatewayContainer.hide();
                        manualPaymentContainer.show();
                        return;

                    case "{{ \PCK\PaymentGateway\PaymentGatewaySetting::GATEWAY_SENANGPAY }}":
                        manualPaymentContainer.hide();
                        getPgButton();
                        break;

                    default:
                        paymentGatewayContainer.hide();
                        manualPaymentContainer.hide();
                        return;
                }
            }

            function getPgButton() {
                $.ajax({
                    url: "{{ route('api.payment-gateway.html.payment-btn') }}",
                    method: 'GET',
                    data: {
                        pg: selectedMethod,
                    },
                    success: function (response) {
                        if (response.html) {
                            $('#pg-container .pg-btn-container').html(atob(response.html));
                            $('#pg-container .pg-btn-container').data('pg', btoa(selectedMethod));
                            $('#payment-gateway-container').show();
                            initPaymentGatewayBtn();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            $('#manual-payment-opt').on('change', function(e) {
                e.preventDefault();

                var selectedManual = $(this).val();

                if(selectedManual == '') return;

                app_progressBar.toggle();

                $.ajax({
                    url: "{{ route('vendor.registration.payment.method.select') }}",
                    method: 'GET',
                    data: {
                        paymentSettingId: selectedManual,
                    },
                    success: function (response) {
                        app_progressBar.maxOut();
                        window.location.href = window.location.origin + window.location.pathname + '?q=' + selectedMethod;
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });

            @if($currentlySelectedPaymentMethodRecord)

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
                    var data   = $.get("{{ route('vendor.registration.payment.attachements.get', [$currentlySelectedPaymentMethodRecord->id]) }}", function(data) {
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

                    $('#uploadAttachmentModal').modal('show');
                    $('#attachment-upload-form').prop('action', "{{ route('vendor.registration.payment.attachements.update', [$currentlySelectedPaymentMethodRecord->id]) }}");
                });

                $(document).on('click', '[data-action=submit-attachments]', function(){
                    var uploadedFilesInput = [];

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

                            $.get("{{ route('vendor.registration.payment.attachements.count.get', [$currentlySelectedPaymentMethodRecord->id]) }}", {},function(resp) {
                                $(document).find('[data-component="attachment_upload_count"]').text(resp.attachmentCount);
                            });

                            app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                        }
                    })
                    .fail(function(data){
                        console.error('failed');
                    });
                });
            @endif
        });
    </script>
@endsection