@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}</li>
        <li>{{ link_to_route('digital-star.cycle.index', trans('digitalStar/vendorManagement.cycle'), array()) }}</li>
        <li>{{ link_to_route('digital-star.setups.index', trans('digitalStar/vendorManagement.setup'), array($evaluation->ds_cycle_id)) }}</li>
        <li>{{ link_to_route('digital-star.setups.evaluators.index', trans('digitalStar/digitalStar.evaluators'), array($evaluation->id)) }}</li>
        <li>{{ trans('digitalStar/digitalStar.projectEvaluators') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            {{ $project->title }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{ trans('digitalStar/digitalStar.projectEvaluators') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="row margin-top-5">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <button type="button" class="btn btn-primary btn-md pull-right header-btn" data-toggle="modal" data-target="#unassigned-table-modal">
                                <i class="fa fa-plus"></i> {{{ trans('digitalStar/digitalStar.assignEvaluators') }}}
                            </button>
                        </section>
                    </div>
                    <div class="row margin-top-10">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div id="assigned-table"></div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="unassigned-table-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('digitalStar/digitalStar.assignEvaluators') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div id="unassigned-table"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var assignedTable = new Tabulator('#assigned-table', {
                height: 450,
                ajaxURL: "{{ route('digital-star.setups.evaluators.project.assigned', [$evaluation->id, $project->id]) }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 10,
                pagination: "remote",
                ajaxFiltering: true,
                layout: "fitColumns",
                columns:[
                    {title: "{{ trans('general.no') }}", field: "counter", width: 60, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, frozen: true},
                    {title: "{{ trans('users.name') }}", field: "user_name", hozAlign: "left", headerSort: false, headerFilter: true},
                    {title: "{{ trans('users.email') }}", field: "user_email", hozAlign: 'left', cssClass: "text-left text-middle", headerSort: false, headerFilter: true},
                    {title: "{{ trans('users.company') }}", field: "company_name", hozAlign: 'left', headerSort: false, cssClass: "text-left text-middle", headerFilter: true},
                    {title: "{{ trans('digitalStar/digitalStar.vendorGroup') }}", field: "vendor_group", hozAlign: 'left', headerSort: false, cssClass: "text-left text-middle", headerFilter: true},
                    {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                rowAttributes: {'data-id': 'id'},
                                attributes: {type:'button', class:'btn btn-xs btn-danger', 'data-action':'unassign', title:"{{ trans('digitalStar/digitalStar.unassign') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-trash'}
                                }
                            }
                        ]
                    }}
                ],
            });

            var unassignedTable = new Tabulator('#unassigned-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 10,
                pagination: "remote",
                ajaxFiltering: true,
                layout: "fitColumns",
                columns:[
                    {title: "{{ trans('general.no') }}", field: "counter", width: 60, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, frozen: true},
                    {title: "{{ trans('users.name') }}", field: "user_name", hozAlign: "left", headerSort: false, headerFilter: true},
                    {title: "{{ trans('users.email') }}", field: "user_email", hozAlign: 'left', cssClass: "text-left text-middle", headerSort: false, headerFilter: true},
                    {title: "{{ trans('users.company') }}", field: "company_name", hozAlign: 'left', headerSort: false, cssClass: "text-left text-middle", headerFilter: true},
                    {title: "{{ trans('digitalStar/digitalStar.vendorGroup') }}", field: "vendor_group", hozAlign: 'left', headerSort: false, cssClass: "text-left text-middle", headerFilter: true},
                    {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                rowAttributes: {'data-id': 'id'},
                                attributes: {class:'btn btn-xs btn-success', 'data-action':'assign', title:"{{ trans('digitalStar/digitalStar.assign') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-plus'}
                                }
                            }
                        ]
                    }}
                ],
            });

            $('#unassigned-table-modal').on('show.bs.modal', function(){
                unassignedTable.setData("{{ route('digital-star.setups.evaluators.project.unassigned', [$evaluation->id, $project->id]) }}");
            });

            $('#unassigned-table').on('click', '[data-action=assign]', function(){
                unassignedTable.modules.ajax.showLoader();
                assignedTable.modules.ajax.showLoader();
                $.post(unassignedTable.getRow($(this).data('id')).getData()['route:assign'], {
                    _token: '{{ csrf_token() }}',
                    'uid': $(this).data('id')
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('digitalStar/digitalStar.assignedSuccessfully')}}");
                        unassignedTable.setData();
                        assignedTable.setData();
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
                    unassignedTable.modules.ajax.hideLoader();
                    assignedTable.modules.ajax.hideLoader();
                })
                .fail(function(){
                    unassignedTable.modules.ajax.hideLoader();
                    assignedTable.modules.ajax.hideLoader();
                    SmallErrorBox.refreshAndRetry();
                });
            });

            $('#assigned-table').on('click', '[data-action=unassign]', function() {
                assignedTable.modules.ajax.showLoader();
                $.post(assignedTable.getRow($(this).data('id')).getData()['route:unassign'], {
                    _token: '{{ csrf_token() }}',
                    'uid': $(this).data('id')
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('digitalStar/digitalStar.unassignedSuccessfully')}}");
                    }
                    assignedTable.setData();
                    assignedTable.modules.ajax.hideLoader();
                })
                .fail(function(){
                    assignedTable.modules.ajax.hideLoader();
                    SmallErrorBox.refreshAndRetry();
                });
            });
        });
    </script>
@endsection