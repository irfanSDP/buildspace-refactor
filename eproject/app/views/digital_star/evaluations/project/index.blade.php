@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}</li>
        <li>{{ trans('digitalStar/vendorManagement.evaluations') }}</li>
        <li>{{ trans('digitalStar/digitalStar.project') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            {{ trans('digitalStar/digitalStar.projectEvaluations') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{ trans('digitalStar/vendorManagement.evaluations') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="evaluations-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var evaluationsTable = new Tabulator('#evaluations-table', {
                height: 450,
                ajaxURL: "{{ route('digital-star.evaluation.project.list') }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 10,
                pagination: "remote",
                ajaxFiltering: true,
                layout: "fitColumns",
                columns:[
                    { title: "{{ trans('general.no') }}", formatter:"rownum", width: 60, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, frozen: true },
                    { title: "{{ trans('projects.reference') }}", field:"reference", width: 180, cssClass:"text-center text-middle", headerSort: false, headerFilter: 'input', frozen: true },
                    { title: "{{ trans('projects.title') }}", field:"title", hozAlign:'center', cssClass:"text-left", headerSort: false, headerFilter: 'input' },
                    {title:"{{ trans('digitalStar/vendorManagement.startDate') }}", field:"start_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:false},
                    {title:"{{ trans('digitalStar/vendorManagement.endDate') }}", field:"end_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:false},
                    {title:"{{ trans('general.status') }}", field:"status", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($statuses) }} },
                    {title:"{{ trans('general.actions') }}", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                opaque: function(cell){
                                    return cell.getData()['route:evaluation_form'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("digitalStar/digitalStar.evaluationForm") }}'},
                                rowAttributes: {'href': 'route:evaluation_form'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list-ol'},
                                    innerHtml: function(rowData) {
                                        return " {{ trans('forms.form') }}";
                                    }
                                }
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection