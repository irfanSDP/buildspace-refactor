@extends('layout.main')
@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('digital-star.cycle.index', trans('digitalStar/vendorManagement.vendorPerformanceEvaluationCycles'), array()) }}</li>
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
                    {{ Form::model($cycle, array('route' => array('digital-star.cycle.update', $cycle->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.startDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('start_date') ? 'state-error' : null }}}">
                                    <input type="date" name="start_date" value="{{ Input::old('start_date') ?? \Carbon\Carbon::parse($cycle->start_date)->format('Y-m-d') }}">
                                </label>
                                {{ $errors->first('start_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.endDate') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('end_date') ? 'state-error' : null }}}">
                                    <input type="date" name="end_date" value="{{ Input::old('end_date') ?? \Carbon\Carbon::parse($cycle->end_date)->format('Y-m-d') }}">
                                </label>
                                {{ $errors->first('end_date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.vpeCycleName') }}}:</label>
                                <label class="input">
                                    {{ Form::textArea('remarks', Input::old('remarks'), array('class' => 'fill-horizontal', 'rows' => 3)) }}
                                </label>
                            </section>
                        </div>

                        <div class="row">
                            <div class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('digitalStar/digitalStar.weightageStarRating') }}}</label>
                            </div>

                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('digitalStar/digitalStar.weightageCompany') }}} <span class="required">*</span>:</label>
                                <label class="input">
                                    <i class="icon-append">%</i>
                                    {{ Form::number('weight_company',
                                        isset($weightage) ? $weightage['company'] : Input::old('weight_company'), [
                                        'required' => 'required',
                                        'min' => 0,
                                        'max' => 100,
                                    ]) }}
                                </label>
                                <em class="invalid" data-error="weight_company"></em>
                            </section>

                            <section class="col col-xs-12 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('digitalStar/digitalStar.weightageProject') }}} <span class="required">*</span>:</label>
                                <label class="input">
                                    <i class="icon-append">%</i>
                                    {{ Form::number('weight_project',
                                        isset($weightage) ? $weightage['project'] : Input::old('weight_project'), [
                                        'required' => 'required',
                                        'min' => 0,
                                        'max' => 100,
                                    ]) }}
                                </label>
                                <em class="invalid" data-error="weight_project"></em>
                            </section>
                        </div>

                        <footer>
                            {{ link_to_route('digital-star.cycle.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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
                <h2>{{{ trans('digitalStar/digitalStar.assignForm') }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="assigned-forms-table"></div>
                </div>
            </div>
        </div>
        @include('templates.generic_table_modal', [
            'modalId'    => 'templateSelectionModal',
            'title'      => trans('digitalStar/digitalStar.assignForm'),
            'tableId'    => 'templateSelectionTable',
            'showSubmit' => true,
            'showCancel' => true,
            'cancelText' => trans('forms.close'),
        ])
        @include('templates.yesNoModal', [
            'modalId'   => 'yesNoModal',
            'titleId'   => 'yesNoModalTitle',
            'title'     => trans('general.confirmation'),
            'message'   => trans('projectReport.sureToUnlinkTemplate'),
        ])
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('digitalStar/vendorManagement.evaluations') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <button type="button" class="btn btn-primary btn-md pull-right header-btn" data-toggle="modal" data-target="#excluded-evaluations-table-modal">
                                <i class="fa fa-plus"></i> {{{ trans('digitalStar/digitalStar.addCompany') }}}
                            </button>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('digitalStar/digitalStar.companies') }}}:</label>
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
                    {{{ trans('digitalStar/digitalStar.companies') }}}
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
        $(document).ready(function() {
            $('.datetimepicker').datetimepicker({
                format: 'DD-MMM-YYYY',
                showTodayButton: true,
                allowInputToggle: true
            });

            const weightCompanyInput = $("[name='weight_company']");
            const weightProjectInput = $("[name='weight_project']");

            if (weightCompanyInput.length === 0 || weightProjectInput.length === 0) {
                console.warn("Weight inputs not found.");
                return;
            }

            function adjustWeights(changedInput, otherInput) {
                let changedValue = parseInt(changedInput.val(), 10) || 0;

                changedValue = Math.min(100, Math.max(0, changedValue));
                changedInput.val(changedValue);

                otherInput.val(100 - changedValue);
            }

            weightCompanyInput.on("input", function () {
                adjustWeights(weightCompanyInput, weightProjectInput);
            });

            weightProjectInput.on("input", function () {
                adjustWeights(weightProjectInput, weightCompanyInput);
            });

            let templateSelectionTable = null;

            const actionsFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                const container = document.createElement('div');

                if (rowData.hasOwnProperty('route:assign_form')) {
                    const bindTemplateButton = document.createElement('a');
                    bindTemplateButton.dataset.toggle = 'tooltip';
                    bindTemplateButton.title = "{{ trans('digitalStar/digitalStar.assignForm') }}";
                    bindTemplateButton.className = 'btn btn-xs btn-primary';
                    bindTemplateButton.innerHTML = '<i class="fas fa-link"></i>';
                    bindTemplateButton.style['margin-right'] = '5px';
                    bindTemplateButton.dataset.toggle = 'modal';
                    bindTemplateButton.dataset.target = '#templateSelectionModal';

                    bindTemplateButton.addEventListener('click', function(e) {
                        $('#templateSelectionModal [data-action="actionSave"]').data('url', rowData['route:assign_form']);

                        if(cell.getRow().getData().hasOwnProperty('template_id')) {
                            $('#templateSelectionModal').data('template_id', rowData.template_id);
                        } else {
                            $('#templateSelectionModal').data('template_id', null);
                        }

                        if(cell.getRow().getData().hasOwnProperty('template_type')) {
                            $('#templateSelectionModal').data('type', rowData['type']);
                        } else {
                            $('#templateSelectionModal').data('type', null);
                        }
                    });
                    container.appendChild(bindTemplateButton);
                }

                return container;
            };

            const assignFormTable = new Tabulator('#assigned-forms-table', {
                //fillHeight: true,
                pagination: "local",
                paginationSize: 30,
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('digitalStar/digitalStar.type') }}", field: 'template_type', width:120, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('digitalStar/digitalStar.templateName') }}", field: 'template_title', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
                ],
                layout: "fitColumns",
                ajaxURL: "{{ route('digital-star.cycle.assign-form.list', [$cycle->id]) }}",
                placeholder: "{{ trans('digitalStar/digitalStar.noTemplatesAvailable') }}",
                columnHeaderSortMulti: false,
            });

            $('#templateSelectionModal').on('shown.bs.modal', function (e) {
                e.preventDefault();

                templateSelectionTable = new Tabulator('#templateSelectionTable', {
                    height:300,
                    columns: [
                        { formatter:"rowSelection", width:30, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false },
                        { title: "{{ trans('digitalStar/digitalStar.title') }}", field:"template_name", cssClass:"text-left", align: 'left', headerSort: false, headerFilter: 'input' },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('digital-star.cycle.assign-form.assignable-forms', [$cycle->id]) }}",
                    ajaxConfig: "GET",
                    pagination:"local",
                    selectable: 1,
                    placeholder:"{{{ trans('general.noRecordsFound') }}}",
                    columnHeaderSortMulti:false,
                    dataLoaded: function(data) {
                        const templateId = $('#templateSelectionModal').data('template_id');

                        if(templateId == null) return;

                        this.selectRow(templateId);
                    },
                    rowSelectionChanged:function(data, rows){
                        $('#templateSelectionModal [data-action="actionSave"]').prop('disabled', (rows.length == 0));
                    },
                });
            });

            $(document).on('click', '#templateSelectionModal [data-action="actionSave"]', bindTemplateHandler);
            //$(document).on('click', '#yesNoModal [data-action="actionYes"]', unbindTemplateHandler);

            async function bindTemplateHandler(e) {
                e.preventDefault();

                app_progressBar.toggle();

                const url = $(this).data('url');
                const [templateId] = templateSelectionTable.getSelectedData().map(data => data.id);

                try {
                    const options = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            templateId: templateId,
                            _token: '{{{ csrf_token() }}}'
                        }),
                    };

                    const promise = await fetch(url, options);

                    if(!promise.ok || (promise.status !== 200)) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    const response = await promise.json();

                    if(!response.success) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    $('#templateSelectionModal').modal('hide');
                    assignFormTable.setData();
                } catch(err) {
                    console.error(err.message);
                    SmallErrorBox.refreshAndRetry();
                } finally {
                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            }

            /*async function unbindTemplateHandler(e) {
                e.preventDefault();

                const url = $(this).data('url');

                try {
                    const options = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            _token: '{{{ csrf_token() }}}'
                        }),
                    };

                    const promise = await fetch(url, options);

                    if(!promise.ok || (promise.status !== 200)) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    const response = await promise.json();

                    if(!response.success) {
                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    $('#yesNoModal').modal('hide');
                    assignFormTable.setData();
                } catch(err) {
                    console.error(err.message);
                    SmallErrorBox.refreshAndRetry();
                } finally {
                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            }*/

            var includedEvaluationsTable = new Tabulator('#included-evaluations-table', {
                height:450,
                ajaxURL: "{{ route('digital-star.cycle.assignedCompanies', [$cycle->id]) }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                    {title:"{{ trans('digitalStar/digitalStar.company') }}", field:"company_name", hozAlign:"left", headerSort:false, cssClass:"text-left text-middle", headerFilter:true},
                    {title:"{{ trans('digitalStar/digitalStar.vendorGroup') }}", field:"vendor_group", hozAlign:"left", headerSort:false, cssClass:"text-left text-middle", headerFilter:true},
                    {title:"{{ trans('digitalStar/vendorManagement.startDate') }}", field:"start_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:false},
                    {title:"{{ trans('digitalStar/vendorManagement.endDate') }}", field:"end_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:false},
                    {title:"{{ trans('general.status') }}", field:"status", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", {{ \PCK\DigitalStar\Evaluation\DsEvaluation::STATUS_DRAFT }}:"{{ trans('digitalStar/vendorManagement.draft') }}", {{ \PCK\DigitalStar\Evaluation\DsEvaluation::STATUS_IN_PROGRESS }}:"{{ trans('digitalStar/vendorManagement.inProgress')}}", {{ \PCK\DigitalStar\Evaluation\DsEvaluation::STATUS_COMPLETED }}:"{{ trans('digitalStar/vendorManagement.completed') }}"}}},
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
                    {title:"{{ trans('digitalStar/digitalStar.company') }}", field:"company_name", hozAlign:"left", headerSort:false, cssClass:"text-left text-middle", headerFilter:true},
                    {title:"{{ trans('digitalStar/digitalStar.vendorGroup') }}", field:"vendor_group", hozAlign:"left", headerSort:false, cssClass:"text-left text-middle", headerFilter:true},
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
                excludedEvaluationsTable.setData("{{ route('digital-star.cycle.unassignedCompanies', [$cycle->id]) }}");
            });

            $('#excluded-evaluations-table').on('click', '[data-action=include-evaluation]', function(){
                excludedEvaluationsTable.modules.ajax.showLoader();
                includedEvaluationsTable.modules.ajax.showLoader();
                $.post(excludedEvaluationsTable.getRow($(this).data('id')).getData()['route:add_company'], {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('digitalStar/vendorManagement.evaluationAdded')}}");
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
                $.post(includedEvaluationsTable.getRow($(this).data('id')).getData()['route:remove_company'], {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('digitalStar/vendorManagement.evaluationRemoved')}}");
                    }
                    includedEvaluationsTable.setData();
                    includedEvaluationsTable.modules.ajax.hideLoader();
                })
                .fail(function(){
                    includedEvaluationsTable.modules.ajax.hideLoader();
                    SmallErrorBox.refreshAndRetry();
                });
            });
        });
    </script>
@endsection