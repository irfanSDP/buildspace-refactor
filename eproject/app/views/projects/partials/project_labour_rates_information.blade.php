<div class="row">
    <div class="col col-xs-12 col-md-12 col-lg-12">
        <div class="card border">
            <div class="card-header"><strong>Labour Rates</strong></div>
            <div class="card-body">
                <strong>Normal Working Hours</strong>: {{{ number_format($project->projectLabourRates->first()->normal_working_hours) }}} (hour)
                <br/>
                <br/>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{{ trans('dailyLabourReports.labour') }}}</th>
                            <th class="text-right text-nowrap">
                                {{{ trans('dailyLabourReports.normal_rate_per_hour') }}}
                                (
                                    @if(empty($project->modified_currency_code))
                                        {{{ mb_strtoupper($project->country->currency_code) }}}
                                    @else
                                        {{{ mb_strtoupper($project->modified_currency_code) }}}
                                    @endif
                                )
                            </th>
                            <th class="text-right text-nowrap">{{{ trans('dailyLabourReports.ot_rate_per_hour') }}}
                            (
                                @if(empty($project->modified_currency_code))
                                    {{{ mb_strtoupper($project->country->currency_code) }}}
                                @else
                                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                                @endif
                            )</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($project->projectLabourRates as $rates)
                        <tr>
                            <td><strong>{{{ $rates->getTypeName() }}}</strong></td>
                            <td class="text-right text-nowrap">{{{ number_format($rates->normal_rate_per_hour, 2, '.', ',') }}}</td>
                            <td class="text-right text-nowrap">{{{ number_format($rates->ot_rate_per_hour, 2, '.', ',') }}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>