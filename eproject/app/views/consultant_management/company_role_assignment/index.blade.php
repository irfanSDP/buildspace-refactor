@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        <li>{{{ trans("general.groupManagement") }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-university"></i> {{{ trans("general.groupManagement") }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                {{ Form::open(['route' => ['consultant.management.company.role.assignment.store', $consultantManagementContract->id], 'class' => 'smart-form']) }}
                    <table class="table table-hover" style="text-align: center;">
                        <thead>
                            <tr>
                                <th style="text-align: center;width:98px;">{{{ trans('general.callingRFP') }}}</th>
                                <th style="text-align: left;width:280px;">{{{ trans('general.role') }}}</th>
                                <th style="text-align: left;">{{{ trans('inspection.groups') }}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    {{ Form::radio('calling_rfp', PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT, Input::old('calling_rfp', ($consultantManagementContract->getCallingRfpCompanyRole()->role == PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT))) }}
                                </td>
                                <td style="text-align: left;">
                                    <div class="well">
                                        <strong>{{{ PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT_TEXT }}}</strong>
                                    </div>
                                </td>
                                <td style="text-align: left;">
                                    <div class="well">
                                    {{{ mb_strtoupper($recommendationOfConsultantCompany->name) }}}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                {{ Form::radio('calling_rfp', PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT, Input::old('calling_rfp', ($consultantManagementContract->getCallingRfpCompanyRole()->role == PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))) }}
                                </td>
                                <td style="text-align: left;">
                                    <div class="well">
                                        <strong>{{{ PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT_TEXT }}}</strong>
                                    </div>
                                </td>
                                <td style="text-align: left;vertical-align:middle;">
                                    <label class="fill-horizontal {{{ $errors->has('company_id') ? 'state-error' : null }}}">
                                        <select class="select2 fill-horizontal" id="list_of_consultant-company_id-select" name="company_id">
                                            <option value="">{{{ trans('forms.select') }}}</option>
                                            @foreach($listOfConsultantCompanies as $company)
                                            <option value="{{$company->id}}" @if($company->id == Input::old('company_id', ($listOfConsultantCompany) ? $listOfConsultantCompany->id : null)) selected @endif>{{{ $company->name }}}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    {{ $errors->first('company_id', '<em class="invalid">:message</em>') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <footer>
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        {{ Form::button('<i class="fa fa-search"></i> '.trans('general.logs'), ['type' => 'button', 'class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#companyRoleLogsModal'] )  }}
                    </footer>
                {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pendingTasksModal" tabindex="-1" role="dialog" aria-labelledby="pendingTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header alert-danger">
                <h4 class="modal-title">
                    Pending Tasks List By Modules
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body no-padding">
                <div style="padding:8px;">
                    <div class="alert alert-warning text-center">
                        <i class="fa-fw fa fa-exclamation-triangle"></i>
                            <strong>Attention!</strong> There are pending tasks for users from the current company. Please clear out all the
                            pending tasks before you can change the company role.
                        </div>
                </div>
                <div id="pending_tasks-content"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default btn-md" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="companyRoleLogsModal" tabindex="-1" role="dialog" aria-labelledby="companyRoleLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    Group Management Logs
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body no-padding">
                <div id="company_role_logs-table"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default btn-md" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {

    $('#pendingTasksModal').on('shown.bs.modal', function () {
        $("#pendingTasksTab a:first").tab("show");
        $("#pendingTasksTab a").click(function(evt){
            evt.preventDefault();
            $(this).tab("show");
        });
    });

    var logTable = new Tabulator('#company_role_logs-table', {
        height:380,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"Details", field:"id", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(data){
                        var callingRfp = (data.calling_rfp) ? '<b class="badge bg-color-yellow" style="font-size:10px;">{{ trans('general.callingRFP') }}</b>' : '';
                        var str = "<strong>"+data.role+":</strong> "+data.company+" "+callingRfp+"<br/>";
                        str += "{{ trans('general.updatedBy')}} "+data.updated_by;

                        return str;
                    }
                }]
            }},
            {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    $('#companyRoleLogsModal').on('shown.bs.modal', function () {
        logTable.setData("{{ route('consultant.management.company.role.assignment.logs', $consultantManagementContract->id) }}");
    });

    $("input[type='radio'][name='calling_rfp']").on('change', function(e){
        var $this = $(this);
        if($this.val() && parseInt($this.val()) != parseInt({{$consultantManagementContract->getCallingRfpCompanyRole()->role}})){
            setContent($this, "{{route('consultant.management.company.role.assignment.calling.rfp.validate', $consultantManagementContract->id)}}", parseInt({{$consultantManagementContract->getCallingRfpCompanyRole()->role}}), {
                role: parseInt($this.val())
            }, "radio");
        }
    });

    @if($listOfConsultantCompany)
    $("#list_of_consultant-company_id-select").on('change', function(e){
        var $this = $(this);
        if($this.val() && parseInt($this.val()) != parseInt({{$listOfConsultantCompany->id}})){
            setContent($this, "{{route('consultant.management.company.role.assignment.loc.validate', $consultantManagementContract->id)}}", parseInt({{$listOfConsultantCompany->id}}), {
                cid: parseInt($this.val())
            }, "select");
        }
    });
    @endif
});

function setContent(elem, url, originalVal, ajaxData, elemType){
    $.get(url, ajaxData)
    .done(function(resp){
        if(resp.has_pending){
            if(elemType=="radio"){
                $("input[type='radio'][name=calling_rfp][value='"+originalVal+"']").prop("checked",true);
            }else{
                elem.val(originalVal).trigger('change');
            }

            var content = '<ul class="nav nav-tabs" id="pendingTasksTab">';
            var tabContent = '';

            for (var mdl in resp.modules) {
                switch(mdl){
                    case 'loc':
                        tabTitle = "LOC";
                        break;
                    case 'calling_rfp':
                        tabTitle = "Calling RFP";
                        break;
                    case 'open_rfp':
                        tabTitle = "RFP Opening";
                        break;
                    case 'resubmission_rfp':
                        tabTitle = "RFP Resubmission";
                        break;
                    case 'approval_doc':
                        tabTitle = "Approval Document";
                        break;
                    default:
                        tabTitle = '';
                }
                if(resp.modules[mdl].length){
                    content += '<li class="nav-item">'+
                    '<a href="#pending-'+mdl+'" class="nav-link" data-bs-toggle="tab">'+tabTitle+'</a>'+
                    '</li>';

                    for (let i = 0; i < resp.modules[mdl].length; i++){
                        tabContent += '<div class="tab-pane fade" id="pending-'+mdl+'"><div id="'+mdl+'-table"></div></div>';
                    }
                }
            }
            content += '</ul><div class="tab-content">'+tabContent+'</div>';

            $("#pending_tasks-content").html(content);

            for (var mdl in resp.modules) {
                if(resp.modules[mdl].length){
                    new Tabulator('#'+mdl+'-table', {
                        height:260,
                        placeholder: "{{ trans('general.noRecordsFound') }}",
                        data: resp.modules[mdl],
                        layout:"fitColumns",
                        columns:[
                            {title:"{{ trans('general.no') }}", field:"counter", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                            {title:"RFP", field:"vendor_category_name", minWidth: 300, hozAlign:"left", headerSort:false},
                            {title:"{{ trans('general.createdAt') }}", field:"created_at", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
                        ]
                    });
                }
            }

            $("#pendingTasksModal").modal('show');
        }
    })
    .fail(function(data){
        console.error('failed');
    });
}
</script>
@endsection
