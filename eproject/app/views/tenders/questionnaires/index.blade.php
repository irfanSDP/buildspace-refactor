@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{{ trans('general.questionnaires') }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tasks"></i> {{{ trans("general.questionnaires") }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div id="questionnaires-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {
    new Tabulator('#questionnaires-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('projects.questionnaires.contractors.ajax.list', [$project->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('tenders.contractor') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('general.questionnaires') }}", field:"id", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:questionnaire']+'" class="btn btn-xs btn-info"><i class="fa fa-tasks"></i></a>';
                    }
                }]
            }},
            {title:"{{ trans('general.status') }}", field:"questionnaire_status", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(parseInt(rowData.questionnaire_status) == {{ PCK\ContractorQuestionnaire\Questionnaire::STATUS_PUBLISHED }}){
                            return '<span class="label bg-color-green">{{{ trans("general.publish") }}}</span>';
                        }else{
                            return '<span class="label bg-color-red">{{{ trans("general.unpublish") }}}</span>';
                        }
                    }
                }]
            }},
            {title:"{{ trans('tenders.publishDateTime') }}", field:"published_date", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection