@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.calling.rfp.show', $vendorCategoryRfp->vendorCategory->name, [$vendorCategoryRfp->id, $callingRfp->id]) }}</li>
        <li>{{{ trans('general.consultantInterview') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-comments"></i> {{{ trans('general.consultantInterview') }}}
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
        @if($user->isSuperAdmin() or ($user->isGroupAdmin() && $user->isConsultantManagementCallingRfpEditor($consultantManagementContract)))
            <a href="{{ route('consultant.management.consultant.rfp.interview.create', [$vendorCategoryRfp->id, $callingRfp->id]) }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('general.new') }}} {{{trans('general.consultantInterview')}}}
            </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div id="rfp_interview-table"></div>
                    <div class="row pe-4">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div class="pull-right">
                                {{ link_to_route('consultant.management.calling.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $callingRfp->id], ['class' => 'btn btn-default']) }}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    title:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<a href="'+obj['route:show']+'" class="plain">'+this.sanitizeHTML(obj.title)+'</a>';
        if(obj.details.length){
            str += '<div class="well">'+obj.details+'</div>';
        }
        return this.emptyToSpace(str);
    }
});
$(document).ready(function (){
    new Tabulator('#rfp_interview-table', {
        height:420,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.rfp.interview.list', [$vendorCategoryRfp->id, $callingRfp->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:'title'},
            {title:"{{ trans('general.date') }}", field:"interview_date", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status_txt", width: 140, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection