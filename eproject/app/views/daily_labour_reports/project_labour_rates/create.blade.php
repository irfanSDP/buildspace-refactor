@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.skip.postContract.confirmation', trans('projects.skipToPostContract') , array($project->id)) }}</li>
        <li>{{{ trans('dailyLabourReports.project_labour_rates') }}}</li>
    </ol>

@endsection

@section('content')

<style>
    input[type=number]{
        text-align: right;
    }
</style>

<?php
    $workingHours = PCK\DailyLabourReports\ProjectLabourRate::where("project_id", $project->id)->pluck("normal_working_hours");
    $submittedUserId = PCK\DailyLabourReports\ProjectLabourRate::where("project_id", $project->id)->pluck("submitted_by");
?>

{{ Form::open(array('route' => array('storeLabourRates', $project->id, $contractorId))) }}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget well">
                <div class="widget-body">
                    <div class="table-responsive" style="overflow:hidden;">
                        <table class="table table-hover">
                            <tbody>
                                <tr>
                                    <td>
                                        <label for="labour"><strong>{{{ trans('dailyLabourReports.normal_working_hours') }}}</strong></label>
                                    </td>
                                    <td>
                                        <input type="number" name="normal_working_hours" value="{{{$workingHours}}}">&nbsp;&nbsp;
                                        <strong>{{{ trans('dailyLabourReports.hours_per_day') }}}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <?php
                                        $submittedUser = PCK\Users\User::find($submittedUserId);
                                    ?>
                                    <td>
                                        <label for="labour"><strong>{{{ trans('dailyLabourReports.last_updated') }}}</strong></label>
                                    </td>
                                    <td>
                                        <strong>
                                            @if(isset($submittedUserId))
                                                {{{$submittedUser->name}}}
                                            @else
                                                {{{ trans('dailyLabourReports.none') }}}
                                            @endif
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <strong>{{{ trans('dailyLabourReports.labour') }}}</strong>
                                    </th>
                                    <th>
                                        <strong>{{{ trans('dailyLabourReports.normal_rate_per_hour') }}}</strong>
                                    </th>
                                    <th>
                                        <strong>{{{ trans('dailyLabourReports.ot_rate_per_hour') }}}</strong>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="width:30%;">
                                        <label for="skill">{{{ trans('dailyLabourReports.skill') }}}</label>
                                    </td>
                                    @if($labourRate = PCK\DailyLabourReports\ProjectLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_SKILL,$project))
                                        <td>
                                            <input type="number" step="0.01" name="normal_rate_per_hour_1" value="{{{$labourRate->normal_rate_per_hour}}}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="ot_rate_per_hour_1" value="{{{$labourRate->ot_rate_per_hour}}}">
                                        </td>
                                    @endif
                                </tr>
                                <tr>
                                    <td style="width:30%;">
                                        <label for="semi_skill">{{{ trans('dailyLabourReports.semi_skill') }}}</label>
                                    </td>
                                    @if($labourRate = PCK\DailyLabourReports\ProjectLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_SEMI_SKILL,$project))
                                        <td>
                                            <input type="number" step="0.01" name="normal_rate_per_hour_2" value="{{{$labourRate->normal_rate_per_hour}}}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="ot_rate_per_hour_2" value="{{{$labourRate->ot_rate_per_hour}}}">
                                        </td>
                                    @endif
                                </tr>
                                <tr>
                                    <td style="width:30%;">
                                        <label for="labour">{{{ trans('dailyLabourReports.labour') }}}</label>
                                    </td>
                                    @if($labourRate = PCK\DailyLabourReports\ProjectLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_LABOUR,$project))
                                        <td>
                                            <input type="number" step="0.01" name="normal_rate_per_hour_3" value="{{{$labourRate->normal_rate_per_hour}}}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="ot_rate_per_hour_3" value="{{{$labourRate->ot_rate_per_hour}}}">
                                        </td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                        <footer align="right">
                            <div>
                                <button class="btn btn-success btn-md header-btn" type="submit">
                                    {{{ trans('forms.submit') }}}
                                </button>
                                <a href="{{ route('daily-labour-report.index',$project->id )}}">
                                    <div class="btn btn-info btn-md header-btn" >{{{ trans('forms.cancel') }}}</div>
                                </a>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{ Form::close() }}
    
@endsection

@section('js')
<script>

$(document).ready(function() {

    $('button[type=submit]').on('click', function(){
        app_progressBar.toggle();
        app_progressBar.maxOut();

    });

});
</script>
    
@endsection

   