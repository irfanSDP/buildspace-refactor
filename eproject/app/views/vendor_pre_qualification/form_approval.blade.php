@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorPreQualification.vendorPreQualification') }}}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.index', trans('vendorPreQualification.formLibrary'), array()) }}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.vendorWorkCategories.index', $vendorGroup->name, array($vendorGroup->id)) }}</li>
        <li>{{{ trans('forms.approval') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
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
                        @if($templateForm->isDraft())
                        {{ Form::open(array('route' => array('vendorPreQualification.formLibrary.form.approval.submit', $vendorGroup->id, $vendorWorkCategory->id), 'id' => 'form')) }}
                        @elseif(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $templateForm))
                        {{ Form::open(array('route' => array('vendorPreQualification.formLibrary.form.verify', $vendorGroup->id, $vendorWorkCategory->id), 'id' => 'form')) }}
                        @endif
                        @foreach($flatData as $item)
                            @if($item['type'] == 'node')
                            <input type="hidden" name="node_remarks[{{ $item['nodeId'] }}]" value="{{{ $item['remarks'] }}}"/>
                            @else
                            <input type="hidden" name="score_remarks[{{ $item['id'] }}]" value="{{{ $item['remarks'] }}}"/>
                            @endif
                        @endforeach
                        @if($templateForm->isDraft())
                        <span class="smart-form">
                            @include('verifiers.select_verifiers')
                        </span>
                        @endif
                        @if($templateForm->isDraft())
                        <footer class="pull-right">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">{{ trans('forms.submitForApproval') }}</button>
                        </footer>
                        @elseif(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $templateForm))
                        <footer class="pull-right">
                            <button type="submit" class="btn btn-default" name="submit" value="save">{{ trans('forms.save') }}</button>
                            <button type="submit" class="btn btn-danger" name="submit" value="reject">{{ trans('forms.reject') }}</button>
                            <button type="submit" class="btn btn-success" name="submit" value="approve">{{ trans('forms.approve') }}</button>
                        </footer>
                        @endif
                        {{ Form::close() }}
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

                        if(cell.getData()['type'] == 'node'){
                            description = '<strong>'+description+'</strong>';
                        }
                        
                        return '<span class="'+cssClass+'">'+description+'</span>';
                    }},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                if(rowData['type'] == 'score')
                                {
                                    var checked = rowData['selected'] ? 'checked' : '';
                                    return '<input type="radio" disabled>';
                                }
                            }
                        }
                    }},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", width: 250, hozAlign:"left", headerSort:false, editor: "input", editable: function(cell){return {{ $editable ? true : false }};}, cellEdited:function(cell){
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
                    }}
                ],
            });

            @if($editable)
                mainTable.showColumn('actions');
            @endif
        });
    </script>
@endsection