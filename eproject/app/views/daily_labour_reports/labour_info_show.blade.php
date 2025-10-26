<div class="table-responsive" style="overflow:hidden;">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>
                    &nbsp;
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
                <?php
                    $labourRate = PCK\DailyLabourReports\DailyLabourReportLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_SKILL,$dailyLabourReport);
                ?>
                <td>
                    {{{$labourRate->normal_workers_total}}}
                </td>
                <td>
                    {{{$labourRate->ot_workers_total}}}
                </td>
                <td>
                    {{{$labourRate->ot_hours_total}}}
                </td>
            </tr>
            <tr>
                <td  style="width:30%;">
                   <strong><label for="semi_skill">{{{ trans('dailyLabourReports.semi_skill') }}}</label></strong>
                </td>
                <?php
                    $labourRate = PCK\DailyLabourReports\DailyLabourReportLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_SEMI_SKILL,$dailyLabourReport);
                ?>
                <td>
                    {{{$labourRate->normal_workers_total}}}
                </td>
                <td>
                    {{{$labourRate->ot_workers_total}}}
                </td>
                <td>
                    {{{$labourRate->ot_hours_total}}}
                </td>
            </tr>
            <tr>
                <td  style="width:30%;">
                    <strong><label for="labour">{{{ trans('dailyLabourReports.unskill') }}}</label></strong>
                </td>
                <?php
                    $labourRate = PCK\DailyLabourReports\DailyLabourReportLabourRate::getLabourRatesRecords(PCK\DailyLabourReports\ProjectLabourRate::LABOUR_TYPE_LABOUR,$dailyLabourReport);
                ?>
                <td>
                    {{{$labourRate->normal_workers_total}}}
                </td>
                <td>
                    {{{$labourRate->ot_workers_total}}}
                </td>
                <td>
                    {{{$labourRate->ot_hours_total}}}
                </td>
            </tr>
        </tbody>
    </table>
</div>
