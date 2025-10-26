@extends('layout.main')
<?php 
use Carbon\Carbon;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
?>
@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.cycle.index', trans('vendorManagement.vendorPerformanceEvaluationCycles'), array()) }}</li>
        <li>{{ trans('forms.edit') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('forms.edit') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('forms.edit') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::model($cycle, array('route' => array('vendorPerformanceEvaluation.cycle.update', $cycle->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.startDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('start_date') ? 'state-error' : null }}}">
                                    <input type="date" name="start_date" value="{{ Input::old('start_date') ?? Carbon::parse($cycle->start_date)->format('Y-m-d') }}">
                                </label>
                                {{ $errors->first('start_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.endDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('end_date') ? 'state-error' : null }}}">
                                    <input type="date" name="end_date" value="{{ Input::old('end_date') ?? Carbon::parse($cycle->end_date)->format('Y-m-d') }}">
                                </label>
                                {{ $errors->first('end_date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.vpeCycleName') }}}:</label>
                                <label class="input">
                                    {{ Form::textArea('remarks', Input::old('remarks'), array('class' => 'fill-horizontal', 'rows' => 3)) }}
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="checkbox"><input type="checkbox" name="type_180" @if($evaluationType == VendorPerformanceEvaluation::TYPE_180) checked @endif><i></i>{{ trans('vendorPerformanceEvaluation.type180Description') }}</label>
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.cycle.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.evaluations') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <button type="button" class="btn btn-primary btn-md pull-right header-btn" data-toggle="modal" data-target="#excluded-evaluations-table-modal">
                                <i class="fa fa-plus"></i> {{{ trans('vendorManagement.addProjects') }}}
                            </button>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('vendorManagement.projects') }}}:</label>
                            <div id="included-evaluations-table"></div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="excluded-evaluations-table-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('projects.projects') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div id="excluded-evaluations-table"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            showTodayButton: true,
            allowInputToggle: true
        });

        var includedEvaluationsTable = new Tabulator('#included-evaluations-table', {
            height:450,
            ajaxURL: "{{ route('vendorPerformanceEvaluation.cycle.assignedProjects', [$cycle->id]) }}",
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true, frozen:true},
                {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.stage') }}", field:"project_stage", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                {title:"{{ trans('vendorManagement.startDate') }}", field:"start_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                {title:"{{ trans('vendorManagement.endDate') }}", field:"end_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                {title:"{{ trans('general.status') }}", field:"status", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", {{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation::STATUS_DRAFT }}:"{{ trans('vendorManagement.draft') }}", {{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation::STATUS_IN_PROGRESS }}:"{{ trans('vendorManagement.inProgress')}}", {{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation::STATUS_COMPLETED }}:"{{ trans('vendorManagement.completed') }}"}}},
                {title:"{{ trans('vendorManagement.type') }}", field:"type", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", {{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation::TYPE_180 }}:"{{ trans('vendorPerformanceEvaluation.type180') }}", {{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation::TYPE_360 }}:"{{ trans('vendorPerformanceEvaluation.type360')}}"}}},
                {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            show:function(cell){
                                return cell.getData()['can_delete'];
                            },
                            tag: 'button',
                            rowAttributes: {'data-id': 'id'},
                            attributes: {type:'button', class:'btn btn-xs btn-danger', 'data-action':'remove-evaluation', title:"{{ trans('forms.delete') }}"},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class:'fa fa-trash'}
                            }
                        }
                    ]
                }}
            ],
        });

        var excludedEvaluationsTable = new Tabulator('#excluded-evaluations-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true, frozen:true},
                {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                {title:"{{ trans('projects.stage') }}", field:"project_stage", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle"},
                {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            tag: 'button',
                            rowAttributes: {'data-id': 'id'},
                            attributes: {class:'btn btn-xs btn-success', 'data-action':'include-evaluation', title:"{{ trans('forms.add') }}"},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class:'fa fa-plus'}
                            }
                        }
                    ]
                }}
            ],
        });

        $('#excluded-evaluations-table-modal').on('show.bs.modal', function(){
            excludedEvaluationsTable.setData("{{ route('vendorPerformanceEvaluation.cycle.unassignedProjects', [$cycle->id]) }}");
        });

        $('#excluded-evaluations-table').on('click', '[data-action=include-evaluation]', function(){
            excludedEvaluationsTable.modules.ajax.showLoader();
            includedEvaluationsTable.modules.ajax.showLoader();
            $.post(excludedEvaluationsTable.getRow($(this).data('id')).getData()['route:add_project'], {
                evaluation_type: "{{ $evaluationType }}",
                _token: '{{ csrf_token() }}'
            })
            .done(function(data){
                if(data.success){
                    SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.evaluationAdded')}}");
                    excludedEvaluationsTable.setData();
                    includedEvaluationsTable.setData();
                }
                else{
                    if(data.errors.length > 0){
                        $.smallBox({
                            title : "{{ trans('general.anErrorHasOccured') }}",
                            content : "<i class='fa fa-times'></i> <i>" + data.errors[0].msg + "</i>",
                            color : "#C46A69",
                            sound: false,
                            iconSmall : "fa fa-exclamation-triangle shake animated"
                        });
                    }
                }
                excludedEvaluationsTable.modules.ajax.hideLoader();
                includedEvaluationsTable.modules.ajax.hideLoader();
            })
            .fail(function(){
                excludedEvaluationsTable.modules.ajax.hideLoader();
                includedEvaluationsTable.modules.ajax.hideLoader();
                SmallErrorBox.refreshAndRetry();
            });
        });
        $('#included-evaluations-table').on('click', '[data-action=remove-evaluation]', function(){
            includedEvaluationsTable.modules.ajax.showLoader();
            $.post(includedEvaluationsTable.getRow($(this).data('id')).getData()['route:remove_project'], {
                _token: '{{ csrf_token() }}'
            })
            .done(function(data){
                if(data.success){
                    SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.evaluationRemoved')}}");
                }
                includedEvaluationsTable.setData();
                includedEvaluationsTable.modules.ajax.hideLoader();
            })
            .fail(function(){
                includedEvaluationsTable.modules.ajax.hideLoader();
                SmallErrorBox.refreshAndRetry();
            });
        });
    </script>
@endsection