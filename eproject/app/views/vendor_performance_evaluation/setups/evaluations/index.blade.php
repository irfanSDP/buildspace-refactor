@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ trans('vendorManagement.setup') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.setup') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            @include('vendor_performance_evaluation.setups.evaluations.index_action_menu', array('classes' => 'pull-right'))
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ empty($cycle->remarks) ? trans('vendorManagement.vendorPerformanceEvaluationCycle') : $cycle->remarks }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                ajaxURL: "{{ ! empty($cycle) ? route('vendorPerformanceEvaluation.setups.list', array('cycle' => $cycle->id)) : route('vendorPerformanceEvaluation.setups.list') }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('vendorManagement.projectStage') }}", field:"project_stage", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($projectStageFilterOptions, JSON_FORCE_OBJECT) }}},
                    {title:"{{ trans('vendorManagement.startDate') }}", field:"start_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.endDate') }}", field:"end_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($statusFilterOptions) }}},
                    {title:"{{ trans('vendorManagement.assignedVendors') }}", field:"assigned_vendors", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($assignedVendorsFilterOptions, JSON_FORCE_OBJECT) }}, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(cellData)
                        {
                            var content = cellData['assigned_company_number']+'/'+cellData['total_company_number'];
                            var classes = 'text-warning';
                            if(cellData['assigned_company_number'] === cellData['total_company_number']){
                                classes = 'text-success';
                            }
                            return '<strong class="'+classes+'">'+content+'</strong>';
                        }
                    }},
                    {title:"{{ trans('general.actions') }}", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-primary', 'data-action':'resend-vpe-form-assigned-email', 'title': '{{ trans("vendorPerformanceEvaluation.sendReminders") }}'},
                                rowAttributes: {'data-url': 'route:resend_vpe_form_assigned_email'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-envelope'},
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("vendorManagement.vendors") }}'},
                                rowAttributes: {'href': 'route:vendors'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'},
                                    innerHtml: function(rowData){
                                        return " {{ trans('vendorManagement.vendors') }}";
                                    }
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['route:edit'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            }
                        ]
                    }}
                ],
            });

            $(document).on('click', '[data-action="resend-vpe-form-assigned-email"]', function(e) {
                e.preventDefault();

                app_progressBar.toggle();
                app_progressBar.maxOut();

                var url = $(this).data('url');

                $.post(url, {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response){
                    if(response.success){
                        app_progressBar.toggle();

                        $.smallBox({
                            title : "{{ trans('general.success') }}",
                            content : "<i class='fa fa-check'></i> <i>Reminders sent successfully.</i>",
                            color : "#739E73",
                            sound: false,
                            iconSmall : "fa fa-paper-plane",
                            timeout : 5000
                        });
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                    app_progressBar.toggle();
                });

            });

            $('[data-action=export-setups]').on('click', function(){
                var filters = mainTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorPerformanceEvaluation.setups.list.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));

                    }
                }

                if(parameters.length){
                    url += '&'+parameters.join('&');
                }

                window.open(url, '_blank');
            });
        });
    </script>
@endsection