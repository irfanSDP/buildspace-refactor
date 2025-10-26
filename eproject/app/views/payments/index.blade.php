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
		<li>{{ trans('payment.paymentSettings') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-credit-card"></i> {{ trans('payment.paymentSettings') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnAddNewPaymentSetting" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#paymentSettingEditorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('payment.bankAccount') }}
			</a>
		</div>
	</div>

    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{ trans('payment.bankAccounts') }} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div id="payments-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

	@include('payments.partials.editor_modal')
	@include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'message'   => trans('general.areYouSure') . ' ' . trans('payment.cannotBeUndone'),
    ])
@endsection

@section('js')
	<script>
		$(document).ready(function() {
            // Bank accounts
			var paymentsTable = null;

			var actionsFormatter = function(cell, formatterParams, onRendered) {
				var editPaymentSettingsButton = document.createElement('a');
                editPaymentSettingsButton.id = 'btnEditPaymentSetting_' + cell.getRow().getData().id;
                editPaymentSettingsButton.className = 'btn btn-xs btn-warning';
				editPaymentSettingsButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
				editPaymentSettingsButton.style['margin-right'] = '5px';
                editPaymentSettingsButton.dataset.url = cell.getRow().getData().route_edit;
                editPaymentSettingsButton.dataset.name = cell.getRow().getData().name;
                editPaymentSettingsButton.dataset.accountNumber = cell.getRow().getData().accountNumber;

				var deletepaymentSettingsButton = document.createElement('a');
				deletepaymentSettingsButton.id = 'btnDeleteApportionmentType_' + cell.getRow().getData().id;
				deletepaymentSettingsButton.className = 'btn btn-xs btn-danger';
				deletepaymentSettingsButton.innerHTML = '<i class="fa fa-trash"></i>';
                deletepaymentSettingsButton.dataset.csrf_token = "{{ csrf_token() }}";
				deletepaymentSettingsButton.style['margin-right'] = '5px';

				deletepaymentSettingsButton.addEventListener('click', function(e) {
                    e.preventDefault();

					$('#yesNoModal [data-action=actionYes]').data('url', cell.getRow().getData().route_delete);
                    $('#yesNoModal').modal('show');
                });
			
				var container = document.createElement('div');
				container.appendChild(editPaymentSettingsButton);
				container.appendChild(deletepaymentSettingsButton);

				return container;
			}

			paymentsTable = new Tabulator('#payments-table', {
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('general.name') }}", field: 'name', hozAlign:'left', headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('payment.accountNumber') }}", field: 'accountNumber', width: 200, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter },
				],
                layout: 'fitColumns',
                height: 400,
				ajaxURL: "{{ route('payment.settings.records.get') }}",
                pagination: 'local',
                paginationSize: 10,
                placeholder: "{{ trans('general.noDataAvailable') }}",
                movableColumns: true,
                columnHeaderSortMulti: false,
            });

			$('#paymentSettingEditorModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

			function selectInputField() {
                $('#setting-name-input').select();
            }

			function disableSubmit(disable) {
                $('#submit-button').prop('disabled', disable);
            }

			$(document).on('click', '#submit-button', function () {
                disableSubmit(true);
                submit($(this).data('url'), getPaymentSettingNameInputValue(), getPaymentSettingAccountNumberInputValue());
            });

			function changeEditorModalTitle(title) {
                $('#editorLabel').text(title);
            }

			function setPaymentSettingNameInputValue(name) {
                $('#setting-name-input').val(name);
            }

			function setPaymentSettingAccountNumberInputValue(accountNumber) {
                $('#account-number-input').val(accountNumber);
            }

			function getPaymentSettingNameInputValue() {
                return $('#setting-name-input').val();
            }

            function getPaymentSettingAccountNumberInputValue() {
                return $('#account-number-input').val();
            }

			function setSubmitButtonURL(url) {
				$('#submit-button').data('url', url);
			}

			function getSubmitButtonURL() {
				return $('#submit-button').data('url');
			}

			function showEditorModal() {
                $('#paymentSettingEditorModal').modal('show');
            }

			function hideEditorModal() {
				$('#paymentSettingEditorModal').modal('hide');
			}

			/* Errors */
			function setPaymentSettingNameError(error) {
                $('#setting-name-error').text(error);
            }

            function setPaymentSettingAccountNumberError(error) {
                $('#account-number-error').text(error);
            }

			/* Create */
            $(document).on('click', '#btnAddNewPaymentSetting', function (e) {
				e.preventDefault();

                changeEditorModalTitle("{{ trans('payment.newPaymentSetting') }}");
				setPaymentSettingNameInputValue('');
                setPaymentSettingAccountNumberInputValue('');
				setPaymentSettingNameError('');
                setPaymentSettingAccountNumberError('');
				setSubmitButtonURL("{{ route('payment.settings.store') }}");
            });

			/* Edit */
			$(document).on('click', '[id^=btnEditPaymentSetting_]', function(e) {
				e.preventDefault();

				changeEditorModalTitle("{{ trans('payment.editPaymentSetting') }}");
				setPaymentSettingNameInputValue($(this).data('name'));
                setPaymentSettingAccountNumberInputValue($(this).data('accountNumber'));
				setSubmitButtonURL($(this).data('url'));
				setPaymentSettingNameError('');
                setPaymentSettingAccountNumberError('');
				showEditorModal();
			});

			function submit(url, name, accountNumber) {
                setPaymentSettingNameError('');
                setPaymentSettingAccountNumberError('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        name: name.trim(),
                        accountNumber: accountNumber.trim(),
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (data) {
                        if (data.success) {
                            hideEditorModal();
							paymentsTable.setData();
                        }
                        else {
                            setPaymentSettingNameError(data['errors']['name']);
                            setPaymentSettingAccountNumberError(data['errors']['accountNumber']);
                            disableSubmit(false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            $(document).on('click', '#yesNoModal [data-action=actionYes]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (response) {
                        if(response.success) {
                            paymentsTable.setData();
                        }

                        $('#yesNoModal').modal('hide');
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });
		});
	</script>
@endsection