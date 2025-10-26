@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('navigation/mainnav.financeModule') }}}</li>
    </ol>
@endsection
<?php
    $limitToThisContractor = isset($limitToThisContractor) ? $limitToThisContractor : false;
    $user = isset($user) ? $user : null;
    $claimCertificateTableURL = $limitToThisContractor ?  route('finance.contractor.module.claim-certificate.get', [$user->id]) : route('finance.claim-certificate.data');
?>

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fas fa-dollar-sign"></i> {{{ trans('navigation/mainnav.financeModule') }}}
            </h1>
        </div>
        @if(!$limitToThisContractor)
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <div class="btn-group pull-right header-btn">
                    @include('finance.claim-certificate.index_action_menu')
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{{ trans('finance.claimCertificates') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="form-inline padded-bottom padded-left padded-less-top" data-options="filter-options">
                            <label class="control-label col-sm-2 text-right"><strong>{{ trans('subsidiaries.filterBySubsidiary') }}</strong></label>
                            <div class="col-sm-10" style="padding-top:4px;padding-bottom:4px;">
                                <select class="select2 form-control fill-horizontal" id="subsidiaryFilter" data-action="filter">
                                    <option value="">{{ trans('forms.none') }}</option>
                                    @foreach ($subsidiaries as $subsidiaryId => $subsidiaryName)
                                        <option value="{{{ $subsidiaryId }}}">
                                            {{{ $subsidiaryName }}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="claim-certificates-table" style="width:100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.attachmentsListModal', array(
        'modalId' => 'invoiceAttachmentsListModal',
        'tableId' => 'invoiceAttachmentsTable',
        'title' => trans('finance.invoice')
    ))
    @include('finance.projects.accountCodes.partials.exportToAccountingModal')
    @include('templates.logs_table_modal')
    @include('templates.logs_table_modal', [
        'modalId'      => 'exportToAccountingLogModal',
        'modalTitleId' => 'exportToAccountingLogModalTitle',
        'tableId'      => 'exportToAccountingLogTable',
    ])
    @include('templates.logs_table_modal', [
        'modalId'      => 'exportLogDetailsModal',
        'modalTitleId' => 'exportToAccountingLogDetailsModalTitle',
        'tableId'      => 'exportLogDetailsTable',
    ])
    @include('finance.claim-certificate.partials.claim_certificate_payments_modal')
    @include('finance.claim-certificate.partials.claim_certificate_invoice_information_modal')
    @include('templates.warning_modal')
@endsection

@section('js')
<script src="{{ asset('js/app/app.common.js') }}"></script>
<script src="{{ asset('js/moment/min/moment.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
<script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
            showTodayButton: true,
            allowInputToggle: true
        });

        $('#claimCertificateInvoiceInformationModal .datetimepicker').on('dp.change', function(e) {
            var date = moment(e.target.value, "DD-MMM-YYYY");
            var month = ('0' + (date.month()+1)).slice(-2);
            var postMonth = date.year() + month;

            $('#claimCertificateInvoiceInformationModal').find('[name="postMonth"]').val(postMonth);
        });

        var claimCertificateTable = null;
        var claimCertificateTableURL = "{{{ $claimCertificateTableURL }}}";
        var isEditor = false;
        var allIds = [];
        
        var logsTable = null;
        var exportToAccountingLogTable = null;
        var exportLogDetailsTable = null;
        var claimCertificatePaymentsTable = null;
        var invoiceAttachmentsTable = null;

        var approvedPhaseSubsidiariesTable = null;
        var allItemCodeIds = new Set();

        @if(\PCK\ModulePermission\ModulePermission::isEditor($currentUser, PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE))
            isEditor = true;
        @endif

        var paymentStatusButtonFormatter = function(cell, formatterParams, onRendered) {
            var buttonColorClass = cell.getRow().getData().paid ? 'btn-success' : 'btn-warning';
            var disabled = isEditor ? '' : 'disabled';

            var paymentStatusButton = document.createElement('button');
            paymentStatusButton.id = 'btnPaymentStatus_' + cell.getRow().getData().id;
            paymentStatusButton.innerText = cell.getRow().getData().paymentStatus;
            paymentStatusButton.className = 'btn btn-xs ' + buttonColorClass + ' fill-horizontal ' + disabled;
            paymentStatusButton.dataset.toggle = 'modal';
            paymentStatusButton.dataset.target = '#claimCertificatePaymentsModal';

            paymentStatusButton.addEventListener('click', function(e) {
                e.preventDefault();

                $('#claimCertificatePaymentsModal').data('id', cell.getRow().getData().id);
                $('#claimCertificatePaymentsModal').data('url', cell.getRow().getData().route_getClaimCertificatePayments);
                $('#claimCertificatePaymentsModal').data('claim_cert_payments_url', cell.getRow().getData().route_claimCertPaymentAmounts);
                $('[data-action="proceed"]').data('claim_cert_payment_store_url', cell.getRow().getData().route_claimCertPaymentStore);

                $('#claimCertificatePaymentsModal').find('.currencyCode').html(cell.getRow().getData().currencyCode);
            });

            return paymentStatusButton;
        };

        $('#claimCertificatePaymentsModal').on('shown.bs.modal', function(e) {
            e.preventDefault();
            clearClaimCertPaymentErrors();
            
            var id                   = $(this).data('id');
            var url                  = $(this).data('url');
            var claimCertPaymentsUrl = $(this).data('claim_cert_payments_url');

            claimCertificatePaymentsTable = new Tabulator('#claimCertificatePaymentsTable', {
                height:300,
                columns: [
                    { title: "{{ trans('general.no') }}", field: 'count', width: 50, cssClass:"text-center", align: 'center', headerSort: false },
                    { title: "{{ trans('finance.bank') }}", field: 'bank', width: 300, cssClass:"text-left", align: 'left', headerSort: false },
                    { title: "{{ trans('finance.reference') }}", field: 'reference', width: 200, cssClass:"text-center", align: 'center', headerSort: false },
                    { title: "{{ trans('finance.amount') }}", field: 'amount', width: 150, cssClass:"text-right", align: 'right', headerSort: false },
                    { title: "{{ trans('general.date') }}", field: 'date', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                    { title: "{{ trans('general.createdBy') }}", field: 'createdBy', cssClass:"text-left", align: 'left', headerSort: false },
                ],
                layout:"fitColumns",
                ajaxURL: url,
                ajaxParams: { claimCertificateId: id },
                ajaxConfig: "GET",
                movableColumns:true,
                pagination:"local",
                placeholder:"{{{ trans('general.noRecordsFound') }}}",
                columnHeaderSortMulti:false,
                dataLoaded:function(data) {
                    $.ajax({
                        type: "GET",
                        url: claimCertPaymentsUrl,
                        success: function(response) {
                            $('#claimCertPaymentAmountBalance').html(response.balance);
                            $('#claimCertPaymentPaidAmount').html(response.paidAmount);

                            claimCertificateTable.updateRow(id, { paidAmount: response.paidAmount, balance: response.balance });
                        }
                    });
                },
            });
        });

        function clearClaimCertPaymentErrors() {
            $('#claimCertificatePaymentsModal [name="error-bank"]').html('')
            $('#claimCertificatePaymentsModal [name="error-reference"]').html('');
            $('#claimCertificatePaymentsModal [name="error-amount"]').html('');
            $('#claimCertificatePaymentsModal [name="error-date"]').html('');
        }

        function clearClaimCertPaymentInputs() {
            $('#claimCertificatePaymentsModal [name="bank"]').val('');
            $('#claimCertificatePaymentsModal [name="reference"]').val('');
            $('#claimCertificatePaymentsModal [name="amount"]').val('');
        }

        $('#btnSubmitClaimCertPayment').on('click', function(e) {
            clearClaimCertPaymentErrors();
        });

        $('[data-action="proceed"]').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var url = $(this).data('claim_cert_payment_store_url');

            var bank = $('#claimCertificatePaymentsModal [name="bank"]').val();
            var reference = $('#claimCertificatePaymentsModal [name="reference"]').val();
            var amount = $('#claimCertificatePaymentsModal [name="amount"]').val();
            var date = $('#claimCertificatePaymentsModal [name="date"]').val();

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    bank: bank,
                    reference: reference,
                    amount: amount,
                    date: date,
                    "_token": '{{{csrf_token()}}}'
                },
                success: function(response) {
                    if(response.success) {
                        clearClaimCertPaymentInputs();
                        claimCertificatePaymentsTable.setData();
                    } else {
                        for(const [key, value] of Object.entries(response.errors)) {
                            $(`#claimCertificatePaymentsModal [name="error-${key}"]`).html(value[0]);
                        } 
                    }

                    $('#confirmationModal').modal('hide');
                }
            });
        });

        $('#claimCertificatePaymentsModal').on('hidden.bs.modal', function(e) {
            e.preventDefault();

            clearClaimCertPaymentInputs();
            $('#claimCertPaymentAmountBalance').html('');
            $('#claimCertPaymentPaidAmount').html('');
            
            claimCertificatePaymentsTable.destroy();
            claimCertificatePaymentsTable = null;
        });

        var printButtonFormatter = function(cell, formatterParams, onRendered) {
            var limitToThisContractor = {{{ $limitToThisContractor ? 1 : 0 }}};
            var buttonColorClass = (cell.getRow().getData().printLogCount > 0) ? 'btn-success' : 'btn-warning';

            if(limitToThisContractor) {
                buttonColorClass = 'btn-success';
            }

            var disabled = 'disabled';

            if(limitToThisContractor || isEditor) {
                disabled = '';
            }

            var printButton = document.createElement('button');
            printButton.id = 'btnPrint_' + cell.getRow().getData().id;
            printButton.className = 'btn btn-xs ' + buttonColorClass + ' ' + disabled;
            printButton.innerHTML = '<i class="fa fa-print"></i></a>';
            printButton.disabled = isEditor ? false : true;
            printButton.setAttribute('data-getUpdatedPrintLogURL', "{{ route('finance.claim-certificate.print.log.count.get') }}");

            if(limitToThisContractor) {
                printButton.disabled = false;
            }

            printButton.addEventListener('click', function(e) {
                e.preventDefault();
                var route = 'javascript:void(0)';

                if(limitToThisContractor || isEditor) {
                    route = cell.getRow().getData().route_print;
                }

                window.open(route, '_blank');

                if(!limitToThisContractor) {
                    var partialURL = printButton.getAttribute('data-getUpdatedPrintLogURL');
                    var updatedPrintLogURL = partialURL.replace('%7BclaimCertificateId%7D', cell.getRow().getData().id);

                    $.ajax({
                        type: "GET",
                        url: updatedPrintLogURL,
                        success: function(newLogCount) {
                            claimCertificateTable.updateRow(cell.getRow().getData().id, {printLogCount: newLogCount});
                            cell.getRow().reformat();
                        }
                    });
                }
            });

            var spanPrintLogCount = document.createElement('span');
            spanPrintLogCount.className = 'badge';
            spanPrintLogCount.innerText = cell.getRow().getData().printLogCount;

            var viewPrintLogButton = document.createElement('button');
            viewPrintLogButton.id = 'btnViewPrintLog_' + cell.getRow().getData().id;
            viewPrintLogButton.className = 'btn btn-xs btn-success';
            viewPrintLogButton.innerHTML = "{{ trans('general.log') }}&nbsp;&nbsp;";
            viewPrintLogButton.style.marginLeft = '5px';
            viewPrintLogButton.style.width = '65%';
            viewPrintLogButton.appendChild(spanPrintLogCount);
            viewPrintLogButton.dataset.toggle = 'modal';
            viewPrintLogButton.dataset.target = '#logsTableModal';
            viewPrintLogButton.dataset.url = cell.getRow().getData().route_printLog;

            viewPrintLogButton.addEventListener('click', function(e) {
                e.preventDefault();

                $('#logsTableModal').data('url', cell.getRow().getData().route_printLog);
                $('#logsTableModalTitle').html("{{{ trans('finance.claimCertificatePrintLog') }}}");
            });

            var divContainer = document.createElement('div');
            divContainer.className = 'text-middle text-left text-nowrap';
            divContainer.appendChild(printButton);
            divContainer.appendChild(viewPrintLogButton);

            if(limitToThisContractor) {
                return printButton;
            }

            return divContainer;
        };

        var sendClaimCertificateFormatter = function(cell, formatterParams, onRendered) {
            var buttonColorClass = (cell.getRow().getData().sendClaimCertLogCount > 0) ? 'btn-success' : 'btn-warning';
            var disabled = isEditor ? '' : 'disabled';
            var route = isEditor ? cell.getRow().getData().route_sendClaimCert : 'javascript:void(0)';

            var sendClaimCertificateButton = document.createElement('button');
            sendClaimCertificateButton.id = 'btnSendClaimCertificate_' + cell.getRow().getData().id;
            sendClaimCertificateButton.className = 'btn btn-xs ' + buttonColorClass + ' ' + disabled;
            sendClaimCertificateButton.innerHTML = '<i class="fa fa-paper-plane"></i></a>';
            
            if(isEditor) {
                sendClaimCertificateButton.setAttribute('data-route', route);
                sendClaimCertificateButton.setAttribute('data-action', 'send-claim-certificate');
                sendClaimCertificateButton.setAttribute('data-getClaimCertLogCountURL', "{{ route('finance.claim-certificate.sent.log.count.get') }}");
            }

            sendClaimCertificateButton.addEventListener('click', function() {
                sendNotification(sendClaimCertificateButton, cell.getRow().getData().id, route);    
            });

            sendClaimCertificateButton.disabled = isEditor ? false : true;

            var spanClaimCertificateSendLogCount = document.createElement('span');
            spanClaimCertificateSendLogCount.className = 'badge';
            spanClaimCertificateSendLogCount.innerText = cell.getRow().getData().sendClaimCertLogCount;

            var viewClaimCertificateSendLogButton = document.createElement('button');
            viewClaimCertificateSendLogButton.id = 'btnViewClaimCertificateSendLog_' + cell.getRow().getData().id;
            viewClaimCertificateSendLogButton.className = 'btn btn-xs btn-success';
            viewClaimCertificateSendLogButton.innerHTML = "{{ trans('general.log') }}&nbsp;&nbsp;";
            viewClaimCertificateSendLogButton.style.marginLeft = '5px';
            viewClaimCertificateSendLogButton.style.width = '65%';
            viewClaimCertificateSendLogButton.appendChild(spanClaimCertificateSendLogCount);
            viewClaimCertificateSendLogButton.dataset.toggle = 'modal';
            viewClaimCertificateSendLogButton.dataset.target = '#logsTableModal';

            viewClaimCertificateSendLogButton.addEventListener('click', function(e) {
                e.preventDefault();

                $('#logsTableModal').data('url', cell.getRow().getData().route_sendClaimCertLog);
                $('#logsTableModalTitle').html("{{{ trans('finance.claimCertificateEmailLog') }}}");
            });

            var divContainer = document.createElement('div');
            divContainer.className = 'text-middle text-left text-nowrap';
            divContainer.appendChild(sendClaimCertificateButton);
            divContainer.appendChild(viewClaimCertificateSendLogButton);

            return divContainer;
        }

        var paymentCollectionFormatter = function(cell, formatterParams, onRendered) {
            var buttonColorClass = cell.getRow().getData().hasPendingPaymentNotifications ? 'btn-warning' : 'btn-success';
            var disabled = isEditor ? '' : 'disabled';
            var route = isEditor ? cell.getRow().getData().route_sendPaymentNotification : 'javascript:void(0)';

            var sendPaymentCollectionNotification = document.createElement('button');
            sendPaymentCollectionNotification.id = 'btnSendPaymentCollectionNotification_' + cell.getRow().getData().id;
            sendPaymentCollectionNotification.className = 'btn btn-xs ' + buttonColorClass + ' ' + disabled;
            sendPaymentCollectionNotification.innerHTML = '<i class="fa fa-bell"></i></a>';
            
            if(isEditor) {
                sendPaymentCollectionNotification.setAttribute('data-item-id', cell.getRow().getData().id);
                sendPaymentCollectionNotification.setAttribute('data-route', route);
                sendPaymentCollectionNotification.setAttribute('data-action', 'send-payment-collection-notification');
                sendPaymentCollectionNotification.setAttribute('data-getPaymentNotificationLogCountURL', "{{ route('finance.claim-certificate.payment.notification.log.count.get') }}");
            }

            sendPaymentCollectionNotification.disabled = isEditor ? false : true;

            var paymentNotificationSendLogCount = document.createElement('span');
            paymentNotificationSendLogCount.className = 'badge';
            paymentNotificationSendLogCount.innerText = cell.getRow().getData().paymentNotificationLogCount;

            var viewPaymentNotificationSentLogButton = document.createElement('button');
            viewPaymentNotificationSentLogButton.id = 'btnViewPaymentNotificationSentLogButton' + cell.getRow().getData().id
            viewPaymentNotificationSentLogButton.className = 'btn btn-xs btn-success';
            viewPaymentNotificationSentLogButton.innerHTML = "{{ trans('general.log') }}&nbsp;&nbsp;";
            viewPaymentNotificationSentLogButton.style.marginLeft = '5px';
            viewPaymentNotificationSentLogButton.style.width = '80%';
            viewPaymentNotificationSentLogButton.appendChild(paymentNotificationSendLogCount);
            viewPaymentNotificationSentLogButton.dataset.toggle = 'modal';
            viewPaymentNotificationSentLogButton.dataset.target = '#logsTableModal';

            viewPaymentNotificationSentLogButton.addEventListener('click', function(e) {
                e.preventDefault();

                $('#logsTableModal').data('url', cell.getRow().getData().route_sendPaymentNotificationLog);
                $('#logsTableModalTitle').html("{{{ trans('finance.claimCertificatePaymentLog') }}}");
            });

            var divContainer = document.createElement('div');
            divContainer.className = 'text-middle text-left text-nowrap';
            divContainer.appendChild(sendPaymentCollectionNotification);
            divContainer.appendChild(viewPaymentNotificationSentLogButton);

            return divContainer;
        }

        var exportToAccountingColumFormatter = function(cell, formatterParams, onRendered) {
            var limitToThisContractor = {{{ $limitToThisContractor ? 1 : 0 }}};
            var buttonColorClass = (cell.getRow().getData().exportAccountingLogCount > 0) ? 'btn-success' : 'btn-warning';

            if(limitToThisContractor) {
                buttonColorClass = 'btn-success';
            }

            var disabled = 'disabled';

            if(limitToThisContractor || isEditor) {
                disabled = '';
            }

            var exportAccountingButton = document.createElement('button');
            exportAccountingButton.className = 'btn btn-xs ' + buttonColorClass + ' ' + disabled;
            exportAccountingButton.innerHTML = '<i class="fa fa-print"></i></a>';
            exportAccountingButton.disabled = (isEditor && cell.getRow().getData().canExportAccounting) ? false : true;

            exportAccountingButton.addEventListener('click', function(e) {
                e.preventDefault();

                var route = 'javascript:void(0)';

                if(limitToThisContractor || isEditor) {
                    route = cell.getRow().getData().route_exportAccounting;
                }

                app_progressBar.show();
                app_progressBar.maxOut();

                $.ajax({
                    type: "GET",
                    url: cell.getRow().getData().route_validateExportAccounting,
                    data: {
                        projectId: cell.getRow().getData().projectId,
                        claimCertificateId: cell.getRow().getData().id,
                    },
                    success: function(response) {
                        app_progressBar.hide();
                        if(response.isValid) {
                            $('#approvedPhaseSubsidiariesURL').val(cell.getRow().getData().route_getApprovedPhaseSubsidiaries);
                            $('#apportionmentTypeName').val(cell.getRow().getData().apportionmentTypeName);
                            $('#exportAccountingRoute').val(route);
                            $('#projectId').val(cell.getRow().getData().projectId);
                            $('#claimCertificateId').val(cell.getRow().getData().id)
                            $('#exportToAccountLatesLogCountURL').val("{{ route('finance.claim-certificate.account.report.export.log.count.get') }}");
                            $('#exportToAccountingModal').modal('show');
                        } else {
                            $.smallBox({
                                title : "{{ trans('general.warning') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('accountCodes.amountValidationFailed') }}</i>",
                                color : "#C46A69",
                                sound: true,
                                iconSmall : "fa fa-paper-plane",
                                timeout : 5000
                            });
                        }
                    }
                });
            });

            var spanExportAccountingLogCount = document.createElement('span');
            spanExportAccountingLogCount.className = 'badge';
            spanExportAccountingLogCount.innerText = cell.getRow().getData().exportAccountingLogCount;

            var viewExportAccountingLogButton = document.createElement('button');
            viewExportAccountingLogButton.id = 'btnViewExportAccountLog_' + cell.getRow().getData().id;
            viewExportAccountingLogButton.className = 'btn btn-xs btn-success';disabled
            viewExportAccountingLogButton.innerHTML = "{{ trans('general.log') }}&nbsp;&nbsp;";
            viewExportAccountingLogButton.style.marginLeft = '5px';
            viewExportAccountingLogButton.style.width = '65%';
            viewExportAccountingLogButton.appendChild(spanExportAccountingLogCount);
            viewExportAccountingLogButton.dataset.toggle = 'modal';
            viewExportAccountingLogButton.dataset.target = '#exportToAccountingLogModal';

            viewExportAccountingLogButton.addEventListener('click', function(e) {
                e.preventDefault();

                $('#exportToAccountingLogModal').data('url', cell.getRow().getData().route_getExportAccountingLog);
                $('#exportToAccountingLogModalTitle').html("{{{ trans('finance.claimCertificateExportAccountLog') }}}");
            });

            var divContainer = document.createElement('div');
            divContainer.className = 'text-middle text-left text-nowrap';
            divContainer.appendChild(exportAccountingButton);
            divContainer.appendChild(viewExportAccountingLogButton);

            return divContainer;
        }

        var invoiceInformationFormatter = function(cell, formatterParams, onRendered) {
            var buttonClass = cell.getRow().getData().hasInvoiceInformation ? 'btn-success' : 'btn-warning';
            var buttonText = cell.getRow().getData().hasInvoiceInformation ? "{{ trans('general.updated') }}" : "{{ trans('general.pending') }}";

            var updateClaimCertificateInvoiceInformationButton = document.createElement('button');
            updateClaimCertificateInvoiceInformationButton.id = 'btnUpdateClaimCertificateInvoiceInformation_' + cell.getRow().getData().id;
            updateClaimCertificateInvoiceInformationButton.className = 'btn btn-xs ' + buttonClass;
            updateClaimCertificateInvoiceInformationButton.innerHTML = buttonText;
            updateClaimCertificateInvoiceInformationButton.style.width = '100%';
            updateClaimCertificateInvoiceInformationButton.dataset.toggle = 'modal';
            updateClaimCertificateInvoiceInformationButton.dataset.target = '#claimCertificateInvoiceInformationModal';

            updateClaimCertificateInvoiceInformationButton.addEventListener('click', function(e) {
                e.preventDefault();

                $('#claimCertificateInvoiceInformationModal').data('id', cell.getRow().getData().id);
                $('#claimCertificateInvoiceInformationModal').data('get_url', cell.getRow().getData().route_getClaimCertInfo);
                $('#claimCertificateInvoiceInformationModal').data('store_url', cell.getRow().getData().route_claimCertInvoiceInfoStore);
            });

            return updateClaimCertificateInvoiceInformationButton;
        }

        $('#claimCertificateInvoiceInformationModal').on('shown.bs.modal', function(e) {
            clearClaimCertificateInvoiceInformationErrors();

            var self = $(this);
            var url = $('#claimCertificateInvoiceInformationModal').data('get_url');

            $.ajax({
                type: "GET",
                url: url,
                success: function(response) {
                    self.find('[name="invoiceNumber"]').val(response.claimCertInvoiceNumber);
                    self.find('[name="invoiceDate"]').val(response.claimCertInvoiceDate);
                    self.find('[name="postMonth"]').val(response.claimCertInvoicePostMonth);
                }
            });
        });

        $('#btnSubmitClaimCertificateInvoiceInformation').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            clearClaimCertificateInvoiceInformationErrors();

            var id = $('#claimCertificateInvoiceInformationModal').data('id');
            var url = $('#claimCertificateInvoiceInformationModal').data('store_url');
            var invoiceNumber = $('#claimCertificateInvoiceInformationModal').find('[name="invoiceNumber"]').val();
            var invoiceDate = $('#claimCertificateInvoiceInformationModal').find('[name="invoiceDate"]').val();
            var invoicePostMonth = $('#claimCertificateInvoiceInformationModal').find('[name="postMonth"]').val();

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    invoiceNumber: invoiceNumber,
                    invoiceDate: invoiceDate,
                    postMonth: invoicePostMonth,
                    "_token": '{{{csrf_token()}}}'
                },
                success: function(response) {
                    if(response.success) {
                        $('#claimCertificateInvoiceInformationModal').modal('hide');
                        claimCertificateTable.updateRow(id, { hasInvoiceInformation: true });
                        claimCertificateTable.redraw(true);
                    } else {
                        for(const [key, value] of Object.entries(response.errors)) {
                            $(`#claimCertificateInvoiceInformationModal [name="error-${key}"]`).html(value[0]);
                        } 
                    }
                }
            });
        });

        function clearClaimCertificateInvoiceInformationErrors() {
            $('#claimCertificateInvoiceInformationModal').find('[name="error-invoiceNumber"]').html('');
            $('#claimCertificateInvoiceInformationModal').find('[name="error-invoiceDate"]').html('');
            $('#claimCertificateInvoiceInformationModal').find('[name="error-postMonth"]').html('');
        }

        var columns = [
            { title: "id", field: 'id', visible:false, frozen:true },
            { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', headerSort:false, frozen:true },
            { title: "{{ trans('projects.reference') }}", field: 'reference', width: 180, headerSort:false, headerFilter: 'input', headerFilterPlaceHolder: 'filter', frozen:true },
            { title: "{{ trans('projects.title') }}", field: 'projectTitle', width: 420, headerSort:false, headerFilter: 'input', headerFilterPlaceHolder: 'filter', frozen:true },
            { title: "{{ trans('finance.company') }}", field: 'subsidiary', width: 320, headerSort:false },
            { title: "{{ trans('finance.contractor') }}", field: 'contractor', width: 320, headerSort:false, @if(!$limitToThisContractor) headerFilter: 'input', headerFilterPlaceholder: 'filter' @endif },
            { title: "{{ trans('finance.subContractWork') }}", field: 'subContractWork', width: 420, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter' },
            { title: "{{ trans('finance.claimNo') }}", field: 'version', width: 100, headerSort:false, 'align': 'center', headerFilter: 'input', headerFilterPlaceholder: 'filter' },
            { title: "{{ trans('finance.letterOfAwardNo') }}", field: 'letterOfAwardCode', width: 200, headerSort:false},
            { title: "{{ trans('finance.approvedAmount') }}", field: 'approvedAmount', align: 'right', width: 150, headerSort:false },
            { title: "{{ trans('finance.approvedDate') }}", field: 'approvedDate', align: 'center', width: 150, headerSort:false },
            { title: "{{ trans('finance.paidAmount') }}", field: 'paidAmount', align: 'right', width: 150, headerSort:false },
            { title: "{{ trans('finance.balance') }}", field: 'balance', align: 'right', width: 150, headerSort:false },
            { title: "{{ trans('general.status') }}", field: 'paymentStatus', 'align': 'center', width:100, headerSort:false, align:"center", headerFilter:"select", headerFilterParams:{values:{"":"{{trans('documentManagementFolders.all')}}", "paid":"{{trans('finance.paid')}}", "pending":"{{trans('finance.pending')}}"}}, @if(!$limitToThisContractor) formatter:paymentStatusButtonFormatter @endif  },
            { title: "{{ trans('general.print') }}",'align': 'center', width: 100, headerSort:false, formatter: printButtonFormatter },
            @if(!$limitToThisContractor)
                { title: "{{ trans('general.send') }}", width: 100, headerSort:false, field: 'sendClaimCertLogCount', formatter: sendClaimCertificateFormatter },
                {title:"{{ trans('finance.invoice') }}", cssClass:"text-center", width: 70, headerSort: false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        innerHtml: function(rowData){
                            var output;
                            if(rowData['route:invoiceDownload']){
                                output = '<button data-action="showInvoiceAttachmentModal" data-route="'+rowData['route:invoiceDownload']+'" class="btn btn-xs btn-warning" title="{{ trans("general.attachments") }}"><i class="fa fa-download"></i></a>';
                            }
                            else{
                                output = '<a href="javascript:void(0);" class="btn btn-xs btn-warning disabled" title="{{ trans("general.download") }}"><i class="fa fa-download"></i></a>';
                            }
                            return output;
                        },
                    }
                },
                { title: "{{ trans('finance.invoiceInformation') }}", width: 120, headerSort: false, formatter: invoiceInformationFormatter },
                { title: "{{ trans('finance.accountingExport') }}", width: 120, headerSort:false, formatter: exportToAccountingColumFormatter },
                { title: "{{ trans('finance.paymentCollection') }}", field: 'paymentNotificationLogCount', width: 160, headerSort:false, formatter: paymentCollectionFormatter },
            @endif
        ];

        claimCertificateTable = new Tabulator('#claim-certificates-table', {
            height:480,
            columns: columns,
            layout:"fitColumns",
            ajaxURL: claimCertificateTableURL,
            ajaxConfig: "GET",
            movableColumns:true,
            pagination: "remote",
            paginationSize: 20,
            ajaxFiltering:true,
            placeholder:"{{{ trans('general.noRecordsFound') }}}",
            columnHeaderSortMulti:false,
        });

        $('#logsTableModal').on('shown.bs.modal', function(e) {
            logsTable = new Tabulator('#logsTable', {
                height:460,
                columns: [
                    { title: "id", field: 'id', visible:false, frozen:true },
                    { title: "{{ trans('users.name') }}", field: 'username', headerSort:false, headerFilter: 'input', headerFilterPlaceHolder: 'filter' },
                    { title: "{{ trans('general.date') . ' & ' . trans('general.time') }}", field: 'timestamp', width: 250, headerSort:false, headerFilter: 'input', headerFilterPlaceHolder: 'filter' },
                ],
                layout:"fitColumns",
                ajaxURL: $(this).data('url'),
                ajaxConfig: "GET",
                movableColumns:true,
                pagination:"local",
                placeholder:"{{{ trans('general.noRecordsFound') }}}",
                columnHeaderSortMulti:false,
            });
        });

        $('#logsTableModal').on('hidden.bs.modal', function(e) {
            logsTable.destroy();
            logsTable = null;
        });

        invoiceAttachmentsTable = new Tabulator("#invoiceAttachmentsTable", {
            layout: "fitColumns",
            placeholder: "{{ trans('general.noAttachments') }}",
            columns: columns_invoiceAttachmentsTable
        });

        $(document).on('click', '[data-action=showInvoiceAttachmentModal]', function(){
            invoiceAttachmentsTable.setData($(this).data('route'));
            $('#invoiceAttachmentsListModal').modal().show();
        });

        var exportDetailsColumnFormatter = function(cell, formatterParams, onRendered) {
            var viewExportDetailsButton = document.createElement('button');
            viewExportDetailsButton.id = 'btnViewExportDetails_' + cell.getRow().getData().id;
            viewExportDetailsButton.className = 'btn btn-xs btn-warning';
            viewExportDetailsButton.innerHTML = "{{ trans('general.view') }}";
            viewExportDetailsButton.style.width = '100%';
            viewExportDetailsButton.dataset.toggle = 'modal';
            viewExportDetailsButton.dataset.target = '#exportLogDetailsModal';

            viewExportDetailsButton.addEventListener('click', function(e) {
                $('#exportToAccountingLogDetailsModalTitle').html("{{{ trans('finance.exportDetails') }}}");
                $('#exportLogDetailsModal').data('url', cell.getRow().getData().route_viewExportDetails);
                $('#exportLogDetailsModal').data('apportionment_type_name', cell.getRow().getData().apportionmentTypeName);
            });

            return viewExportDetailsButton;
        };

        $('#exportToAccountingLogModal').on('shown.bs.modal', function(e) {
            exportToAccountingLogTable = new Tabulator('#exportToAccountingLogTable', {
                height:460,
                columns: [
                    { title: "{{ trans('general.no') }}", field: 'count', width: 50, headerSort:false, align: 'center', cssClass: 'text-center' },
                    { title: "{{ trans('users.name') }}", field: 'username', headerSort:false, align: 'left' },
                    { title: "{{ trans('general.date') }}", field: 'exportDate', width: 300, headerSort: false, align: 'center', cssClass: 'text-center' },
                    { title: "{{ trans('finance.exportDetails') }}", width: 100, headerSort: false, align: 'center', cssClass: 'text-center', formatter: exportDetailsColumnFormatter },
                ],
                layout:"fitColumns",
                ajaxURL: $(this).data('url'),
                ajaxConfig: "GET",
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
                pagination:"local",
            });
        });

        $('#exportToAccountingLogModal').on('hidden.bs.modal', function(e) {
            exportToAccountingLogTable.destroy();
            exportToAccountingLogTable = null;
        });

        $('#exportLogDetailsModal').on('shown.bs.modal', function(e) {
            var apportionmentTypeName = $(this).data('apportionment_type_name');

            exportLogDetailsTable = new Tabulator('#exportLogDetailsTable', {
                height:400,
                columns: [
                    { title: "{{ trans('subsidiaries.subsidiaryCode') }}", field: 'subsidiaryCode', width: 200, cssClass:"text-center", align: 'center', headerSort: false },
                    { title: "{{ trans('accountCodes.itemCode') }}", field: 'itemCodeDescription', cssClass:"text-center", align: 'center', headerSort: false },
                ],
                layout:"fitColumns",
                ajaxURL: $(this).data('url'),
                ajaxConfig: "GET",
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
                groupBy: function(data) {
                    return data.subsidiaryName + ' (' + data.proportion + ' %) ' + apportionmentTypeName + ' - ' + data.weightage;
                },
                groupHeader: function(value, count, data, group) {
                    return value;
                },
            });
        });

        $('#exportLogDetailsModal').on('hidden.bs.modal', function(e) {
            exportLogDetailsTable.destroy();
            exportLogDetailsTable = null;
        });

        function sendNotification(triggerElement, itemId, route) {
            app_progressBar.show();
            app_progressBar.maxOut();
            var successMessage = "{{ trans('finance.claimCertificateSent') }}";
            var errorMessage = "{{ trans('email.emailsCouldNotBeSent') }}";
            var partialURL = triggerElement.getAttribute('data-getClaimCertLogCountURL');
            var url = partialURL.replace('%7BclaimCertificateId%7D', itemId);

            $.ajax({
                type: "GET",
                url: route,
            }).then(function(data) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(newLogCount) {
                        claimCertificateTable.updateRow(itemId, {sendClaimCertLogCount: newLogCount});
                        app_progressBar.hide();

                        $.smallBox({
                            title : "Success",
                            content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                            color : "#739E73",
                            sound: true,
                            iconSmall : "fa fa-paper-plane",
                            timeout : 5000
                        });
                    },
                });
            }).fail(function() {
                $.smallBox({
                    title : "An error occurred",
                    content : "<i class='fa fa-close'></i> <i>" + errorMessage + "</i>",
                    color : "#C46A69",
                    sound: true,
                    iconSmall : "fa fa-exclamation-triangle shake animated"
                });
            });
        }

        $('#subsidiaryFilter').on('change', function() {
            claimCertificateTable.setData(claimCertificateTableURL, { subsidiaryId: this.options[this.selectedIndex].value });
        });

        var itemCodesSelectionFormatter = function(cell, formatterParams, onRendered) {
            var checkBoxString = '';

            var selectedItemCodeIds = cell.getRow().getData().selectedItemCodeIds ? cell.getRow().getData().selectedItemCodeIds : [];

            var checkedAttribute;

            cell.getRow().getData().itemCodeSettings.forEach(function(itemCodeSetting) {
                allItemCodeIds.add(itemCodeSetting.id);
                checkedAttribute = selectedItemCodeIds.includes(itemCodeSetting.id) ? 'checked' : '';
                checkBoxString += `<label class="checkbox" style="text-align: left;"><input type="checkbox" data-type="itemCodeSettingCheckbox" data-parent-id="${cell.getRow().getData().id}" data-ics-id="${itemCodeSetting.id}" `+checkedAttribute+`><i></i>${itemCodeSetting.description}</label>`;
            });

            return checkBoxString;
        }

        $('#exportToAccountingModal').on('shown.bs.modal', function(e) {
            allItemCodeIds.clear();
            var approvedPhaseSubsidiaryURL = $('#approvedPhaseSubsidiariesURL').val();
            var claimCertificateId = $('#claimCertificateId').val();

            var approvedPhaseSubsidiariesTableColumns = [
                { formatter:"rowSelection", titleFormatter:"rowSelection", align:"center", cssClass: "text-center", width: 30, headerSort:false },
                { title: "{{ trans('subsidiaries.name') }}", field: 'name', cssClass:"text-left", align: 'left', headerSort: false },
                { title: "{{ trans('subsidiaries.subsidiaryCode') }}", field: 'identifier', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                { title: "{{ trans('accountCodes.weightage') }}", field: 'weightage', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                { title: "{{ trans('accountCodes.proportion') }} (%)", field: 'proportion', width: 100, cssClass:"text-center", align: 'center', headerSort: false },
                { title: "{{ trans('accountCodes.itemCodes') }}", field: 'itemCodes', width: 350, cssClass:"text-center", align: 'center', headerSort: false, formatter: itemCodesSelectionFormatter },
            ];

            approvedPhaseSubsidiariesTable = new Tabulator('#approvedPhaseSubsidiariesTable', {
                height:400,
                columns: approvedPhaseSubsidiariesTableColumns,
                layout:"fitColumns",
                ajaxURL: approvedPhaseSubsidiaryURL,
                ajaxParams: {},
                ajaxConfig: "GET",
                placeholder:"{{ trans('general.noDataAvailable') }}",
                columnHeaderSortMulti:false,
                tableBuilt: function() {
                    this.deleteColumn('weightage');
                    this.addColumn({ title: $('#apportionmentTypeName').val(), field: 'weightage', width: 150, cssClass:"text-center", align: 'center', headerSort: false }, true, "proportion");
                },
                dataLoaded: function() {
                    allIds = [];

                    $.ajax({
                        type: "GET",
                        url: "{{ route('finance.claim-certificate.account.report.export.last.selected.options.get') }}".replace('%7BclaimCertificateId%7D', claimCertificateId),
                        success: function(data) {
                            approvedPhaseSubsidiariesTable.selectRow(Object.keys(data));
                            var row;
                            for(var i in data){
                                row = approvedPhaseSubsidiariesTable.getRow(i);
                                row.update({selectedItemCodeIds: data[i]});
                                row.reformat();
                            }
                        }
                    });
                },
                rowSelectionChanged:function(data, rows){
                    var selectedIds = [];

                    $('#btnExportToAccounting').prop('disabled', (data.length == 0));

                    data.forEach(function(rowData) {
                        selectedIds.push(rowData.id);
                    });

                    var projectId = $('#projectId').val();
                    var url = "{{ route('project.code.settings.proportion.get') }}".replace('%7BprojectId%7D', projectId);

                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {
                            selectedIds: selectedIds,
                        },
                        success: function(response) {
                            response = JSON.parse(response);

                            data.forEach(function(rowData) {
                                approvedPhaseSubsidiariesTable.updateRow(rowData.id, { proportion: response[rowData.id] });
                                approvedPhaseSubsidiariesTable.redraw();
                            });

                            var uncheckedIds = allIds.diff(selectedIds);

                            uncheckedIds.forEach(function(id) {
                                approvedPhaseSubsidiariesTable.updateRow(id, { proportion: '0.00' });
                                approvedPhaseSubsidiariesTable.redraw();
                            });
                        }
                    });
                },
            });
        });

        $('#approvedPhaseSubsidiariesTable').on('change', 'input[type=checkbox][data-type=itemCodeSettingCheckbox]', function(){
            var row = approvedPhaseSubsidiariesTable.getRow($(this).data('parentId'));

            var selectedItemCodeIds = row.getData()['selectedItemCodeIds'] ? row.getData()['selectedItemCodeIds'] : [];
            if($(this).prop('checked')){
                if(!selectedItemCodeIds.includes($(this).data('icsId'))) selectedItemCodeIds.push($(this).data('icsId'));
            }
            else{
                var index = selectedItemCodeIds.indexOf($(this).data('icsId'));
                if (index > -1) {
                  selectedItemCodeIds.splice(index, 1);
                }
            }
            row.update({selectedItemCodeIds: selectedItemCodeIds});
        });

        $('#btnExportToAccounting').on('click', function() {
            var checkedItemCodes = {};
            var selectedSubsidiariesForAccountingExport = approvedPhaseSubsidiariesTable.getSelectedData();
            var exportToAccountingURL = $('#exportAccountingRoute').val();
            var hasErrors = false;
            var row;
            var checkedIds;

            for(var i = 0; i < selectedSubsidiariesForAccountingExport.length; i++) {
                row = approvedPhaseSubsidiariesTable.getRow(selectedSubsidiariesForAccountingExport[i].id);
                checkedIds = row.getData()['selectedItemCodeIds'] ? row.getData()['selectedItemCodeIds'] : [];

                if(checkedIds.length === 0) {
                    $('#warningMessageId').html("{{ trans('accountCodes.atLeastOneItemCodeMustBeSelected') }}");
                    $('#warningModal').modal('show');
                    hasErrors = true;

                    break;
                }

                checkedItemCodes[selectedSubsidiariesForAccountingExport[i].id] = checkedIds;
            }

            if(hasErrors) return false;

            var uniqueCheckedItemCodes = new Set();
            var paramStrings           = [];

            for(var pcsId in checkedItemCodes) {
                checkedItemCodes[pcsId].forEach(function(id) {
                    uniqueCheckedItemCodes.add(id);
                });

                paramStrings.push(`${pcsId}[${checkedItemCodes[pcsId].join('|')}]`);
            }

            if(uniqueCheckedItemCodes.size !== allItemCodeIds.size) {
                $('#warningMessageId').html("{{ trans('accountCodes.eachItemCodeMustBeSelectedOnce') }}");
                $('#warningModal').modal('show');

                return false;
            }

            exportToAccountingURL += '?projectCodeSettingIds=' + paramStrings;

            function defer (callback) {
                var channel = new MessageChannel();
                channel.port1.onmessage = function (e) {
                    callback();
                };
                channel.port2.postMessage(null);
            }

            var w = window.open(exportToAccountingURL, '_blank');

            w.addEventListener("unload", function () {
                defer(function () {
                    if (w.document.readyState === "loading") {
                        w.addEventListener("load", function () {});
                    } else {
                        var partialURL = $('#exportToAccountLatesLogCountURL').val();
                        var exportToAccountLatesLogCountURL = partialURL.replace('%7BclaimCertificateId%7D', $('#claimCertificateId').val());

                        $.ajax({
                            type: "GET",
                            url: exportToAccountLatesLogCountURL,
                            success: function(newLogCount) {
                                claimCertificateTable.updateRow($('#claimCertificateId').val() , { exportAccountingLogCount: newLogCount });
                                claimCertificateTable.redraw(true);
                            }
                        });
                    }
                });
            });
        });

        $(document).on('click', '[data-action=send-payment-collection-notification]', function(e){
            app_progressBar.show();
            app_progressBar.maxOut();
            var successMessage = "{{ trans('finance.paymentNotificationSent') }}";
            var errorMessage = "{{ trans('email.emailsCouldNotBeSent') }}";
            var itemId = $(this).attr('data-item-id');
            var partialURL = $(this).attr('data-getpaymentnotificationlogcounturl');
            var url = partialURL.replace('%7BclaimCertificateId%7D', itemId);

            $.ajax({
                type: "GET",
                url: $(this).data('route'),
            }).then(function(data) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(newLogCount) {
                        claimCertificateTable.updateRow(itemId, {paymentNotificationLogCount: newLogCount});
                        app_progressBar.hide();

                        $.smallBox({
                            title : "Success",
                            content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                            color : "#739E73",
                            sound: true,
                            iconSmall : "fa fa-paper-plane",
                            timeout : 5000
                        });
                    },
                });
            }).fail(function() {
                $.smallBox({
                    title : "An error occurred",
                    content : "<i class='fa fa-close'></i> <i>" + errorMessage + "</i>",
                    color : "#C46A69",
                    sound: true,
                    iconSmall : "fa fa-exclamation-triangle shake animated"
                });
            });
        });
    });
</script>
@endsection