@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.calling.rfp.show', $vendorCategoryRfp->vendorCategory->name, [$vendorCategoryRfp->id, $callingRfp->id]) }}</li>
        <li>{{ link_to_route('consultant.management.consultant.rfp.interview.index', trans('general.consultantInterview'), [$vendorCategoryRfp->id, $callingRfp->id]) }}</li>
        <li>{{{ trans('general.view') }}}</li>
    </ol>
@endsection
<?php use Carbon\Carbon; ?>
@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-comments"></i> {{{ trans('general.consultantInterview') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-fw fa-comments"></i> {{{ PCK\Helpers\StringOperations::shorten($rfpInterview->title, 38) }}} @if($rfpInterview->status == PCK\ConsultantManagement\ConsultantManagementRfpInterview::STATUS_DRAFT) <span class="badge bg-color-yellow inbox-badge"> @else <span class="badge bg-color-green inbox-badge"> @endif {{{ $rfpInterview->getStatusText() }}}</span></h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.title') }}:</dt>
                                <dd>{{{ $rfpInterview->title }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.details') }}:</dt>
                                <dd><div class="well">{{ nl2br($rfpInterview->details) }}</div></dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <section class="col col-xs-6 col-md-6 col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.date') }}:</dt>
                                <dd>{{{ Carbon::parse($rfpInterview->interview_date)->format(\Config::get('dates.full_format')) }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-4 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.createdBy') }}:</dt>
                                <dd>{{{ $rfpInterview->createdBy->name }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </section>
                        <section class="col col-xs-4 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.updatedBy') }}:</dt>
                                <dd>{{{ $rfpInterview->updatedBy->name }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </section>
                    </div>

                    <div id="rfp_interview-consultants">
                        <hr class="simple">
                        <div class="row">
                            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <h5><i class="fa fa-users"></i> Consultant(s)</h5>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <div id="consultant_list-table"></div>
                            </section>
                        </div>
                    </div>

                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            @if($user->isSuperAdmin() or ($user->isGroupAdmin() && $user->isConsultantManagementCallingRfpEditor($consultantManagementContract)) && $rfpInterview->status == PCK\ConsultantManagement\ConsultantManagementRfpInterview::STATUS_DRAFT)
                            {{ Form::open(['route' => ['consultant.management.consultant.rfp.interview.send', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                            <footer>
                                {{ Form::hidden('id', $rfpInterview->id) }}
                                {{ Form::hidden('calling_rfp_id', $callingRfp->id) }}
                                {{ link_to_route('consultant.management.consultant.rfp.interview.index', trans('forms.back'), [$vendorCategoryRfp->id, $callingRfp->id], ['class' => 'btn btn-default']) }}
                                {{ HTML::decode(link_to_route('consultant.management.consultant.rfp.interview.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id], ['data-id'=>$rfpInterview->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                                {{ HTML::decode(link_to_route('consultant.management.consultant.rfp.interview.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id], ['class' => 'btn btn-primary'])) }}
                                {{ Form::button('<i class="fa fa-paper-plane"></i> '.trans('forms.send'), ['type' => 'submit', 'name'=>'dates_extension', 'class' => 'btn btn-success', 'data-intercept' => 'confirmation', 'data-intercept-condition' => 'noVerifier'] )  }}
                            </footer>
                            {{ Form::close() }}
                            @else
                            <div class="pull-right">
                                {{ link_to_route('consultant.management.consultant.rfp.interview.index', trans('forms.back'), [$vendorCategoryRfp->id, $callingRfp->id], ['class' => 'btn btn-default']) }}
                            </div>
                            @endif
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    consultantName:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = this.sanitizeHTML(obj.company_name);
        if(obj.consultant_interview_remarks.length){
            str += '<div class="well">'+this.sanitizeHTML(obj.consultant_interview_remarks)+'</div>';
        }
        return this.emptyToSpace(str);
    },
    consultantRemarks:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = this.sanitizeHTML(obj.consultant_remarks);
        return this.emptyToSpace(str);
    }
});
$(document).ready(function (){
    new Tabulator('#consultant_list-table', {
        height:320,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.rfp.interview.selected.consultant.list', [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id]) }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"id", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:'rownum'},
            {title:"Consultant(s)", field:"company_name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:'consultantName'},
            {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.time') }}", field:"interview_timestamp", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
            @if($rfpInterview->status == PCK\ConsultantManagement\ConsultantManagementRfpInterview::STATUS_SENT)
            ,{title:"{{ trans('general.status') }}", field:"consultant_interview_status_txt", width: 140, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
            ,{title:"Consultant Remarks", field:"consultant_remarks", width: 300, hozAlign:"left", headerSort:false, formatter:'consultantRemarks'}
            ,{title:"Resend Email", field:"consultant_interview_id", width: 110, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(parseInt(rowData.consultant_interview_status) == {{ PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant::STATUS_UNSET }}){
                            return '<a href="'+rowData['route:resend']+'" class="btn btn-xs btn-success"><i class="fa fa-paper-plane"></i></a>';
                        }else{
                            return '';
                        }
                    }
                }]
            }}
            @endif
        ]
    });
});
</script>
@endsection