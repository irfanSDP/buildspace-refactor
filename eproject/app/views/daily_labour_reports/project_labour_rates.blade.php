<fieldset>
<div class="row">
    <div class="col col-xs-12 col-md-12 col-lg-12">
        <div class="card border">
            <div class="card-header"><strong>Labour Rates</strong></div>
            <div class="card-body">
                <dl class="dl-horizontal no-margin">
                    <dt>
                        <label class="label">{{{ trans('dailyLabourReports.normal_working_hours') }}} <span class="required">*</span>:</label>
                    </dt>
                    <dd>
                        <label class="input">
                        {{ Form::number('normal_working_hours', Input::old('normal_working_hours', 0), ['id'=>'normal_working_hours', 'required' => 'required', 'style'=>'display:inline;width:80px;', 'autofocus' => 'autofocus']) }} <strong>{{{ trans('dailyLabourReports.hours_per_day') }}}</strong>
                        </label>
                    </dd>
                </dl>
                <br/>
                <br/>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                {{{ trans('dailyLabourReports.labour') }}}
                            </th>
                            <th>
                                {{{ trans('dailyLabourReports.normal_rate_per_hour') }}}
                                (
                                    @if(empty($project->modified_currency_code))
                                        {{{ mb_strtoupper($project->country->currency_code) }}}
                                    @else
                                        {{{ mb_strtoupper($project->modified_currency_code) }}}
                                    @endif
                                )
                            </th>
                            <th>
                                {{{ trans('dailyLabourReports.ot_rate_per_hour') }}}
                                (
                                    @if(empty($project->modified_currency_code))
                                        {{{ mb_strtoupper($project->country->currency_code) }}}
                                    @else
                                        {{{ mb_strtoupper($project->modified_currency_code) }}}
                                    @endif
                                )
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width:30%;">
                                <label class="label" for="skill">{{{ trans('dailyLabourReports.skill') }}}</label>
                            </td>
                            <td>
                                <label class="input">
                                {{ Form::number('normal_rate_per_hour_1', Input::old('normal_rate_per_hour_1', '0.00'), ['id'=>'normal_rate_per_hour_1', 'step'=>'0.01', 'required' => 'required', 'style'=>'display:inline;width:120px;', 'autofocus' => 'autofocus']) }}
                                </label>
                            </td>
                            <td>
                                <label class="input">
                                {{ Form::number('ot_rate_per_hour_1', Input::old('ot_rate_per_hour_1', '0.00'), ['id'=>'ot_rate_per_hour_1', 'step'=>'0.01', 'required' => 'required', 'style'=>'display:inline;width:120px;', 'autofocus' => 'autofocus']) }}
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:30%;">
                                <label  class="label" for="semi_skill">{{{ trans('dailyLabourReports.semi_skill') }}}</label>
                            </td>
                            <td>
                                <label class="input">
                                {{ Form::number('normal_rate_per_hour_2', Input::old('normal_rate_per_hour_2', '0.00'), ['id'=>'normal_rate_per_hour_2', 'step'=>'0.01', 'required' => 'required', 'style'=>'display:inline;width:120px;', 'autofocus' => 'autofocus']) }}
                                </label>
                            </td>
                            <td>
                                <label class="input">
                                {{ Form::number('ot_rate_per_hour_2', Input::old('ot_rate_per_hour_2', '0.00'), ['id'=>'ot_rate_per_hour_2', 'step'=>'0.01', 'required' => 'required', 'style'=>'display:inline;width:120px;', 'autofocus' => 'autofocus']) }}
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:30%;">
                                <label class="label" for="labour">{{{ trans('dailyLabourReports.labour') }}}</label>
                            </td>
                            <td>
                                <label class="input">
                                {{ Form::number('normal_rate_per_hour_3', Input::old('normal_rate_per_hour_3', '0.00'), ['id'=>'normal_rate_per_hour_3', 'step'=>'0.01', 'required' => 'required', 'style'=>'display:inline;width:120px;', 'autofocus' => 'autofocus']) }}
                                </label>
                            </td>
                            <td>
                                <label class="input">
                                {{ Form::number('ot_rate_per_hour_3', Input::old('ot_rate_per_hour_3', '0.00'), ['id'=>'ot_rate_per_hour_3', 'step'=>'0.01', 'required' => 'required', 'style'=>'display:inline;width:120px;', 'autofocus' => 'autofocus']) }}
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</fieldset>