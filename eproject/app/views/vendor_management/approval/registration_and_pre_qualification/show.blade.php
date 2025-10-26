@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{{ $vendorRegistration->company->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-address-book"></i> {{{ trans('vendorManagement.registrationAndPreQualification') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $vendorRegistration->company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    @if(!empty($vendorRegistration->getProcessorRemarks()))
                    @if($vendorRegistration->isDraft())
                        <div class="well border-danger text-danger">
                            {{{ $vendorRegistration->getProcessorRemarks() }}}
                        </div>
                    @else
                        <div class="well border-info text-info">
                            {{{ $vendorRegistration->getProcessorRemarks() }}}
                        </div>
                    @endif
                    <br/>
                    @endif
                    <div id="main-table"></div>
                    @if($canBeDeleted)
                    <form action="{{ route('vendorManagement.approval.vendorRegistration.company.delete', [$vendorRegistration->id])}}" method="POST" id="deleteCompanyForm">
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                    </form>
                    @endif
                    @if($canProcess)
                        {{ Form::open(array('route' => array('vendorManagement.approval.registrationAndPreQualification.submit', $vendorRegistration->id))) }}
                            @if($canApprove)
                            <span class="smart-form">
                                @include('verifiers.select_verifiers')
                            </span>
                            @endif
                            <footer>
                                <div class="pull-left">
                                @if($canBeDeleted)
                                <button type="submit" form="deleteCompanyForm" class="btn btn-danger" data-toggle="modal" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}"><i class="fas fa-trash-alt"></i> {{ trans('general.delete') }}</button>
                                @endif
                                </div>
                                <div class="pull-right">
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#submissionLogsModal"><i class="fas fa-list"></i> {{ trans('vendorManagement.submissionLogs') }}</button>
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#verifierStatusOverviewModal"><i class="fa fa-users"></i> {{ trans('verifiers.verifiers') }}</button>
                                    <button type="submit" class="btn btn-danger" name="submit" value="reject" data-intercept="confirmation" data-confirmation-with-remarks="remarks" data-confirmation-with-remarks-required="true" data-confirmation-message="{{ trans('forms.rejectionReason') }}">{{ trans('forms.reject') }}</button>
                                    @if($canApprove)
                                        <button type="submit" class="btn btn-success" name="submit" value="approve" data-intercept="confirmation" data-confirmation-with-remarks="remarks" data-confirmation-message="{{ trans('general.remarks') }}">{{ trans('forms.approve') }}</button>
                                    @endif
                                </div>
                            </footer>
                        {{ Form::close() }}
                    @elseif($isVerifier)
                        {{ Form::open(array('route' => array('vendorManagement.approval.registrationAndPreQualification.approve', $vendorRegistration->id))) }}
                            <footer class="pull-right">
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#submissionLogsModal"><i class="fas fa-list"></i> {{ trans('vendorManagement.submissionLogs') }}</button>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#verifierStatusOverviewModal"><i class="fa fa-users"></i> {{ trans('verifiers.verifiers') }}</button>
                                <button type="submit" class="btn btn-danger" name="submit" value="reject" data-intercept="confirmation" data-confirmation-with-remarks="remarks" data-confirmation-with-remarks-required="true" data-confirmation-message="{{ trans('forms.rejectionReason') }}">{{ trans('forms.reject') }}</button>
                                <button type="submit" class="btn btn-success" name="submit" value="approve">{{ trans('forms.approve') }}</button>
                            </footer>
                        {{ Form::close() }}
                    @else
                        <footer>
                            @if($canBeDeleted)
                            <button type="submit" form="deleteCompanyForm" class="btn btn-danger pull-left" data-toggle="modal" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}"><i class="fas fa-trash-alt"></i> {{ trans('general.delete') }}</button>
                            @endif
                            <div class="pull-right">
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#submissionLogsModal"><i class="fas fa-list"></i> {{ trans('vendorManagement.submissionLogs') }}</button>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#verifierStatusOverviewModal"><i class="fa fa-users"></i> {{ trans('verifiers.verifiers') }}</button>
                            </div>
                        </footer>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('verifiers.verifier_status_overview_modal', array(
    'verifierRecords' => $assignedVerifierRecords
))
@include('templates.generic_table_modal', [
    'modalId'    => 'submissionLogsModal',
    'title'      => trans('vendorManagement.submissionLogs'),
    'tableId'    => 'submissionLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var descriptionFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var descriptionLabel = document.createElement('label');
                descriptionLabel.innerText = data.description;

                var container = document.createElement('div');
                container.appendChild(descriptionLabel);

                if(data.hasOwnProperty('hasErrors') && data.hasErrors) {
                    container.style.backgroundColor = '#FDD6D6';
                }
                else if(data.hasOwnProperty('hasChanges') && data.hasChanges) {
                    container.style.backgroundColor = '#ffc241';
                }

				return container;
			}

            var mainTable = new Tabulator('#main-table', {
                height:350,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter:descriptionFormatter },
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:view'},
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("general.view") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'}
                                }
                            }
                        ]
                    }}
                ],
            });

            var submissionLogsTable = null;

            $(document).on('show.bs.modal', '#submissionLogsModal', function(e) {
                submissionLogsTable = new Tabulator('#submissionLogsTable', {
                    height:350,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('vendorManagement.approval.registrationAndPreQualification.submissionLogs.get', [$vendorRegistration->id]) }}",
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    pagination: "local",
                    paginationSize:10,
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.user') }}", field:"user", hozAlign:"left", headerSort:false},
                        {title:"{{ trans('general.actions') }}", field:"action", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.dateAndTime') }}", field:"dateTime", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    ],
                });
            });
        });
    </script>
@endsection