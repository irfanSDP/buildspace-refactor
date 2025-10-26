@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.index', trans('vendorManagement.evaluations'), array()) }}</li>
        <li>{{{ $evaluation->project->short_title }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.evaluators') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $evaluation->project->short_title }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.evaluations.evaluators.update', $evaluation->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.evaluators') }}}<span class="required">*</span>:</label>
                                {{ $errors->first('evaluator_ids', '<em class="invalid">:message</em>') }}
                                <div id="main-table"></div>
                                <div hidden>
                                    @foreach($evaluatorIds ?? [] as $evaluatorId)
                                        {{ Form::checkbox('evaluator_ids[]', $evaluatorId ) }}
                                    @endforeach
                                </div>
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
        var mainTable = new Tabulator('#main-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            data: {{ json_encode($data) }},
            layout:"fitColumns",
            dataLoaded:function(data){
                var selectedEvaluatorIds = {{json_encode($selectedEvaluatorIds)}};
                this.selectRow(selectedEvaluatorIds);
            },
            rowSelectionChanged:function(data, rows){
                $("input[type=checkbox][name='evaluator_ids[]']").prop("checked", false);
                var selectedEvaluatorIds = this.getSelectedData().map(a => a.id);
                for(var i in selectedEvaluatorIds){
                    $("input[type=checkbox][name='evaluator_ids[]'][value="+selectedEvaluatorIds[i]+"]").prop("checked", true);
                }
            },
            columns:[
                {formatter:"rowSelection", titleFormatter:"rowSelection", width: 10, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, cellClick:function(e, cell){
                    cell.getRow().toggleSelect();
                }},
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('users.name') }}", field:"name", minWidth:300, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('users.email') }}", field:"email", width:300, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true}
            ],
        });
    </script>
@endsection