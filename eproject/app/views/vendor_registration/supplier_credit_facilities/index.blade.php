@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.supplierCreditFacilities') }}}</li>
    </ol>
@endsection

@section('css')
<style>
    .spaced {
        margin-right: 5px;
    }
</style>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorManagement.supplierCreditFacilities') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('vendors.vendorRegistration.supplierCreditFacilities.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.supplierCreditFacilities') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        @if(!empty($instructionSettings->supplier_credit_facilities))
                        <div class="padded label-success text-white"><strong>{{ nl2br($instructionSettings->supplier_credit_facilities) }}</strong></div>
                        <br>
                        @endif
                        @if(!empty($section->amendment_remarks))
                        <div class="well @if($section->amendmentsRequired()) border-danger @elseif($section->amendmentsMade()) border-warning @endif">
                            {{ nl2br($section->amendment_remarks) }}
                        </div>
                        @endif
                        <div id="main-table"></div>
                        <footer>
                            @include('vendor_management.partials.link_to_next_registration_section', ['currentSection' => 'supplierCreditFacilities'])
                            {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default pull-right spaced')) }}
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if($setting->has_attachments)
    @include('templates.generic_table_modal', [
        'modalId'    => 'attachmentsModal',
        'title'      => trans('general.attachments'),
        'tableId'    => 'attachmentsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
@endif
@endsection

@section('js')
    <script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            @if($setting->has_attachments)
            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                if(!data.hasOwnProperty('id')) return null;

				var downloadAttachmentsButton = document.createElement('a');
                downloadAttachmentsButton.dataset.toggle = 'modal';
                downloadAttachmentsButton.dataset.target = '#attachmentsModal';
                downloadAttachmentsButton.dataset.url = data['route:attachments'];
                downloadAttachmentsButton.dataset.action = 'upload-item-attachments';
				downloadAttachmentsButton.title = "{{ trans('general.attachments') }}";
                downloadAttachmentsButton.className = 'btn btn-xs btn-info';
                downloadAttachmentsButton.innerHTML = `<i class="fas fa-paperclip"></i> (${data.attachments_count})`;

				return downloadAttachmentsButton;
			}
            @endif

            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.supplierName') }}", field:"name", minWidth: 350, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('vendorManagement.creditFacilities') }}", field:"facilities", width: 350, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    @if($setting->has_attachments)
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter},
                    @endif
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:edit'},
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorPreQualification.updateItem") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                innerHtml: function(rowData){
                                    if(rowData['deletable'])
                                    {
                                        return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                    }

                                    return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                                }
                            },
                        ]
                    }}
                ],
            });

            @if($setting->has_attachments)
            $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
                e.preventDefault();

                $('#attachmentsModal').data('url', $(this).data('url'));
                $('#attachmentsModal').modal('show');
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

            $('#attachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                var attachmentsTable = new Tabulator('#attachmentsTable', {
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
            @endif
        });
    </script>
@endsection