@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->company->name, array($vendorRegistration->id)) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.preQualification', trans('vendorManagement.preQualification'), array($vendorRegistration->id, $vendorPreQualification->id)) }}</li>
        <li>{{{ $form->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-check"></i> {{{ trans('forms.approval') }}}
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
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label pull-right">{{ trans('vendorPerformanceEvaluation.totalScore') }} : <span id="totalSelectedScore"></span> / 100</label>
                        </section>
                    </div>
                    {{ Form::open(array('route' => array('vendorManagement.approval.preQualification.approval.save', $vendorRegistration->id, $vendorPreQualification->id), 'id' => 'form')) }}
                    @foreach($flatData as $item)
                        @if($item['type'] == 'score')
                        <input type="hidden" name="score_remarks[{{ $item['id'] }}]" value="{{{ $item['remarks'] }}}"/>
                        @endif
                    @endforeach
                    @if($editable)
                    <footer class="pull-right">
                        <button type="submit" class="btn btn-default" name="submit" value="save">{{ trans('forms.save') }}</button>
                        <button type="submit" class="btn btn-danger" name="submit" value="reject">{{ trans('forms.reject') }}</button>
                    </footer>
                    @endif
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@include('uploads.downloadModal')
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var excludedIds    = {};
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

                    excludedIds = {{ json_encode($excludedIds) }};

                    var excludedRow;
                    for(var nodeId in excludedIds){
                        $('input[type=checkbox][data-node-id="'+nodeId+'"]').prop('checked', true);

                        excludedRow = getNestedRow(this, 'node-'+excludedIds[nodeId]);

                        if(excludedRow) excludedRow.treeCollapse();
                    }
                },
                renderComplete: function() {
                    $('#totalSelectedScore').text("{{ $vpqScore }}");
                },
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                        var cellData = cell.getData();

                        var cssClass = '';
                        if(cell.getData()['remarks'])
                        {
                            if(cell.getData()['amendmentsRequired']){
                                cssClass = 'text-danger';
                            }
                            else{
                                cssClass = "text-success";
                            }
                        }

                        var description = cellData['description'];

                        if(cell.getData()['type'] == 'node'){
                            description = '<strong>'+description+'</strong>';
                        }
                        else if(cell.getData()['type'] == 'score' && cell.getData()['selected']){
                            description = '<strong>'+description+'</strong>';
                        }
                        
                        return '<span class="'+cssClass+'">'+description+'</span>';
                    }},
                    {title:"{{ trans('forms.notApplicable') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                if(rowData['type'] == 'node' && rowData['depth'] > 0 && rowData['hasScores'])
                                {
                                    var checked = rowData['is_excluded'] ? 'checked' : '';
                                    return '<input type="checkbox" name="exclude-'+rowData['nodeId']+'" data-action="exclude-node" data-id="'+rowData['id']+'" data-node-id="'+rowData['nodeId']+'" '+checked+' disabled>';
                                }
                            }
                        }
                    }},
                    {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' },
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: {
                            show: function(cell){
                                return cell.getData()['route:getDownloads'];
                            },
                            tag:'button',
                            attributes: {type:'button', 'class':'btn btn-xs btn-info', 'data-toggle':'modal', 'data-target': '#downloadModal', 'data-action':'get-downloads'},
                            rowAttributes: {'data-get-downloads':'route:getDownloads'},
                            innerHtml:{
                                tag:'i',
                                attributes:{class:'fa fa-paperclip'}
                            }
                        }
                    }},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", width: 250, hozAlign:"left", headerSort:false, editor: "input", editable: function(cell){
                        if(cell.getData()['type'] == 'node') return false;

                        return {{ $editable ? true : false }};
                    }, cellEdited:function(cell){
                        if(cell.getData()['type'] == 'node'){
                            $('[name="node_remarks['+cell.getData()['nodeId']+']"]').val(cell.getValue());
                        }
                        else{
                            $('[name="score_remarks['+cell.getData()['id']+']"]').val(cell.getValue());
                        }
                    }},
                    {title:"{{ trans('general.actions') }}", field:'actions', width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, visible: false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'button',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorManagement.resolved") }}', 'data-action': 'resolve'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-check'}
                                }
                            }
                        ]
                    }},
                ],
            });

            @if($editable)
                mainTable.showColumn('actions');
            @endif

            function getNestedRow(table, id)
            {
                // hackish way of getting a nested row since tabulator doesn't have a method for it.
                function traverseAndFind(rows, targetId)
                {
                    var row, resultFromChildSearch;

                    for(var i in rows)
                    {
                        row = rows[i];

                        if(row.getData()['id'] == targetId) return row;

                        if(row.getData()['hasScores']) continue;

                        resultFromChildSearch = traverseAndFind(row.getTreeChildren(), targetId);

                        if(resultFromChildSearch) return resultFromChildSearch;
                    }

                    return false;
                }

                return traverseAndFind(table.getRows(), id);
            }
        });
    </script>
@endsection