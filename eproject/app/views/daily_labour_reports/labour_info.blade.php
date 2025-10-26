<style>

.project_labour_rates{
    display: none;
}

</style>
<div class="table-responsive" style="overflow:hidden;">
    <table class="table table-hover" style="display:none">
        <tbody>
            <tr>
                <td>
                    <label for="labour"><strong>{{{ trans('dailyLabourReports.normal_working_hours') }}}</strong></label>
                </td>
                <td>
                    <input type="number" id="normal_working_hours" name="normal_working_hours" value="{{{Input::old('normal_working_hours')??0}}}" readonly>&nbsp;&nbsp;
                    <strong>{{{ trans('dailyLabourReports.hours_per_day') }}}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    <br><br>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>
                    &nbsp;
                </th>
                <th class="project_labour_rates">
                    <strong>{{{ trans('dailyLabourReports.normal_rate_per_hour') }}}</strong>
                </th>
                <th class="project_labour_rates">
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
                
                <td class="project_labour_rates">
                    <input type="number" step="0.01" id= "normal_rate_per_hour_1" name="normal_rate_per_hour_1" value="{{{Input::old('normal_rate_per_hour_1')??0}}}" readonly>
                </td>
                <td class="project_labour_rates">
                    <input type="number" step="0.01" id="ot_rate_per_hour_1" name="ot_rate_per_hour_1" value="{{{Input::old('ot_rate_per_hour_1')??0}}}" readonly>
                </td>
  
                <td>
                    <input type="number" name="number_of_workers_1" value="{{{Input::old('number_of_workers_1')??0}}}" required>
                </td>
                <td>
                    <input type="number" name="ot_number_of_workers_1" value="{{{Input::old('ot_number_of_workers_1')??0}}}" required>
                </td>
                <td>
                    <input type="number" name="ot_hours_1" value="{{{Input::old('ot_hours_1')??0}}}" required>
                </td>
            </tr>
            <tr>
                <td style="width:30%;">
                   <strong><label for="semi_skill">{{{ trans('dailyLabourReports.semi_skill') }}}</label></strong>
                </td>
                
                <td class="project_labour_rates">
                    <input type="number" step="0.01" id="normal_rate_per_hour_2" name="normal_rate_per_hour_2" value="{{{Input::old('normal_rate_per_hour_2')??0}}}" readonly>
                </td>
                <td class="project_labour_rates">
                    <input type="number" step="0.01" id="ot_rate_per_hour_2" name="ot_rate_per_hour_2" value="{{{Input::old('ot_rate_per_hour_2')??0}}}" readonly>
                </td>

                <td>
                    <input type="number" name="number_of_workers_2" value="{{{Input::old('number_of_workers_2')??0}}}" required>
                </td>
                <td>
                    <input type="number" name="ot_number_of_workers_2" value="{{{Input::old('ot_number_of_workers_2')??0}}}" required>
                </td>
                <td>
                    <input type="number" name="ot_hours_2" value="{{{Input::old('ot_hours_2')??0}}}" required>
                </td>
            </tr>
            <tr>
                <td  style="width:30%;">
                    <strong><label for="labour">{{{ trans('dailyLabourReports.unskill') }}}</label></strong>
                </td>
                
                <td class="project_labour_rates">
                    <input type="number" step="0.01" id="normal_rate_per_hour_3" name="normal_rate_per_hour_3" value="{{{Input::old('normal_rate_per_hour_3')??0}}}" readonly>
                </td>
                <td class="project_labour_rates">
                    <input type="number" step="0.01" id="ot_rate_per_hour_3" name="ot_rate_per_hour_3" value="{{{Input::old('ot_rate_per_hour_3')??0}}}" readonly>
                </td>

                <td>
                    <input type="number" name="number_of_workers_3" value="{{{Input::old('number_of_workers_3')??0}}}" required>
                </td>
                <td>
                    <input type="number" name="ot_number_of_workers_3" value="{{{Input::old('ot_number_of_workers_3')??0}}}" required>
                </td>
                <td>
                    <input type="number" name="ot_hours_3" value="{{{Input::old('ot_hours_3')??0}}}" required>
                </td>
            </tr>
        </tbody>
    </table>
</div>
