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
		<li>{{{ trans('vendorManagement.vendorPaymentMasterList') }}}</li>
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

    <div class="jarviswidget ">
		<header>
			<h2> {{ trans('vendorManagement.vendorPaymentMasterList') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="payment-list-table"></div>
			</div>
		</div>
	</div>
@include('templates.generic_table_modal', [
    'modalId'    => 'payment-proof-modal',
    'title'      => trans('vendorManagement.proofOfPayment'),
    'tableId'    => 'payment-proof-table',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@include('uploads.uploadModal')
@endsection

@section('js')
    <script>
        $(document).ready(function() {
			var dateFormatter = function(cell, formatterParams, onRendered) {
				return cell.getValue() ? cell.getValue() : "{{ trans('general.no') }}";
			}

            var paymentListTable = new Tabulator('#payment-list-table', {
                height:500,
                columns: [
					{ title:"{{ trans('general.no') }}", field: 'counter', width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('companies.company') }}", field: 'company', minWidth:350, cssClass:"text-center text-left header-filter-rowspan-2", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('general.bank') }}", field: 'bank', width:150, cssClass:"text-center text-middle header-filter-rowspan-2", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('vendorManagement.virtualAccountNumber') }}", field: 'virtual_account_number', width: 150, hozAlign:'center', cssClass:"text-center text-middle header-filter-rowspan-2", headerSort:false, headerFilter:"input" },
					{ title:"{{ trans('vendorManagement.submitted') }}", cssClass:"text-center text-middle", columns:[
                        {title:"{{ trans('general.date') }}", field: 'submitted', width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($yesNoSelection) }}, formatter:dateFormatter},
                        {title:"{{ trans('vendorManagement.proofOfPayment') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: [
                                {
                                    innerHtml: {
                                        tag: 'button',
                                        rowAttributes: {'data-id':'company_id'},
                                        attributes: {'class': 'btn btn-xs btn-primary', 'data-action':'show-payment-proof'},
                                        innerHtml: function(rowData){
                                            return '<i class="fas fa-paperclip"></i> ('+rowData['payment_proof_attachment_count']+')';
                                        }
                                    }
                                }
                            ]
                        }}
                    ] },
					{ title:"{{ trans('vendorManagement.paid') }}", cssClass:"text-center text-middle", columns:[
                        { title:"{{ trans('general.date') }}", field: 'paid', width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($yesNoSelection) }}, formatter:dateFormatter },
                        {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: [
                                {
                                    show: function(cell){
                                        return cell.getData()['route:do_upload_paid'];
                                    },
                                    tag:'button',
                                    attributes: {type:'button', 'class':'btn btn-xs btn-info', 'data-action':'upload-item-attachments'},
                                    rowAttributes: {'data-do-upload':'route:do_upload_paid', 'data-get-uploads':'route:get_uploads_paid'},
                                    innerHtml: function(rowData){
                                        return '<i class="fas fa-paperclip"></i> ('+rowData['paid_attachments_count']+')';
                                    },
                                }
                            ]
                        }},
                    ]},
					{ title:"{{ trans('vendorManagement.completed') }}", cssClass:"text-center text-middle", columns:[
                        { title:"{{ trans('general.date') }}", field: 'completed', width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($yesNoSelection) }}, formatter:dateFormatter },
                        {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: [
                                {
                                    show: function(cell){
                                        return cell.getData()['route:do_upload_completed'];
                                    },
                                    tag:'button',
                                    attributes: {type:'button', 'class':'btn btn-xs btn-info', 'data-action':'upload-item-attachments'},
                                    rowAttributes: {'data-do-upload':'route:do_upload_completed', 'data-get-uploads':'route:get_uploads_completed'},
                                    innerHtml: function(rowData){
                                        return '<i class="fas fa-paperclip"></i> ('+rowData['completed_attachments_count']+')';
                                    },
                                }
                            ]
                        }},
                    ]}
				],
                layout:"fitColumns",
				ajaxURL: "{{ route('vendor.registration.payments.master.list.get') }}",
                movableColumns:true,
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
				pagination: "remote",
                ajaxFiltering:true,
            });

            var paymentProofTable = new Tabulator('#payment-proof-table', {
                height:500,
                pagination: "remote",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                    {title:"{{ trans('general.download') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'a',
                                    rowAttributes: {href: 'download_url', download: 'filename'},
                                    attributes: {'class': 'btn btn-xs btn-primary', 'data-action':'download'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {title: "{{ trans('general.view') }}", class: 'fas fa-download'}
                                    }
                                }
                            }
                        ]
                    }}
                ],
            });

            $('#payment-list-table').on('click', '[data-action=show-payment-proof]', function(){
                var data = paymentListTable.getRow($(this).data('id')).getData();
                if(data['payment_proof_attachment_count'] > 0){
                    paymentProofTable.setData(data['route:payment_proof']);
                    $('#payment-proof-modal').modal('show');
                }
            });
        });
    </script>
@endsection