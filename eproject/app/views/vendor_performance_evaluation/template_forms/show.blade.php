@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.templateForms', trans('forms.templateForms'), array()) }}</li>
        <li>{{{ $templateForm->weightedNode->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-check"></i> {{{ $templateForm->weightedNode->name }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $templateForm->weightedNode->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    <footer class="pull-right">
                        <a href="{{ route('vendorPerformanceEvaluation.templateForms', array($templateForm->id)) }}" class="btn btn-default">{{ trans('general.back') }}</a>
                    </footer>
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
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 150, cssClass:"text-center text-middle", headerSort:false},
                ],
            });
        });
    </script>
@endsection