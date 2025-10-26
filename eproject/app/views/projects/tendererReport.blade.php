@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>Tenderers' Report</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{ trans('tenders.tenderersReport') }}
                <small>
                    Closed at <b>{{{ $project->getProjectTimeZoneTime($project->latestTender->tender_closing_date) }}}</b>
                </small>
            </h1>
        </div>
        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                @include('projects.partials.tenderer_report_action_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('tenders.tendererRateListing') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div id="tenderers-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.generic_table_modal', [
        'modalId'          => 'withdrawn-tenders-modal',
        'title'            => trans('tenders.withdrawnTenders'),
        'tableId'          => 'withdrawn-tenders-table',
        'modalDialogClass' => 'modal-xl',
        'tablePadding'     => true,
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'participated-tenders-modal',
        'title'            => trans('tenders.participatedTenders'),
        'tableId'          => 'participated-tenders-table',
        'modalDialogClass' => 'modal-xl',
        'tablePadding'     => true,
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'ongoing-projects-modal',
        'title'            => trans('tenders.ongoingProjects'),
        'tableId'          => 'ongoing-projects-table',
        'modalDialogClass' => 'modal-xl',
        'tablePadding'     => true,
        'showInfo'         => true,
        'infoText'         => trans('projects.totalContractSum'),
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'completed-projects-modal',
        'title'            => trans('tenders.completedProjects'),
        'tableId'          => 'completed-projects-table',
        'modalDialogClass' => 'modal-xl',
        'tablePadding'     => true,
        'showInfo'         => true,
        'infoText'         => trans('projects.totalContractSum'),
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'total-contract-sum-modal',
        'title'            => trans('projects.totalContractSum'),
        'tableId'          => 'total-contract-sum-table',
        'modalDialogClass' => 'modal-xs',
        'tablePadding'     => true,
    ])
    <div id="templates" style="visibility: hidden;">
        <div data-id="info" class="well">
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/app/modalStack.js') }}"></script>
    <script>
        $(document).ready(function() {
            var modalStack = new ModalStack();
            $('#withdrawn-tenders-modal [data-id=info-div]').append($('#templates [data-id=info]').clone());
            $('#participated-tenders-modal [data-id=info-div]').append($('#templates [data-id=info]').clone());
            $('#ongoing-projects-modal [data-id=info-div]').append($('#templates [data-id=info]').clone());
            $('#completed-projects-modal [data-id=info-div]').append($('#templates [data-id=info]').clone());
            $('#total-contract-sum-modal [data-id=info-div]').append($('#templates [data-id=info]').clone());
            var tenderersTable = new Tabulator('#tenderers-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('projects.openTender.report.list', [$project->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen: true},
                    {title:"{{ trans('companies.name') }}", field:"name", minWidth: 300, hozAlign:'left', headerFilter: true, headerSort:false, frozen: true},
                    {title:"{{ trans('tenders.submittedDate') }}", field:"submitted_date", width: 150, cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: {
                            opaque: function(cell){
                                return cell.getData()['is_submitted'];
                            },
                            tag: 'span',
                            attributes: {'class': 'label label-info'},
                            innerHtml: function(rowData){
                                return rowData['submitted_date'];
                            }
                        }
                    }},
                    {title:"{{ trans('tenders.amount') }} ({{{ $project->modified_currency_code }}})", field:"amount", width:150, cssClass:"text-right text-middle", headerSort:false},
                    {title:"{{ trans('tenders.withdrawnTenders') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-id':'id'},
                                    attributes: {'class': 'btn btn-xs btn-success', 'data-action':'show-withdrawn-tenders'},
                                    innerHtml: function(rowData){
                                        return rowData['count:withdrawn_tenders'];
                                    }
                                }
                            }
                        ]
                    }},
                    {title:"{{ trans('tenders.participatedTenders') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-id':'id'},
                                    attributes: {'class': 'btn btn-xs btn-success', 'data-action':'show-participated-tenders'},
                                    innerHtml: function(rowData){
                                        return rowData['count:participated_tenders'];
                                    }
                                }
                            }
                        ]
                    }},
                    {title:"{{ trans('tenders.ongoingProjects') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-id':'id'},
                                    attributes: {'class': 'btn btn-xs btn-success', 'data-action':'show-ongoing-projects'},
                                    innerHtml: function(rowData){
                                        return rowData['count:ongoing_projects'];
                                    }
                                }
                            }
                        ]
                    }},
                    {title:"{{ trans('tenders.completedProjects') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-id':'id'},
                                    attributes: {'class': 'btn btn-xs btn-success', 'data-action':'show-completed-projects'},
                                    innerHtml: function(rowData){
                                        return rowData['count:completed_projects'];
                                    }
                                }
                            }
                        ]
                    }}
                ],
            });

            var withdrawnTendersTable = new Tabulator('#withdrawn-tenders-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:'left', headerFilter: true, headerSort:false},
                ],
            });

            var participatedTendersTable = new Tabulator('#participated-tenders-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:'left', headerFilter: true, headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"amount", width: 150, cssClass:"text-right text-top", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency_code", width: 100, cssClass:"text-center text-top", headerSort:false, headerFilter:true},
                    {title:"{{ trans('tenders.closingDate') }}", field:"closing_date", width: 150, cssClass:"text-center text-top", headerSort:false},
                ],
            });

            var ongoingProjectsTable = new Tabulator('#ongoing-projects-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:'left', headerFilter: true, headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"amount", width: 150, cssClass:"text-right text-top", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency_code", width: 100, cssClass:"text-center text-top", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.commencementDate') }}", field:"commencement_date", width: 150, cssClass:"text-center text-top", headerSort:false},
                ],
            });

            var completedProjectsTable = new Tabulator('#completed-projects-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:'left', headerFilter: true, headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"amount", width: 150, cssClass:"text-right text-top", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency_code", width: 100, cssClass:"text-center text-top", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.completionDate') }}", field:"completion_date", width: 150, cssClass:"text-center text-top", headerSort:false},
                ],
            });

            var totalContractSumTable = new Tabulator('#total-contract-sum-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"amount", minWidth: 150, cssClass:"text-right text-top", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency_code", width: 100, cssClass:"text-center text-top", headerSort:false, headerFilter:true},
                ],
            });

            $('#tenderers-table').on('click', '[data-action=show-withdrawn-tenders]', function(){
                var rowData = tenderersTable.getRow($(this).data('id')).getData();
                withdrawnTendersTable.setData(rowData['route:withdrawn_tenders']);
                $('#withdrawn-tenders-modal [data-id=info]').html(rowData['name']);
                modalStack.push('#withdrawn-tenders-modal');
            });

            $('#tenderers-table').on('click', '[data-action=show-participated-tenders]', function(){
                var rowData = tenderersTable.getRow($(this).data('id')).getData();
                participatedTendersTable.setData(rowData['route:participated_tenders']);
                $('#participated-tenders-modal [data-id=info]').html(rowData['name']);
                modalStack.push('#participated-tenders-modal');
            });

            $('#tenderers-table').on('click', '[data-action=show-ongoing-projects]', function(){
                var rowData = tenderersTable.getRow($(this).data('id')).getData();
                ongoingProjectsTable.setData(rowData['route:ongoing_projects']);
                totalContractSumTable.setData(rowData['route:ongoing_projects_contract_sums']);
                $('#ongoing-projects-modal [data-id=info]').html(rowData['name']);
                $('#total-contract-sum-modal [data-id=info]').html(rowData['name']);
                modalStack.push('#ongoing-projects-modal');
            });

            $('#tenderers-table').on('click', '[data-action=show-completed-projects]', function(){
                var rowData = tenderersTable.getRow($(this).data('id')).getData();
                completedProjectsTable.setData(rowData['route:completed_projects']);
                totalContractSumTable.setData(rowData['route:completed_projects_contract_sums']);
                $('#completed-projects-modal [data-id=info]').html(rowData['name']);
                $('#total-contract-sum-modal [data-id=info]').html(rowData['name']);
                modalStack.push('#completed-projects-modal');
            });

            $('#ongoing-projects-modal,#completed-projects-modal').on('click', '[data-action=info]', function(){
                modalStack.push('#total-contract-sum-modal');
            });
        });
    </script>
@endsection