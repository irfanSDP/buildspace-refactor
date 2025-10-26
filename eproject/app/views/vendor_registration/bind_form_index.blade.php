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
		<li>{{ trans('formBuilder.formsLibrary') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('formBuilder.formsLibrary') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('formBuilder.formTemplates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="vendor-registration-form-mapping-table"></div>
			</div>
		</div>
	</div>

	@include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'message'   => trans('formBuilder.sureToUnlinkForm'),
    ])
	@include('templates.generic_table_modal', [
		'modalId'    => 'formSelectionsModal',
		'title'      => trans('formBuilder.linkForm'),
		'tableId'    => 'formSelectionsTable',
        'showSubmit' => true,
		'showCancel' => true,
		'cancelText' => trans('forms.close'),
	])
@endsection

@section('js')
	<script>
		$(document).ready(function() {
			var vendorRegistrationFormMappingTable = null;
			var formSelectionsTable = null;

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var container = document.createElement('div');

				var linkFormTemplateButton = document.createElement('a');
				linkFormTemplateButton.dataset.toggle = 'tooltip';
				linkFormTemplateButton.title = "{{ trans('formBuilder.linkForm') }}";
                linkFormTemplateButton.className = 'btn btn-xs btn-primary';
                linkFormTemplateButton.innerHTML = '<i class="fas fa-link"></i>';
                linkFormTemplateButton.style['margin-right'] = '5px';
                linkFormTemplateButton.dataset.toggle = 'modal';
                linkFormTemplateButton.dataset.target = '#formSelectionsModal';

                linkFormTemplateButton.addEventListener('click', function(e) {
					e.preventDefault();

					$('#formSelectionsModal [data-action="actionSave"]').data('contract_group_category_id', cell.getRow().getData().contract_group_category_id);
					$('#formSelectionsModal [data-action="actionSave"]').data('business_entity_type_id', cell.getRow().getData().business_entity_type_id);

                    if(cell.getRow().getData().hasOwnProperty('dynamic_form_id')) {
                        $('#formSelectionsModal').data('dynamic_form_id', cell.getRow().getData().dynamic_form_id);
                    } else {
                        $('#formSelectionsModal').data('dynamic_form_id', null);
                    }
                });

                container.appendChild(linkFormTemplateButton);
                
                if(cell.getRow().getData().hasOwnProperty('dynamic_form_id')) {
                    var unlinkFormTemplateButton = document.createElement('a');
                    unlinkFormTemplateButton.dataset.toggle = 'tooltip';
                    unlinkFormTemplateButton.title = "{{ trans('formBuilder.unlinkForm') }}";
                    unlinkFormTemplateButton.className = 'btn btn-xs btn-danger';
                    unlinkFormTemplateButton.innerHTML = '<i class="fas fa-unlink"></i>';
                    unlinkFormTemplateButton.style['margin-right'] = '5px';
                    unlinkFormTemplateButton.dataset.toggle = 'modal';
                    unlinkFormTemplateButton.dataset.target = '#yesNoModal';

                    unlinkFormTemplateButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#yesNoModal [data-action=actionYes]').data('route_unlink', cell.getRow().getData().route_unlink);
                    });
                    
                    container.appendChild(unlinkFormTemplateButton);
                }

				return container;
			}

			var revisionFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().revision == 0)
				{
					return "{{ trans('formBuilder.original') }}";
				}

				return cell.getRow().getData().revision;
			}

			vendorRegistrationFormMappingTable = new Tabulator('#vendor-registration-form-mapping-table', {
                height:500,
				pagination:"local",
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('users.userType') }}", field: 'contract_group_category_name', width: 280, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('businessEntityTypes.businessEntityType') }}", field: 'business_entity_type_name', width: 280, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('formBuilder.formName') }}", field: 'dynamic_form_name', hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('formBuilder.revision') }}", field: 'dynamic_form_revision', width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: revisionFormatter },
                    { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter },
				],
                layout:"fitColumns",
				ajaxURL: "{{ route('vendor.registration.form.mappings.get') }}",
                movableColumns:true,
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
            });

            $('#formSelectionsModal').on('shown.bs.modal', function (e) {
                e.preventDefault();

                formSelectionsTable = new Tabulator('#formSelectionsTable', {
                    height:300,
                    columns: [
                        { formatter:"rowSelection", width:30, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false },
                        { title: "{{ trans('formBuilder.formName') }}", field:"name", cssClass:"text-left", align: 'left', headerSort: false, headerFilter: 'input' },
						{ title: "{{ trans('formBuilder.revision') }}", field: 'revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: revisionFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('vendor.registration.form.selections.get') }}",
                    ajaxConfig: "GET",
                    pagination:"local",
                    placeholder:"{{{ trans('general.noRecordsFound') }}}",
                    columnHeaderSortMulti:false,
                    selectable: 1,
                    selectableRollingSelection:true,
                    dataLoaded: function(data) {
                        var dynamicFormId = $('#formSelectionsModal').data('dynamic_form_id');

                        if(dynamicFormId == null) {
                            return;
                        }

                        this.selectRow(dynamicFormId);
                    },
                    rowSelectionChanged:function(data, rows){
                        $('#formSelectionsModal [data-action="actionSave"]').prop('disabled', (rows.length == 0));
                    },
                });
            });

            $(document).on('click', '#formSelectionsModal [data-action="actionSave"]', function(e) {
                e.preventDefault();

                var selectedData = formSelectionsTable.getSelectedData()[0];
                var contractGroupCategoryId = $(this).data('contract_group_category_id');
                var businessEntityTypeId = $(this).data('business_entity_type_id');

                $.ajax({
                    url: "{{ route('vendor.registration.form.mapping.form.link') }}",
                    method: 'POST',
                    data: {
                        contractGroupCategoryId: contractGroupCategoryId,
                        businessEntityTypeId: businessEntityTypeId,
                        formId:selectedData.id,
                        _token: _csrf_token,
                    },
                    success: function (data) {
                        if (data.success) {
							vendorRegistrationFormMappingTable.setData();
                            $('#formSelectionsModal').modal('hide');
                        }
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });

			$(document).on('click', '#yesNoModal [data-action="actionYes"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

                var url = $(this).data('route_unlink');

				$.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: _csrf_token,
                    },
                    success: function (data) {
                        if (data.success) {
							vendorRegistrationFormMappingTable.setData();
                            $('#yesNoModal').modal('hide');
                        }
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });
		});
	</script>
@endsection