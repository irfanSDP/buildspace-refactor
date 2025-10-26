@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}</li>
        <li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluationCycles') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluationCycles') }}}
        </h1>
    </div>
    @if($canAddCycle)
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('digital-star.cycle.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluationCycles') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('digital-star.cycle.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('digitalStar/vendorManagement.startDate') }}", field:"start_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('digitalStar/vendorManagement.endDate') }}", field:"end_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('digitalStar/vendorManagement.completed') }}", field:"completed", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter: "tick"},
                    {title:"{{ trans('digitalStar/vendorManagement.vpeCycleName') }}", field:"remarks", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("digitalStar/digitalStar.setup") }}'},
                                rowAttributes: {'href': 'route:setup'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                show: function(cell){
                                    return !cell.getData()['completed'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection