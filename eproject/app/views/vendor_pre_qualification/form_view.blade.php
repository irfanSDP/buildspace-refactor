@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorPreQualification.vendorPreQualification') }}}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.index', trans('vendorPreQualification.formLibrary'), array()) }}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.vendorWorkCategories.index', $vendorGroup->name, array($vendorGroup->id)) }}</li>
        <li>{{{ $form->name }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-check"></i> {{{ $form->name }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ $form->name }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="main-table"></div>
                        <footer class="pull-right">
                            <a href="{{ route('vendorPreQualification.formLibrary.vendorWorkCategories.index', array($vendorGroup->id, $vendorWorkCategory->id)) }}" class="btn btn-default">{{ trans('general.back') }}</a>
                        </footer>
                    </div>
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
                dataLoaded: function(data){
                    var table = this;
                    $('[data-action=resolve]').on('click', function(){
                        var row = table.getRow($(this).data('id'));
                        var cell = row.getCell('remarks');
                        cell.setValue('');
                    });
                },
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                        var description = cell.getData()['description'];

                        if(cell.getData()['type'] == 'node'){
                            description = '<strong>'+description+'</strong>';
                        }

                        return description;
                    }},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, hozAlign:"center", headerSort:false}
                ],
            });
        });
    </script>
@endsection