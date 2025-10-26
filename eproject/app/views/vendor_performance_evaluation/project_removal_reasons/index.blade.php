@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorPerformanceEvaluation.projectRemovalReasons') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorPerformanceEvaluation.projectRemovalReasons') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorPerformanceEvaluation.projectRemovalReasons.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('vendorPerformanceEvaluation.projectRemovalReasons') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="main-table"></div>
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.projectRemovalReasons.update'))) }}
                    @foreach($recordIds as $id)
                    <input hidden type="checkbox" name="id[{{ $id }}]" value="{{ $id }}"/>
                    @endforeach
                    <footer class="pull-right">
                        <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
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
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                dataLoaded: function(data){
                    var selectedIds = {{ json_encode($hiddenIds) }};

                    for(var i in selectedIds){
                        $('input[type=checkbox][name="id['+selectedIds[i]+']"]').prop('checked', true);
                    }
                },
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorPerformanceEvaluation.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('vendorPerformanceEvaluation.hidden') }}", field: 'hidden', width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                var checked = rowData['hidden'] ? 'checked' : '';
                                return '<input type="checkbox" data-action="hide" data-id="'+rowData['id']+'" '+checked+'>';
                            }
                        }
                    }}
                ],
            });

            $('#main-table').on('click', 'input[data-action=hide]', function(){
                $('input[type=checkbox][name="id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });
        });
    </script>
@endsection