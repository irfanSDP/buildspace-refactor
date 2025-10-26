@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('dailyLabourReports.daily_labour_reports') }}}</li>
    </ol>

@endsection

@section('content')

<style>
 th {
    text-align: center;
 }
</style>

<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <h1 class="page-title txt-color-blueDark">
            {{{ trans('dailyLabourReports.daily_labour_reports') }}}
        </h1>
    </div>
    @if($project->isMainProject())
         @if(PCK\SiteManagement\SiteManagementUserPermission::isEditor(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS, $user, $project))
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <a href="{{ route('daily-labour-report.create',$project->id )}}">
                    <button class="btn btn-primary btn-md pull-right header-btn">
                        <i class="fa fa-plus"></i> {{{ trans('dailyLabourReports.add_record') }}}
                    </button>
                </a>
            </div>
        @endif
    @endif
</div>
<br>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('dailyLabourReports.daily_labour_report_list') }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table  table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Weather" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Location" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Trade" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Company" />
                                    </th>
                                    <th colspan="3">&nbsp;</th>
                                    <th colspan="3">&nbsp;</th>
                                    <th colspan="3">&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Work Description" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Remark" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Submitted User" />
                                    </th>
                                </tr>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th colspan="3">{{{ trans('dailyLabourReports.skill') }}}</th>
                                    <th colspan="3">{{{ trans('dailyLabourReports.semi_skill') }}}</th>
                                    <th colspan="3">{{{ trans('dailyLabourReports.labour') }}}</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th>{{{ trans('dailyLabourReports.no') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.date_submitted') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.weather') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.location') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.trade') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.company') }}}</th>
                                    @if($labourTypes = \PCK\DailyLabourReports\ProjectLabourRate::getLabourTypes())
                                        @foreach($labourTypes as $labourType)
                                            <th>{{{ trans('dailyLabourReports.no') }}}</th>
                                            <th>{{{ trans('dailyLabourReports.ot_no') }}}</th>
                                            <th>{{{ trans('dailyLabourReports.ot_hours') }}}</th>
                                        @endforeach
                                    @endif
                                    <th>{{{ trans('dailyLabourReports.work_description') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.remark') }}}</th>
                                    <th>{{{ trans('dailyLabourReports.submitted_by') }}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($records as $record)
                                <?php
                                    $dailyLabourReportLabourRates = PCK\DailyLabourReports\DailyLabourReportLabourRate::where("daily_labour_report_id",$record->id)->orderBy("labour_type", "asc")->get();
                                ?>
                                    <tr>
                                        <td>
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           	{{{$project->getProjectTimeZoneTime($record->created_at)}}}
                                        </td>
                                        <td>
                                            {{{$record->weather->name}}}
                                        </td>
                                        <td>
                                            {{{$record->projectStructureLocationCode->description}}}
                                        </td>
                                        <td>
                                            {{{$record->preDefinedLocationCode->name}}}
                                        </td>
                                        <td>
                                            <a href="{{ route('daily-labour-report.show', 
                                                           array($project->id,$record->id))}}">
                                                {{{$record->contractorCompany->name}}}
                                            </a>
                                        </td>
                                        @foreach($dailyLabourReportLabourRates as $labourRate)
                                            <td>
                                                {{{$labourRate->normal_workers_total}}}
                                            </td>
                                            <td>
                                                {{{$labourRate->ot_workers_total}}}
                                            </td>
                                            <td>
                                                {{{$labourRate->ot_hours_total}}}
                                            </td>
                                        @endforeach
                                        <td>
                                            {{{$record->work_description}}}
                                        </td>
                                        <td>
                                            {{{$record->remark}}}
                                        </td>
                                        <td>
                                            {{{$record->submittedUser->name}}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            var otable = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : true,
                "scrollCollapse": true,
                "scrollX": true
            });

            $("thead th input[type=text]").on( 'keyup change', function () {
                otable.column( $(this).parent().index()+':visible' ).search( this.value ).draw();
            });
        });
    </script>
    
@endsection