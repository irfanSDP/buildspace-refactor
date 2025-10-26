@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.declarationAndSubmission') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-address-book"></i> {{{ trans('vendorManagement.vendorRegistration') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.declarationAndSubmission') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::open(array('route' => array('vendors.vendorRegistration.update'), 'class' => 'smart-form')) }}
                            <div class="widget-body">
                                <ul id="myTab1" class="nav nav-tabs bordered">
                                    <li class="active">
                                        <a href="#company-details" data-toggle="tab">{{ trans('vendorProfile.companyDetails') }}</a>
                                    </li>
                                    <li>
                                        <a href="#registration-data" data-toggle="tab">{{ trans('vendorManagement.vendorRegistration') }}</a>
                                    </li>
                                    <li>
                                        <a href="#company-personnel" data-toggle="tab">{{ trans('vendorManagement.companyPersonnel') }}</a>
                                    </li>
                                    <li>
                                        <a href="#project-track-record" data-toggle="tab">{{ trans('vendorManagement.projectTrackRecord') }}</a>
                                    </li>
                                    @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
                                    <li>
                                        <a href="#pre-qualification" data-toggle="tab">{{ trans('vendorManagement.preQualification') }}</a>
                                    </li>
                                    @endif
                                    @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_SUPPLIER_CREDIT_FACILITIES'))
                                    <li>
                                        <a href="#supplier-credit-facilities" data-toggle="tab">{{ trans('vendorManagement.supplierCreditFacilities') }}</a>
                                    </li>
                                    @endif
                                    @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_VENDOR_PAYMENT'))
                                    <li>
                                        <a href="#vendor-registration-payment" data-toggle="tab">{{ trans('vendorManagement.vendorRegistrationPayment') }}</a>
                                    </li>
                                    @endif
                                </ul>
                                <div id="myTabContent1" class="tab-content padding-10">
                                    <div class="tab-pane fade in active" id="company-details">
                                        @foreach($companyDetails as $companyDetail)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $companyDetail['label'] }}</label>
                                                <label>
                                                    @if(isset($companyDetail['values']))
                                                    <ul>
                                                        @foreach($companyDetail['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </label>
                                            </section>
                                            @if($companyDetail['enable_attachments'])
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $companyDetail['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $companyDetail['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade in" id="registration-data">
                                        @foreach($vendorRegistrationDetails as $registrationDetails)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $registrationDetails['label'] }}</label>
                                                <label>
                                                    @if(isset($registrationDetails['values']))
                                                    <ul>
                                                        @foreach($registrationDetails['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                    @if(isset($registrationDetails['attachments']))
                                                        <button type="button" class="btn btn-primary pull-right" data-action="view-attachments" data-url="{{ $registrationDetails['attachments']['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $registrationDetails['attachments']['attachments_count'] }}</span>) {{ trans('formBuilder.filesUploaded') }}</button>
                                                    @endif
                                                </label>
                                            </section>
                                            @if(isset($registrationDetails['route_attachments']) && $registrationDetails['enable_attachments'])
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $registrationDetails['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $registrationDetails['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="company-personnel">
                                        @foreach($companyPersonnels as $companyPersonnel)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $companyPersonnel['label'] }}</label>
                                                <label>
                                                    @if(isset($companyPersonnel['values']))
                                                    <ul>
                                                        @foreach($companyPersonnel['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </label>
                                            </section>
                                            @if($companyPersonnelSetting->has_attachments)
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $companyPersonnel['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $companyPersonnel['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="project-track-record">
                                        @foreach($projectTrackRecords as $trackRecord)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $trackRecord['label'] }}</label>
                                                <label>
                                                    @if(isset($trackRecord['values']))
                                                    <ul>
                                                        @foreach($trackRecord['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </label>
                                            </section>
                                            @if(isset($trackRecord['route_attachments']))
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $trackRecord['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $trackRecord['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="pre-qualification">
                                        @foreach($preQualificationDownloads as $download)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $download['label'] }}</label>
                                                <label>
                                                    @if(isset($download['values']))
                                                    <ul>
                                                        @foreach($download['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                    @if(isset($download['attachments']))
                                                        <button type="button" class="btn btn-primary pull-right" data-action="view-attachments" data-url="{{ $download['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $download['attachments_count'] }}</span>) {{ trans('formBuilder.filesUploaded') }}</button>
                                                    @endif
                                                </label>
                                            </section>
                                            @if(isset($download['route_attachments']))
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $download['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $download['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="supplier-credit-facilities">
                                        @foreach($supplierCreditFacilities as $facility)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $facility['label'] }}</label>
                                                <label>
                                                    @if(isset($facility['values']))
                                                    <ul>
                                                        @foreach($facility['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </label>
                                            </section>
                                            @if($supplierCreditFacilitySetting->hasAttachments)
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $facility['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $facility['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="vendor-registration-payment">
                                        @foreach($vendorRegistrationPayments as $payment)
                                        <div class="row padded smart-form registration-details">
                                            <section class="col col-6">
                                                <label class="label">{{ $payment['label'] }}</label>
                                                <label>
                                                    @if(isset($payment['values']))
                                                    <ul>
                                                        @foreach($payment['values'] as $value)
                                                        <li>{{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </label>
                                            </section>
                                            @if(isset($payment['route_attachments']))
                                            <section class="col col-2">
                                                <label>
                                                    <button type="button" class="btn btn-primary btn-xs pull-right" data-action="view-attachments" data-url="{{ $payment['route_attachments'] }}"><i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $payment['attachments_count'] }}</span>)</button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="label">{{ ! empty($settings->vendor_declaration) ? $settings->vendor_declaration : trans('vendorManagement.declarationMessage') }}</label>
                                    <label class="checkbox">
                                        {{ Form::checkbox('confirm', 1, false, array('id' => 'confirm')) }}

                                        <i></i>{{ trans('vendorManagement.iConfirm') }}
                                    </label>
                                </section>
                            </div>
                            <footer>
                                {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.generic_table_modal', [
    'modalId'    => 'attachmentsModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'attachmentsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@endsection
@section('js')
    <script>
        $('input[type=checkbox]').on('change', function(){
            $('button[type=submit]').prop('disabled', !$(this).prop('checked'));
        });

        $('button[type=submit]').prop('disabled', true);

        var attachmentsTable = null;

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

            $('[data-action="view-attachments"]').on('click', function(e) {
                e.preventDefault();

                $('#attachmentsModal').data('url', $(this).data('url'));
                $('#attachmentsModal').modal('show');
            });

            $('#attachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                attachmentsTable = new Tabulator('#attachmentsTable', {
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
    </script>
@endsection