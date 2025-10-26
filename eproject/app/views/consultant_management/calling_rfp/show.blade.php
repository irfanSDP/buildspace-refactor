@extends('layout.main')

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
?>

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            @if($callingRfp->is_extend)
            <i class="fa fa-clock"></i> Extension of Calling RFP {{{trans('documentManagementFolders.revision')}}} {{$callingRfp->consultantManagementRfpRevision->revision}}
            @else
            <i class="fa fa-trophy"></i> {{{ trans('general.callingRFP') }}} {{{trans('documentManagementFolders.revision')}}} {{$callingRfp->consultantManagementRfpRevision->revision}}
            @endif
        </h1>
    </div>
</div>

@if(!$callingRfp->editableByUser($user) && $callingRfp->needApprovalFromUser($user))
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-user-tie"></i> {{{$callingRfp->getStatusText()}}}</h2>
            </header>   
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.calling.rfp.verify', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                                <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                                    {{ Form::textarea('remarks', Input::old('remarks'), ['rows' => 3]) }}
                                </label>
                                {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', $callingRfp->id) }}
                        {{ link_to_route('consultant.management.calling.rfp.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-times-circle"></i> '.trans('forms.reject'), ['type' => 'submit', 'name'=>'reject', 'class' => 'btn btn-danger'] )  }}
                        {{ Form::button('<i class="fa fa-check-circle"></i> '.trans('forms.approve'), ['type' => 'submit', 'name'=>'approve', 'class' => 'btn btn-success'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-contract-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#consultant-management-contract-tab-main-info" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> {{{ trans('projects.mainInformation') }}}</a>
                        </li>
                        <li>
                            <a href="#consultant-management-contract-tab-phases" data-toggle="tab"><i class="fa fa-fw fa-lg fa-file-contract"></i> {{{ trans('general.phases') }}}</a>
                        </li>
                    </ul>
                    <div id="consultant-management-contract-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-contract-tab-main-info">
                            @include('consultant_management.contracts.partials.main_info')
                        </div>
                        <div class="tab-pane fade in " id="consultant-management-contract-tab-phases">
                            @if($consultantManagementContract->consultantManagementSubsidiaries->count())
                            <ul id="consultant-management-subsidiaries-tabs" class="nav nav-pills">
                                @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                                <li class="nav-item @if($key == 0) active @endif">
                                    <a href="#consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}" title="{{{ $consultantManagementSubsidiary->subsidiary->name }}}" data-toggle="tab">{{{ $consultantManagementSubsidiary->subsidiary->short_name}}}</a>
                                </li>
                                @endforeach
                            </ul>
                            <div id="consultant-management-subsidiaries-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                                <div class="tab-pane fade in @if($key==0) active @endif" id="consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}">
                                    @include('consultant_management.contracts.partials.phase_info')
                                </div>
                            @endforeach
                            </div>
                            @else
                            <div class="row">
                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fa-fw fa fa-info"></i>
                                        <strong>Info!</strong> There is no Phase for this Development Plan.
                                    </div>
                                </section>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-building"></i> {{{ $vendorCategoryRfp->vendorCategory->name }}} <span class="badge bg-color-green inbox-badge">{{{$callingRfp->getStatusText()}}}</span> </h2>
            </header>
            <div>
                <div class="widget-body">
                    @if ($callingRfp->editableByUser($user) )
                    @include('consultant_management.calling_rfp.partials.form')
                    @else
                    @include('consultant_management.calling_rfp.partials.info')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="duplicateDirectorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-user-tie"></i> Duplicate Director(s)</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body no-padding">
                <div id="duplicate_director-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>

@include('consultant_management.partials.vendor_profile.modal')

@include('consultant_management.partials.verifier_logs_modal')

@include('consultant_management.partials.general_attachment')

@endsection

@section('js')
<link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />

<script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
@include('consultant_management.partials.general_attachment_javascript')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    textarea:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<div>' +this.sanitizeHTML(obj.remarks)+ '</div>';
        return this.emptyToSpace(str);
    },
    duplicateDirector:function(cell, formatterParams){
        var obj = cell.getRow().getData();
        var str = '&nbsp;';
        if(obj.duplicate_directors.length > 0){
            str = '<button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#duplicateDirectorModal"><i class="fa fa-user-tie"></i> {{{trans("forms.view")}}}</button>';
        }
        return this.emptyToSpace(str);
    },
    isBumiputera:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.is_bumiputera) ? '<i class="fas fa-lg fa-fw fa-check-circle text-success"></i>' : '<i class="fas fa-lg fa-fw fa-times-circle text-danger"></i>';
        return this.emptyToSpace(str);
    }
});
var consultantStatusFormatter = function(cell, formatterParams, onRendered){
    var data = cell.getRow().getData();
    var cellValue = null;

    switch(data.status.toString()) {
        case "{{ PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_YES }}": 
            cellValue = "{{ PCK\ConsultantManagement\ConsultantManagementListOfConsultantCompany::STATUS_YES_TEXT }}";
            break;
        case "{{ PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_NO }}": 
            cellValue = "{{ PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_NO_TEXT }}";
            break;
        default:
            cellValue = "{{ PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_PENDING_TEXT }}";
    }

    return '<i class="fa fa-sm fa-edit"></i> '+cellValue;
};

$(document).ready(function () {

    $('.datetimepicker').datetimepicker({
        format: 'DD-MMM-YYYY hh:mm A',
        stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
        showTodayButton: true,
        allowInputToggle: true
    });

    var selectedConsultantTable = new Tabulator('#selected_consultants-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.calling.rfp.selected.consultants.ajax.list', [$vendorCategoryRfp->id, $callingRfp->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.consultant') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            @if ($callingRfp->editableByUser($user) )
            {title:"{{ trans('general.status') }}", field:"status_txt", width:128, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:consultantStatusFormatter, editor:"select", editorParams:{
                values:{
                    "{{PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_PENDING}}":"{{PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_PENDING_TEXT}}",
                    "{{PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_YES}}":"{{PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_YES_TEXT}}",
                    "{{PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_NO}}":"{{PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_NO_TEXT}}",
                }}
            },
            @else
            {title:"{{ trans('general.status') }}", field:"status_txt", width:128, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            @endif
            @if($callingRfp->status == PCK\ConsultantManagement\ConsultantManagementCallingRfp::STATUS_APPROVED)
            {title:"{{ trans('general.questionnaires') }}", field:"id", width:128, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(rowData.status == {{ PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany::STATUS_YES }}){
                            return '<a href="'+rowData['route:questionnaire']+'" class="btn btn-xs btn-info"><i class="fa fa-tasks"></i></a>';
                        }
                        return '';
                    }
                }]
            }},
            @endif
            {title:"Duplicate Director(s)", field:"duplicate_directors", width:140, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:'duplicateDirector'},
            {title:"{{ trans('vendorProfile.vendorProfile') }}", field:"vendor_profile", width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#vendorProfileModal"><i class="fa fa-search"></i> {{{trans("forms.view")}}}</button>';
                    }
                }]
            }}
        ],
        cellClick:function(e, cell){
            var row = cell.getRow();
            var item = row.getData();
            var field = cell.getField();

            if(field=='duplicate_directors' && item.duplicate_directors.length > 0){
                var table = Tabulator.prototype.findTable("#duplicate_director-table")[0];
                if(table){
                    table.setData(item.duplicate_directors);
                }
            }else if(field == 'vendor_profile'){
                $('#vp-vendor_categories').val(null).trigger('change');
                $('#contractor-section').hide();
                $('#consultant-section').hide();
                $('#vp-vendor_performance_evaluation-rows').empty();

                var url = "{{ route('consultant.management.vendor.profile.info', ':id')}}";
                url = url.replace(':id', parseInt(item.id));

                app_progressBar.toggle();
                $.get(url, function(data){
                    $.each(data.details, function(key,val){
                        if(key=='vendor_categories'){
                            $.each(val, function(k,v){
                                $('#vp-vendor_categories').append('<option selected>'+v+'</option>').trigger('change');
                            });
                        }else if(key != 'is_contractor' || key != 'is_consultant'){
                            $('#vp-'+key).html(val);
                        }
                    });

                    if(data.details.is_contractor){
                        $('#contractor-section').show();
                    }

                    if(data.details.is_consultant){
                        $('#consultant-section').show();
                    }

                    $.each(data.vpe_rows, function(key,row){
                        $('#vp-vendor_performance_evaluation-rows').append(row);
                    });

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_DIRECTOR]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CPDTable = Tabulator.prototype.findTable("#company-personnel-directors-table")[0];
                    if(CPDTable){
                        CPDTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_SHAREHOLDERS]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CPSTable = Tabulator.prototype.findTable("#company-personnel-shareholders-table")[0];
                    if(CPSTable){
                        CPSTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_HEAD_OF_COMPANY]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CPHODTable = Tabulator.prototype.findTable("#company-personnel-head-of-company-table")[0];
                    if(CPHODTable){
                        CPHODTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.track.record.list', [':id', \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CompPTRTable = Tabulator.prototype.findTable("#completed-project-track-record-table")[0];
                    if(CompPTRTable){
                        CompPTRTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.track.record.list', [':id', \PCK\TrackRecordProject\TrackRecordProject::TYPE_CURRENT]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CurrPTRTable = Tabulator.prototype.findTable("#current-project-track-record-table")[0];
                    if(CurrPTRTable){
                        CurrPTRTable.setData(url);
                    }

                    var preQUrl = "{{ route('consultant.management.vendor.profile.preq.list', ':id') }}";
                    preQUrl = preQUrl.replace(':id', parseInt(item.id));
                    var preQTable = Tabulator.prototype.findTable("#vendor-prequalification-table")[0];
                    if(preQTable){
                        preQTable.setData(preQUrl, {}, "GET");
                    }

                    var vwcUrl = "{{ route('vendorProfile.vendor.list', [':id']) }}";
                    vwcUrl = vwcUrl.replace(':id', parseInt(item.id));
                    var vwcTable = Tabulator.prototype.findTable("#vendor_work_categories-table")[0];
                    if(vwcTable){
                        vwcTable.setData(vwcUrl, {}, "GET");
                    }

                    var apUrl = "{{ route('vendorProfile.awardedProjects', [':id']) }}";
                    apUrl = apUrl.replace(':id', parseInt(item.id));
                    var apTable = Tabulator.prototype.findTable("#awarded-projects-table")[0];
                    if(apTable){
                        $.get(apUrl, function(data){
                            apTable.setData(data.data, {}, "GET");
                        });
                    }

                    var cpUrl = "{{ route('vendorProfile.completedProjects', [':id']) }}";
                    cpUrl = cpUrl.replace(':id', parseInt(item.id));
                    var cpTable = Tabulator.prototype.findTable("#completed-projects-table")[0];
                    if(cpTable){
                        $.get(cpUrl, function(data){
                            cpTable.setData(data.data, {}, "GET");
                        });
                    }

                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                });
            }
        }
        @if ($callingRfp->editableByUser($user) )
        ,cellEdited:function(cell) {
            var row = cell.getRow();
            var item = row.getData();
            var field = cell.getField();
            var value = cell.getValue();
            var table = cell.getTable();

            table.modules.ajax.showLoader();
            
            var params = {
                cid: parseInt(item.id),
                field: field,
                val: value,
                _token:'{{{csrf_token()}}}',
            };

            $.post(item['route:update'], params)
            .done(function(data){
                if(data.updated){
                    cell.getRow().update(data.item);
                    cell.getRow().reformat();
                }
                table.modules.ajax.hideLoader();
            })
            .fail(function(data){
                console.error('failed');
                table.modules.ajax.hideLoader();
            });
        }
        @endif
    });

    var verifierLogTable = new Tabulator('#verifier_logs-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.calling.rfp.verifier.ajax.log', [$vendorCategoryRfp->id, $callingRfp->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('documentManagementFolders.revision') }}", field:"version", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status_txt", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width:380, hozAlign:'left', headerSort:false, formatter:'textarea'},
            {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    new Tabulator("#duplicate_director-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"id", width:40, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:"rownum"},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.company') }}", field:"company_name", hozAlign:"left", width:300, headerSort:false},
        ]
    });

    new Tabulator("#company-personnel-directors-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
        ]
    });

    new Tabulator("#company-personnel-shareholders-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.designation') }}", field:"designation", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.amountOfShare') }}", field:"amount_of_share", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.holdingPercentage') }}", field:"holding_percentage", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
        ]
    });

    new Tabulator("#company-personnel-head-of-company-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
        ]
    });

    new Tabulator('#completed-project-track-record-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", width: 480, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
            {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicOrConquasScore') }}", field:"has_qlassic_or_conquas_score", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicScore') }}", field:"qlassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicYearOfAchievement') }}", field:"qlassic_year_of_achievement", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.conquasScore') }}", field:"conquas_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.conquasYearOfAchievement') }}", field:"conquas_year_of_achievement", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.awardsReceived') }}", field:"awards_received", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfAwardsReceived') }}", field:"year_of_recognition_awards", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
        ]
    });

    new Tabulator('#current-project-track-record-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", minWidth: 480, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
            {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
        ],
    });

    new Tabulator("#vendor-prequalification-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('vendorManagement.form') }}", field:"form", minWidth: 200, hozAlign:'left', headerSort:false},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 280, hozAlign:'left', headerSort:false},
            {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 250, cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    new Tabulator('#vendor_work_categories-table', {
        height:360,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            { title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: function(rowData){
                    var c = '<div class="well">';
                    $.each(rowData.vendor_categories, function( key, value ) {
                        c+='<p>'+value+'</p>';
                    });
                    c+='</div>';
                    return c;
                }
            }},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: function(rowData){
                    var c = '<div class="well">';
                    c+='<p>'+rowData.vendor_work_category_name+'</p>';
                    c+='</div>';
                    return c;
                }
            }},
            {title:"{{ trans('vendorManagement.qualified') }}", field:"qualified", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ],
    });

    new Tabulator('#awarded-projects-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data: [],
        ajaxConfig: "GET",
        layout:"fitColumns",
        dataLoaded:function(data){
            if(data.length < 1) return;
        },
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('projects.status') }}", field:"status", width: 120, hozAlign:"center", headerSort:false},
            {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
            {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
        ],
    });

    new Tabulator('#completed-projects-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data: [],
        ajaxConfig: "GET",
        layout:"fitColumns",
        dataLoaded:function(data){
            if(data.length < 1) return;
        },
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
            {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
        ],
    });
});
</script>
@endsection
