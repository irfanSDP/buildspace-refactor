<div class="table-responsive" style="overflow:hidden;">
    <table class="table table-hover">
        <?php
            $workingHours = PCK\DailyLabourReports\DailyLabourReportLabourRate::where("daily_labour_report_id", $dailyLabourReport->id)->pluck("normal_working_hours");
        ?>
        <thead>
            <tr>
                <th>
                    <label for="labour"><strong>{{{ trans('dailyLabourReports.normal_working_hours') }}}</strong></label>
                </th>
                <th>
                    <input type="number" name="normal_working_hours" value="{{{Input::old('normal_working_hours')??$workingHours}}}">&nbsp;&nbsp;
                    <strong>{{{ trans('dailyLabourReports.hours_per_day') }}}</strong>
                </th>
            </tr>
            <tr>
                <th>
                    &nbsp;
                </th>
                <th>
                    <strong>{{{ trans('dailyLabourReports.normal_rate_per_hour') }}}</strong>
                </th>
                <th>
                    <strong>{{{ trans('dailyLabourReports.ot_rate_per_hour') }}}</strong>
                </th>
                <th>
                    <strong>{{{ trans('dailyLabourReports.number_of_normal_time_workers') }}}</strong>
                </th>
                <th>
                    <strong>{{{ trans('dailyLabourReports.number_of_ot_workers') }}}</strong>
                </th>
                <th>
                    <strong>{{{ trans('dailyLabourReports.total_ot_hours') }}}</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width:30%;">
                    <strong><label for="skill">{{{ trans('dailyLabourReports.skill') }}}</label></strong>
                </td>
                @if($labourRate = PCK\DailyLabourReports\DailyLabourReportLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_SKILL,$dailyLabourReport))
                    <td>
                        <input type="number" step="0.01" name="normal_rate_per_hour_1" value="{{{Input::old('normal_rate_per_hour_1')??$labourRate->normal_rate}}}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="ot_rate_per_hour_1" value="{{{Input::old('ot_rate_per_hour_1')??$labourRate->ot_rate}}}">
                    </td>
                    <td>
                        <input type="number" name="number_of_workers_1" value="{{{Input::old('number_of_workers_1')??$labourRate->normal_workers_total}}}">
                    </td>
                    <td>
                        <input type="number" name="ot_number_of_workers_1" value="{{{Input::old('ot_number_of_workers_1')??$labourRate->ot_workers_total}}}">
                    </td>
                    <td>
                        <input type="number" name="ot_hours_1" value="{{{Input::old('ot_hours_1')??$labourRate->ot_hours_total}}}">
                    </td>
                 @endif
            </tr>
            <tr>
                <td  style="width:30%;">
                   <strong><label for="semi_skill">{{{ trans('dailyLabourReports.semi_skill') }}}</label></strong>
                </td>
                @if($labourRate = PCK\DailyLabourReports\DailyLabourReportLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_SEMI_SKILL,$dailyLabourReport))
                    <td>
                        <input type="number" step="0.01" name="normal_rate_per_hour_2" value="{{{Input::old('normal_rate_per_hour_2')??$labourRate->normal_rate}}}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="ot_rate_per_hour_2" value="{{{Input::old('ot_rate_per_hour_2')??$labourRate->ot_rate}}}">
                    </td>
                    <td>
                        <input type="number" name="number_of_workers_2" value="{{{Input::old('number_of_workers_2')??$labourRate->normal_workers_total}}}">
                    </td>
                    <td>
                        <input type="number" name="ot_number_of_workers_2" value="{{{Input::old('ot_number_of_workers_2')??$labourRate->ot_workers_total}}}">
                    </td>
                    <td>
                        <input type="number" name="ot_hours_2" value="{{{Input::old('ot_hours_2')??$labourRate->ot_hours_total}}}">
                    </td>
                @endif
            </tr>
            <tr>
                <td  style="width:30%;">
                    <strong><label for="labour">{{{ trans('dailyLabourReports.unskill') }}}</label></strong>
                </td>
                @if($labourRate = PCK\DailyLabourReports\DailyLabourReportLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_LABOUR,$dailyLabourReport))
                    <td>
                        <input type="number" step="0.01" name="normal_rate_per_hour_3" value="{{{Input::old('normal_rate_per_hour_3')??$labourRate->normal_rate}}}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="ot_rate_per_hour_3" value="{{{Input::old('ot_rate_per_hour_3')??$labourRate->ot_rate}}}">
                    </td>
                    <td>
                        <input type="number" name="number_of_workers_3" value="{{{Input::old('number_of_workers_3')??$labourRate->normal_workers_total}}}">
                    </td>
                    <td>
                        <input type="number" name="ot_number_of_workers_3" value="{{{Input::old('ot_number_of_workers_3')??$labourRate->ot_workers_total}}}">
                    </td>
                    <td>
                        <input type="number" name="ot_hours_3" value="{{{Input::old('ot_hours_3')??$labourRate->ot_hours_total}}}">
                    </td>
                @endif
            </tr>
        </tbody>
    </table>
</div>
