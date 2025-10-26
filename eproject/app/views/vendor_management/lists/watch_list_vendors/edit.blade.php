@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.watchList.index', trans('vendorManagement.watchList')) }}</li>
        <li>{{{$vendor->vendorWorkCategory->name}}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-user-lock"></i> {{{ trans('vendorManagement.pushToNomineesForWatchList') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
            <?php
                switch($vendor->company->getStatus())
                {
                    case \PCK\Companies\Company::STATUS_DEACTIVATED:
                        $badgeColor = 'bg-color-red';
                        break;
                    case \PCK\Companies\Company::STATUS_EXPIRED:
                        $badgeColor = 'bg-color-yellow';
                        break;
                    default:
                        $badgeColor = 'bg-color-green';
                }
                ?>
                <h2>{{ $vendor->company->name }} <span class="label {{$badgeColor}}">{{{ $vendor->company->getStatusText() }}}</span></h2> 
            </header>
            <div>
                <div class="widget-body">

                    <div class="well">
                        <div class="row">
                            <div class="col col-xs-6 col-md-3 col-lg-3">
                                <dl>
                                    <dt>{{ trans('vendorManagement.entryDate') }}:</dt>
                                    <dd>{{{ $entryDate->format(\Config::get('dates.submitted_at')) }}}</dd>
                                </dl>
                            </div>
                            <div class="col col-xs-6 col-md-3 col-lg-3">
                                <dl>
                                    <dt>{{ trans('vendorManagement.releaseDate') }}:</dt>
                                    <dd>{{{ $releaseDate->format(\Config::get('dates.submitted_at')) }}}</dd>
                                </dl>
                            </div>
                            <div class="col col-xs-6 col-md-3 col-lg-3">
                                <dl>
                                    <dt>{{ trans('vendorManagement.daysInWatchList') }}:</dt>
                                    <dd>{{{ $entryDate->diffInDays(\Carbon\Carbon::now()) }}}</dd>
                                </dl>
                            </div>
                            <div class="col col-xs-6 col-md-3 col-lg-3">
                                <dl>
                                    <dt>{{ trans('vendorManagement.daysToRelease') }}:</dt>
                                    <dd>
                                    @if($releaseDate->isPast())
                                        0
                                    @else
                                        {{ $releaseDate->diffInDays(\Carbon\Carbon::now()) }}
                                    @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col col-xs-12 col-md-12 col-lg-12">
                                <dl>
                                    <dt>{{ trans('vendorManagement.vendorWorkCategories') }}:</dt>
                                    <dd>{{{ $vendor->vendorWorkCategory->name }}}</dd>
                                </dl>
                            </div>
                        </div>

                        {{ Form::model($vendor, ['route' => ['vendorManagement.watchList.update', $vendor->id], 'class' => 'smart-form']) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <div class="pull-right">
                                    {{ Form::button('<i class="fa fa-user-shield"></i> '.trans('vendorManagement.pushToNomineesForWatchList'), ['type' => 'submit', 'class' => 'btn btn-success'] )  }}
                                    {{ link_to_route('vendorManagement.watchList.index', trans('forms.back'), [], array('class' => 'btn btn-default')) }}
                                    </div>
                                </section>
                            </div>
                        {{ Form::close() }}
                    </div>

                    <hr class="simple"/>

                    <ul id="myTab1" class="nav nav-tabs bordered">
                        <li class="active">
                            <a href="#company-details" data-toggle="tab">{{ trans('vendorProfile.companyDetails') }}</a>
                        </li>
                        <li>
                            <a href="#vendor-performance-evaluation" data-toggle="tab">{{ trans('vendorProfile.vendorPerformanceEvaluation') }}</a>
                        </li>
                        <li>
                            <a href="#remarks" data-toggle="tab">{{ trans('vendorProfile.remarks') }}</a>
                        </li>
                    </ul>
                    <div id="myTabContent1" class="tab-content padding-10">
                        <div class="tab-pane fade in active" id="company-details">
                            <div>
                                <fieldset>
                                    @include('vendor_profile.partials.company_details', ['company'=>$vendor->company])
                                </fieldset>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="vendor-performance-evaluation">
                            <p>
                                @include('vendor_profile.partials.vendor_performance_evaluations', ['company'=>$vendor->company])
                            </p>
                        </div>
                        <div class="tab-pane fade" id="remarks">
                            <div class="smart-form">
                                <div class="row">
                                    <section class="col col-xs-11">
                                        <label class="label">{{ trans('general.remarks') }}</label>
                                        <label class="textarea">
                                            <textarea rows="5" name="clientRemarks" id="clientRemarks"></textarea>
                                        </label>
                                    </section>
                                    <section class="col col-xs-1">
                                        <div class="pull-right">
                                            <label class="label">&nbsp;</label>
                                            <button class="btn btn-info" id="btnSaveClientRemarks"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                                        </div>
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12">
                                        <div id="vendor-profile-remarks-table"></div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@include('module_parameters.email_notification_settings.partials.modifiable_contents_modal', [
    'title'      => trans('vendorProfile.editRemarks'),
    'modalId'    => 'editVendorProfileRemarksModal',
    'textareaId' => 'vendorProfileRemarksTextarea'
])

@include('templates/yesNoModal', [
    'modalId' => 'deleteVendorProfileRemarkYesNoModal',
    'titleId' => 'deleteVendorProfileRemarkYesNoModalTitle',
    'message' => trans('vendorProfile.areYouSureDeleteRemarks'),
])

@include('templates.generic_table_modal', [
    'modalId'          => 'historical-evaluation-scores-modal',
    'title'            => '',
    'tableId'          => 'historical-evaluation-scores-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'cycle-evaluations-modal',
    'title'            => '',
    'tableId'          => 'cycle-evaluations-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-forms-modal',
    'title'            => '',
    'tableId'          => 'evaluation-forms-table',
    'modalDialogClass' => 'modal-xl',
])

<div class="modal fade" id="evaluation-form-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.form') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="well">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.reference') }}:</dt>
                                <dd data-name="project-reference"></dd>
                            </dl>
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.project') }}:</dt>
                                <dd data-name="project"></dd>
                            </dl>
                        </div>
                        <div class="col col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.companyName') }}:</dt>
                                <dd data-name="company"></dd>
                                <dt>{{ trans('vendorManagement.vendorWorkCategory') }}:</dt>
                                <dd data-name="vendor_work_category"></dd>
                                <dt>{{ trans('vendorManagement.evaluator') }}:</dt>
                                <dd data-name="evaluator"></dd>
                                <dt>{{ trans('vendorManagement.rating') }}:</dt>
                                <dd data-name="rating"></dd>
                            </dl>
                        </div>
                        <div class="col col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('vendorManagement.form') }}:</dt>
                                <dd data-name="form_name"></dd>
                                <dt>{{ trans('vendorManagement.status') }}:</dt>
                                <dd data-name="status"></dd>
                                <dt>&nbsp;</dt>
                                <dd></dd>
                                <dt>{{ trans('vendorManagement.score') }}:</dt>
                                <dd data-name="score"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div id="evaluation-form-table"></div>
                <div class="row">
                    <div class="col col-lg-12">
                    <dl>
                        <dt>{{ trans('general.remarks') }}:</dt>
                        <dd data-name="remarks"></dd>
                    </dl>
                    <dl>
                        <dt>{{ trans('general.attachments') }}:</dt>
                        <dd><button class="btn btn-xs btn-primary" data-action='show-attachments' data-route=''><i class="fa fa-paperclip"></i></button></dd>
                    </dl>
                    <dl>
                        <dt>{{ trans('general.logs') }}:</dt>
                        <dd>
                            <button class="btn btn-xs btn-primary" data-action='show-evaluator-log'>{{ trans('vendorPerformanceEvaluation.evaluationLogs') }}</button>
                            <button class="btn btn-xs btn-primary" data-action='show-verifier-log'>{{ trans('verifiers.verifierLog') }}</button>
                            <button class="btn btn-xs btn-primary" data-action='show-edit-log'>{{ trans('general.editLogs') }}</button>
                        </dd>
                    </dl>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'evaluation-form-attachments-modal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'evaluation-form-attachments-table',
])

@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-evaluator-log-modal',
    'title'            => trans('vendorPerformanceEvaluation.evaluationLogs'),
    'tableId'          => 'evaluation-form-evaluator-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-verifier-log-modal',
    'title'            => trans('verifiers.verifierLog'),
    'tableId'          => 'evaluation-form-verifier-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-edit-log-modal',
    'title'            => trans('general.editLogs'),
    'tableId'          => 'evaluation-form-edit-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-edit-details-log-modal',
    'title'            => trans('vendorPerformanceEvaluation.editDetails'),
    'tableId'          => 'evaluation-form-edit-details-log-table',
])
@endsection

@section('js')
<script src="{{ asset('js/app/modalStack.js') }}"></script>
<script>
    $(document).ready(function () {
        $("select#company-vendor-categories-details").select2();
        $("select#company-cidb-codes").select2();

        var vendorProfileRemarksTable = new Tabulator('#vendor-profile-remarks-table', {
            height:380,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('vendorManagement.watchList.vendorProfile.remarks.ajax.list', [$vendor->company->vendorProfile->id]) }}",
            ajaxConfig: "GET",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.remarks') }}", field:"content", minWidth: 300, hozAlign:"left", headerSort:false,formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        return '<div class="well">'
                        +'<p style="white-space: pre-wrap;">'+rowData['content']+'</p>'
                        +'<br />'
                        +'<p style="color:#4d8af0">'+rowData['created_by']+' &nbsp;&nbsp;&nbsp;&nbsp; '+rowData['created_at']+'</p>'
                        +'</div>';
                    }
                }},
                {title:"{{ trans('general.actions') }}", field:"content", width: 80, hozAlign:"center", cssClass: 'text-center', headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                    var data = cell.getRow().getData();

                    var updateVendorProfileRemarksButton = document.createElement('button');
                    updateVendorProfileRemarksButton.innerHTML = '<i class="fas fa-edit"></i></button>';
                    updateVendorProfileRemarksButton.className = 'btn btn-xs btn-warning';
                    updateVendorProfileRemarksButton.style['margin-right'] = '5px';

                    var deleteVendorProfileRemarksButton = document.createElement('button');
                    deleteVendorProfileRemarksButton.innerHTML = '<i class="fas fa-trash"></i></button>';
                    deleteVendorProfileRemarksButton.className = 'btn btn-xs btn-danger';

                    var container = document.createElement('div');
                    container.appendChild(updateVendorProfileRemarksButton);
                    container.appendChild(deleteVendorProfileRemarksButton);

                    updateVendorProfileRemarksButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#editVendorProfileRemarksModal [data-action="saveContent"]').data('url', data['route:update']);

                        $('#vendorProfileRemarksTextarea').val(data.content);

                        $('#editVendorProfileRemarksModal').modal('show');
                    });

                    deleteVendorProfileRemarksButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#deleteVendorProfileRemarkYesNoModal [data-action="actionYes"]').data('url', data['route:delete']);
                        $('#deleteVendorProfileRemarkYesNoModal').modal('show');
                    });

                    return container;
                }},
            ],
        });

        $(document).on('click', '#btnSaveClientRemarks',function(e) {
            e.preventDefault();

            var remarks = DOMPurify.sanitize($('#clientRemarks').val()).trim();

            if(remarks.length){
                app_progressBar.toggle();

                $.post("{{ route('vendorProfile.remarks.save', [$vendor->company->vendorProfile->id]) }}", {
                    _token: _csrf_token,
                    remarks: remarks,
                })
                .done(function(data) {
                    if(data.success) {
                        app_progressBar.maxOut();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksUpdated') }}");
                    } else {
                        SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                    }

                    if(vendorProfileRemarksTable){
                        vendorProfileRemarksTable.setData();//reload
                    }

                    $('#clientRemarks').val("");
                    app_progressBar.toggle();
                })
                .fail(function(data) {
                    app_progressBar.toggle();
                    SmallErrorBox.refreshAndRetry();
                });
            }
        });

        // remove and reconfigure textarea styles
        // bootstrap adds it's own stylings for unknown reasons
        $('#editVendorProfileRemarksModal').on('show.bs.modal', function() {
            $('#vendorProfileRemarksTextarea').removeAttr('style');
            $('#vendorProfileRemarksTextarea').css('height', '200px');
            $('#vendorProfileRemarksTextarea').css('overflow-y', 'scroll');
        });

        $('#editVendorProfileRemarksModal').on('shown.bs.modal', function() {
            $('#vendorProfileRemarksTextarea').focus();
        });

        $('#editVendorProfileRemarksModal [data-action="saveContent"]').on('click', function(e) {
            e.preventDefault();

            var url 	= $(this).data('url');
            var remarks = DOMPurify.sanitize($('#vendorProfileRemarksTextarea').val().trim());

            if(remarks == '') return;

            app_progressBar.toggle();

            $.post(url, {
                _token: _csrf_token,
                remarks: remarks,
            })
            .done(function(data) {
                if(data.success) {
                    app_progressBar.maxOut();
                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksUpdated') }}");
                } else {
                    SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                }

                if(vendorProfileRemarksTable){
                    vendorProfileRemarksTable.setData();//reload
                }

                $('#vendorProfileRemarksTextarea').val("");

                $('#editVendorProfileRemarksModal').modal('hide');

                app_progressBar.toggle();
            })
            .fail(function(data) {
                app_progressBar.toggle();
                SmallErrorBox.refreshAndRetry();
            });
        });

        $('#deleteVendorProfileRemarkYesNoModal [data-action="actionYes"]').on('click', function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            app_progressBar.toggle();

            $.post(url, {
                _token: _csrf_token,
            })
            .done(function(data) {
                if(data.success) {
                    app_progressBar.maxOut();
                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksDeleted') }}");
                } else {
                    SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                }

                if(vendorProfileRemarksTable){
                    vendorProfileRemarksTable.setData();//reload
                }

                $('#deleteVendorProfileRemarkYesNoModal').modal('hide');

                app_progressBar.toggle();
            })
            .fail(function(data) {
                app_progressBar.toggle();
                SmallErrorBox.refreshAndRetry();
            });
        });

        var latestEvaluationScoresTable = new Tabulator('#latest-evaluation-scores-table', {
            height:450,
            ajaxURL: "{{ route('vendorProfile.vendorPerformanceEvaluation.latest', array($vendor->company->id)) }}",
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.vpeCycleName') }}", field:"cycle", width: 250, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                    var vendorCategoriesArray = cell.getData()['vendor_categories'];
                    var output = [];
                    for(var i in vendorCategoriesArray){
                        output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                    }
                    return output.join('&nbsp;', output);
                }},
                {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.original') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                ]},
                {title:"{{ trans('vendorManagement.deliberated') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                    {title:"{{ trans('vendorManagement.score') }}", field:"deliberated_score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"deliberated_grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                ]},
                {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            tag: 'button',
                            attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('vendorManagement.historical') }}", 'data-action': 'show-historical'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: function(rowData){
                                return "{{ trans('vendorManagement.historical') }}";
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            tag: 'button',
                            attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('projects.projects') }}", 'data-action': 'show-evaluations'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-list'},
                            }
                        }
                    ]
                }}
            ]
        });

        var historicalEvaluationScoresTable = new Tabulator('#historical-evaluation-scores-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.vpeCycleName') }}", field:"cycle", minWidth: 250, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.original') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                ]},
                {title:"{{ trans('vendorManagement.deliberated') }}", width:300, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                    {title:"{{ trans('vendorManagement.score') }}", field:"deliberated_score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"deliberated_grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                ]},
                {title:"{{ trans('general.actions') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            tag: 'button',
                            attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('projects.projects') }}", 'data-action': 'show-evaluations'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-list'},
                            }
                        }
                    ]
                }}
            ]
        });

        var modalStack = new ModalStack();

        $('#latest-evaluation-scores-table').on('click', '[data-action=show-historical]', function(){
            var row = latestEvaluationScoresTable.getRow($(this).data('id'));
            historicalEvaluationScoresTable.setData(row.getData()['route:historical']);
            $('#historical-evaluation-scores-modal .modal-title').html(row.getData()['vendor_work_category']);
            modalStack.push('#historical-evaluation-scores-modal');
        });

        var cycleEvaluationsTable = new Tabulator('#cycle-evaluations-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.actions') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            tag: 'button',
                            attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('vendorManagement.forms') }}", 'data-action': 'show-forms'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-list'},
                            }
                        }
                    ]
                }}
            ]
        });

        $('#latest-evaluation-scores-table').on('click', '[data-action=show-evaluations]', function(){
            var row = latestEvaluationScoresTable.getRow($(this).data('id'));
            cycleEvaluationsTable.setData(row.getData()['route:evaluations']);
            $('#cycle-evaluations-modal .modal-title').html(row.getData()['cycle']);
            modalStack.push('#cycle-evaluations-modal');
        });

        $('#historical-evaluation-scores-table').on('click', '[data-action=show-evaluations]', function(){
            var row = historicalEvaluationScoresTable.getRow($(this).data('id'));
            cycleEvaluationsTable.setData(row.getData()['route:evaluations']);
            $('#cycle-evaluations-modal .modal-title').html(row.getData()['cycle']);
            modalStack.push('#cycle-evaluations-modal');
        });

        var evaluationFormsTable = new Tabulator('#evaluation-forms-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.evaluator') }}", field:"evaluator", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            tag: 'button',
                            attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('general.view') }}", 'data-action': 'show-form'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: function(rowData){
                                return "{{ trans('general.view') }}";
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            tag: 'a',
                            attributes: {target: '_blank', class:'btn btn-xs btn-warning', title: "{{ trans('general.download') }}", 'data-action': 'download'},
                            rowAttributes: {href: 'route:download'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-download'},
                            }
                        }
                    ]
                }}
            ]
        });

        $('#cycle-evaluations-table').on('click', '[data-action=show-forms]', function(){
            var row = cycleEvaluationsTable.getRow($(this).data('id'));
            evaluationFormsTable.setData(row.getData()['route:forms']);
            $('#evaluation-forms-modal .modal-title').html(row.getData()['title']);
            modalStack.push('#evaluation-forms-modal');
        });

        var evaluationFormEvaluatorLogTable = new Tabulator('#evaluation-form-evaluator-log-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"evaluator", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                { title:"{{ trans('general.actions') }}", field: 'action', width: 200, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('general.date') }}", field: 'created_at', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
            ]
        });

        $('#evaluation-form-modal').on('click', '[data-action=show-evaluator-log]', function(){
            modalStack.push('#evaluation-form-evaluator-log-modal');
        });

        var evaluationFormVerifierLogTable = new Tabulator('#evaluation-form-verifier-log-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('users.name') }}", field:"name", minWidth:250, hozAlign:"left", headerSort:false, headerFilter:true},
                { title:"{{ trans('verifiers.status') }}", field: 'approved', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:function(rowData){
                        if(rowData['approved'] === true){
                            return "<span class='text-success'><i class='fa fa-thumbs-up'></i> <strong>{{ trans('verifiers.approved') }}</strong></span>";
                        }
                        else if(rowData['approved'] === false){
                            return "<span class='text-danger'><i class='fa fa-thumbs-down'></i> <strong>{{ trans('verifiers.rejected') }}</strong></span>";
                        }
                        else{
                            return "<span class='text-warning'><i class='fa fa-question'></i> <strong>{{ trans('verifiers.unverified') }}</strong></span>";

                        }
                    }
                }},
                { title:"{{ trans('verifiers.verifiedAt') }}", field: 'verified_at', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('verifiers.remarks') }}", field: 'remarks', width: 240, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
            ]
        });

        $('#evaluation-form-modal').on('click', '[data-action=show-verifier-log]', function(){
            modalStack.push('#evaluation-form-verifier-log-modal');
        });

        var evaluationFormEditLogTable = new Tabulator('#evaluation-form-edit-log-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                { title:"{{ trans('vendorPerformanceEvaluation.editor') }}", field: 'name', headerSort:false, headerFilter:"input" },
                { title:"{{ trans('general.date') }}", field: 'created_at', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            tag: 'button',
                            attributes: {type: 'button', class:'btn btn-xs btn-success', title: "{{ trans('general.view') }}", 'data-action': 'show-edit-details-log'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: function(rowData){
                                return "{{ trans('general.view') }}";
                            }
                        }
                    ]
                }}
            ]
        });

        $('#evaluation-form-modal').on('click', '[data-action=show-edit-log]', function(){
            modalStack.push('#evaluation-form-edit-log-modal');
        });

        var evaluationFormEditDetailsLogTable = new Tabulator('#evaluation-form-edit-details-log-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.name') }}", field:"node_name", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                {
                    title: "{{ trans('general.previous') }}",
                    cssClass:"text-center text-middle",
                    columns:[
                        {title:"{{ trans('general.name') }}", field:"previous_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"previous_score_value", width: 80, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"previous_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false},
                    ]
                },{
                    title: "{{ trans('general.current') }}",
                    cssClass:"text-center text-middle",
                    columns:[
                        {title:"{{ trans('general.name') }}", field:"current_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"current_score_value", width: 80, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"current_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false},
                    ]
                }
            ]
        });

        $('#evaluation-form-edit-log-table').on('click', '[data-action=show-edit-details-log]', function(){
            var row = evaluationFormEditLogTable.getRow($(this).data('id'));
            evaluationFormEditDetailsLogTable.setData(row.getData()['route:details']);
            modalStack.push('#evaluation-form-edit-details-log-modal');
        });

        var evaluationFormTable = new Tabulator('#evaluation-form-table', {
            dataTree: true,
            dataTreeStartExpanded:true,
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                    var cellData = cell.getData();

                    var description = cellData['description'];

                    if(cell.getData()['type'] == 'node'){
                        description = '<strong>'+description+'</strong>';
                    }
                    else if(cell.getData()['type'] == 'score' && cell.getData()['selected']){
                        description = '<strong>'+description+'</strong>';
                    }

                    return description;
                }},
                {title:"{{ trans('forms.notApplicable') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        if(rowData.hasOwnProperty('id')){
                            if(rowData['type'] == 'node' && rowData['depth'] > 0 && rowData['hasScores'])
                            {
                                var checked = rowData['is_excluded'] ? 'checked' : '';
                                return '<input type="checkbox" '+checked+' disabled>';
                            }
                        }
                    }
                }},
                {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' }
            ],
        });

        var evaluationFormAttachmentsTable = new Tabulator('#evaluation-form-attachments-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", cssClass:"text-center", width: 15, headerSort:false, formatter:"rownum"},
                {title:"{{ trans('general.name') }}", cssClass:"text-left", minWidth: 400, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                    formatterParams: {
                        innerHtml: function(rowData){
                            return rowData.filename;
                        },
                        tag: 'a',
                        attributes: {'download': ''},
                        rowAttributes: {'href': 'download_url'}
                    }
                },
                {title:"{{ trans('files.uploadedBy') }}", field:'uploaded_by', minWidth: 150, cssClass:"text-center", headerSort:false},
                {title:"{{ trans('files.uploadedAt') }}", field:'uploaded_at', minWidth: 150, cssClass:"text-center", headerSort:false},
            ]
        });

        $('#evaluation-forms-table').on('click', '[data-action=show-form]', function(){
            var row = evaluationFormsTable.getRow($(this).data('id'));
            evaluationFormEvaluatorLogTable.setData(row.getData()['route:evaluator_log']);
            evaluationFormVerifierLogTable.setData(row.getData()['route:verifier_log']);
            evaluationFormEditLogTable.setData(row.getData()['route:edit_log']);
            $.get(row.getData()['route:form_info'], function(data){
                evaluationFormTable.setData(data['route:grid']);
                $('#evaluation-form-modal [data-name=project-reference]').html(data['project_reference']);
                $('#evaluation-form-modal [data-name=project]').html(data['project']);
                $('#evaluation-form-modal [data-name=company]').html(data['company']);
                $('#evaluation-form-modal [data-name=vendor_work_category]').html(data['vendor_work_category']);
                $('#evaluation-form-modal [data-name=form_name]').html(data['form_name']);
                $('#evaluation-form-modal [data-name=status]').html(data['status']);
                $('#evaluation-form-modal [data-name=evaluator]').html(data['evaluator']);
                $('#evaluation-form-modal [data-name=score]').html(data['score']);
                $('#evaluation-form-modal [data-name=rating]').html(data['rating']);
                $('#evaluation-form-modal [data-name=remarks]').html(data['remarks']);
                evaluationFormAttachmentsTable.setData(data['route:attachments']);
                modalStack.push('#evaluation-form-modal');
            });
        });

        $('#evaluation-form-modal').on('click', '[data-action=show-attachments]', function(){
            modalStack.push('#evaluation-form-attachments-modal');
        });
    });
</script>
@endsection