@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.projectTrackRecord') }}}</li>
    </ol>
@endsection

@section('css')
<style>
    .spaced {
        margin-right: 5px;
    }
</style>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorManagement.projectTrackRecord') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('vendors.vendorRegistration.projectTrackRecord.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            @if(!empty($instructionSettings->project_track_record))
            <div class="padded label-success text-white"><strong>{{ nl2br($instructionSettings->project_track_record) }}</strong></div>
            <br>
            @endif
            @if(!empty($section->amendment_remarks))
            <div class="well @if($section->amendmentsRequired()) border-danger @elseif($section->amendmentsMade()) border-warning @endif">
                {{ nl2br($section->amendment_remarks) }}
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.completedProjects') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="completed-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.currentProjects') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="current-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget padded">
                <footer>
                    @include('vendor_management.partials.link_to_next_registration_section', ['currentSection' => 'projectTrackRecord'])
                    {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default pull-right spaced')) }}
                </footer>
            </div>
        </div>
    </div>
</div>
@include('uploads.downloadModal')
@endsection

@section('js')
    <script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            new Tabulator('#completed-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($completedProjectsData) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"propertyDeveloper", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendorCategory", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendorSubWorkCategory", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money"},
                    {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.qlassicOrConquasScore') }}", field:"has_qlassic_or_conquas_score", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick'},
                    {title:"{{ trans('vendorManagement.qlassicScore') }}", field:"qlassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.qlassicYearOfAchievement') }}", field:"qlassic_year_of_achievement", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.conquasScore') }}", field:"conquas_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.conquasYearOfAchievement') }}", field:"conquas_year_of_achievement", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.awardsReceived') }}", field:"awards_received", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.yearOfAwardsReceived') }}", field:"year_of_recognition_awards", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.shassicScore') }}", field:"shassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 300, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle"},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                innerHtml: function(rowData) {
                                    return '<a href="'+rowData['route:edit']+'" class="btn btn-xs btn-warning" title="{{ trans('vendorPreQualification.updateItem') }}"><i class="fa fa-edit"></i></a>';
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                innerHtml: function(rowData) {
                                    return '<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#downloadModal" data-action="get-downloads" data-get-downloads="' + rowData['route:getDownloads'] + '"><i class="fa fa-paperclip"></i> (' + rowData['attachments_count'] + ')</button>';
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                innerHtml: function(rowData){
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }
                            },
                        ]
                    }}
                ],
            });

            new Tabulator('#current-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($currentProjectsData) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"propertyDeveloper", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendorCategory", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendorSubWorkCategory", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money"},
                    {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 300, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle"},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                innerHtml: function(rowData) {
                                    return '<a href="'+rowData['route:edit']+'" class="btn btn-xs btn-warning" title="{{ trans('vendorPreQualification.updateItem') }}"><i class="fa fa-edit"></i></a>';
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                innerHtml: function(rowData) {
                                    return '<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#downloadModal" data-action="get-downloads" data-get-downloads="' + rowData['route:getDownloads'] + '"><i class="fa fa-paperclip"></i> (' + rowData['attachments_count'] + ')</button>';
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                innerHtml: function(rowData){
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }
                            },
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection