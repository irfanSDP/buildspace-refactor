@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.registrationAndPreQualification') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.registrationAndPreQualification') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.registrationAndPreQualification') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    <button type="submit" class="btn btn-warning pull-right" data-toggle="modal" data-target="#processorDeleteCompanyModal"><i class="fas fa-list"></i> {{ trans('vendorManagement.deletedCompaniesLogs') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.generic_table_modal', [
    'modalId'    => 'processorDeleteCompanyModal',
    'title'      => trans('vendorManagement.companiesDeletedByProcessors'),
    'tableId'    => 'processorDeleteCompanyTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var expiryDateFormatter = function(cell, formatterParams, onRendered) {
                const data = cell.getRow().getData();
                const textClass = (data.expiry_date && data.expiry_alert) ? 'text-danger' : null;

                return `<span class="${textClass}">${data.expiry_date}</span>`;
            }

            var mainTable = new Tabulator('#main-table', {
                fillHeight: true,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.approval.registrationAndPreQualification.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                    {title:"{{ trans('vendorManagement.company') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, frozen:true},
                    {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", minWidth: 250, hozAlign:'left', headerSort:false, headerFilter: true, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions) }}},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", minWidth: 450, hozAlign:'left', headerSort:false},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width: 150, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($statusFilterOptions) }}},
                    {title:"{{ trans('vendorManagement.expiryDate') }}", field:"expiry_date", width: 150, cssClass:"text-center text-middle", headerSort:true, formatter:expiryDateFormatter },
                    {title:"{{ trans('vendorManagement.submissionType') }}", field:"submission_type", width: 150, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($submissionTypeFilterOptions) }}},
                    {title:"{{ trans('vendorManagement.submissionDate') }}", field:"submitted_date", width: 250, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"input", headerFilter:true },
                    {title:"{{ trans('vendorManagement.processor') }}", field:"processor", width: 200, cssClass:"text-center text-middle", headerSort:true, editable: false, headerFilter:true },
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:view'},
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("general.view") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'}
                                }
                            },{
                                innerHtml: function(rowData){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['route:assign'];
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:assign'},
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorManagement.assign") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-play text-white'}
                                }
                            }
                        ]
                    }}
                ],
            });

            var processorDeleteCompanyTable = null;

            $('#processorDeleteCompanyModal').on('show.bs.modal', function(e) {
                processorDeleteCompanyTable = new Tabulator('#processorDeleteCompanyTable', {
                    height:450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('vendorManagement.approval.registrationAndPreQualification.processorDeletedCompanyLogs') }}",
                    ajaxConfig: "GET",
                    paginationSize: 50,
                    pagination: "remote",
                    ajaxFiltering:true,
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.company') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                        {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", minWidth: 250, hozAlign:'left', headerSort:false, headerFilter: true, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions) }}},
                        {title:"{{ trans('vendorManagement.processor') }}", field:"processor", width: 200, cssClass:"text-center text-middle", headerSort:true, editable: false, headerFilter:true },
                    ],
                });
            });
        });
    </script>
@endsection