@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.setups.index', trans('vendorManagement.setup'), array()) }}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.setups.evaluations.vendors.index', $evaluation->project->short_title, array($evaluation->id)) }}</li>
        <li>{{{ $company->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('forms.update') }}}
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
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.setups.evaluations.vendors.update', $evaluation->id, $company->id), 'class' => 'smart-form')) }}
                        @if($canAssignForm)
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.forms') }}}:</label>
                                {{ $errors->first('form_id', '<em class="invalid">:message</em>') }}
                                <div id="forms-table"></div>
                            </section>
                        </div>
                        @endif
                        @if($canAssignEvaluators)
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
                        @endif
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.setups.evaluations.vendors.index', trans('forms.back'), array($evaluation->id), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.generic_table_modal', [
    'modalId'    => 'form-options-modal',
    'title'      => trans('vendorManagement.forms'),
    'tableId'    => 'form-options-table',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@include('templates.modals.confirmation', ['modalId' => 'delete-modal'])
@endsection

@section('js')
    <script>
        @if($canAssignEvaluators)
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
        @endif
        @if($canAssignForm)
        var formsTable = new Tabulator('#forms-table', {
            height:250,
            placeholder: "{{ trans('vendorManagement.noEvaluationForms') }}",
            ajaxURL: "{{ route('vendorPerformanceEvaluation.setups.evaluations.forms', [$evaluation->id, $company->id]) }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_category", width:300, cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('vendorManagement.form') }}", field:"form", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: [
                        {
                            tag: 'button',
                            attributes: {type: 'button', 'data-action': 'update', class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        },{
                            innerHtml:function(){
                                return "&nbsp;";
                            }
                        },{
                            opaque:function(cell)
                            {
                                return cell.getData()['can_delete'];
                            },
                            tag: 'button',
                            attributes: {type: 'button', 'data-action': 'delete', class:'btn btn-xs btn-danger', title: '{{ trans("general.unlink") }}'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-trash'}
                            }
                        }
                    ]
                }}
            ],
        });

        var formOptionsTable = new Tabulator('#form-options-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('vendorPerformanceEvaluation.setups.evaluations.forms.options', [$evaluation->id, $company->id]) }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    tag: 'button',
                    attributes: {type: 'button', 'data-action': 'select', class:'btn btn-xs btn-default', title: '{{ trans("forms.select") }}'},
                    rowAttributes: {'data-id': 'id'},
                    innerHtml: function(rowData)
                    {
                        return "{{ trans('forms.select') }}";
                    }
                }}
            ],
        });

        var updateRoute;
        var deleteRoute;
        var selectedId;

        $('#forms-table').on('click', '[data-action=update]', function(){
            var data = formsTable.getRow($(this).data('id')).getData();
            formOptionsTable.setData();
            updateRoute = data['route:update'];
            $('#form-options-modal').modal('show');
        });
        function updateSelection(){
            app_progressBar.show();
            app_progressBar.maxOut();
            $.post(updateRoute, {
                id: selectedId,
                _token: "{{ csrf_token() }}"
            }, function(data){
                if(data['success'])
                {
                    formsTable.setData();
                    $('#form-options-modal').modal('hide');
                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                }
                else
                {
                    SmallErrorBox.formValidationError("{{ trans('general.somethingWentWrong') }}", data['errorMessage']);
                }

                app_progressBar.hide();
            });
        }
        $('#form-options-table').on('click', '[data-action=select]', function(){
            var data = formOptionsTable.getRow($(this).data('id')).getData();
            selectedId = data['id'];
            updateSelection();
        });
        $('#forms-table').on('click', '[data-action=delete]', function(){
            var data = formsTable.getRow($(this).data('id')).getData();
            deleteRoute = data['route:delete'];
            $('#delete-modal').modal('show');
        });
        $('#delete-modal').on('click', '[data-action=proceed]', function(){
            app_progressBar.show();
            app_progressBar.maxOut();
            $.post(deleteRoute, {
                _method: 'delete',
                _token: "{{ csrf_token() }}"
            }, function(data){
                if(data['success'])
                {
                    formsTable.setData();
                    $('#form-options-modal').modal('hide');
                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                }
                else
                {
                    SmallErrorBox.formValidationError("{{ trans('general.somethingWentWrong') }}", data['errorMessage']);
                }

                app_progressBar.hide();
            });
        });
        @endif
    </script>
@endsection