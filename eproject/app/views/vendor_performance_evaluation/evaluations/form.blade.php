@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.index', trans('vendorManagement.evaluations'), array()) }}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.evaluations.vendors.index', $evaluation->project->short_title, array($evaluation->id)) }}</li>
        <li>{{{ $company->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.formUpdate', $evaluation->id, $company->id), 'id' => 'scores-form')) }}
                    <div id="main-table"></div>
                    <input type="hidden" name="submit_type">
                    {{ Form::close() }}
                    @if(!$readOnly)
                    <footer class="pull-right">
                        <button type="button" class="btn btn-default" data-action="save">{{ trans('forms.save') }}</button>
                        <button type="button" class="btn btn-primary" data-action="submit">{{ trans('forms.submit') }}</button>
                    </footer>
                    @endif
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
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                        var cellData = cell.getData();
                        var spaces = '';

                        for(var i=0;i<cellData['depth'];i++) spaces+='&nbsp;&nbsp;&nbsp;&nbsp;';
                        
                        return spaces+cellData['description'];
                    }},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                if(rowData['type'] == 'score')
                                {
                                    var checked = rowData['selected'] ? 'checked' : '';
                                    return '<input type="radio" name="'+rowData['name']+'" '+checked+' value="'+rowData['id']+'" @if($readOnly)disabled @endif>';
                                }
                            }
                        }
                    }}
                ],
            });

            $('[data-action=submit]').on('click', function(){
                $('#scores-form input[name=submit_type]').val('submit');
                $('#scores-form').submit();
            });

            $('[data-action=save]').on('click', function(){
                $('#scores-form input[name=submit_type]').val('save');
                $('#scores-form').submit();
            });
        });
    </script>
@endsection