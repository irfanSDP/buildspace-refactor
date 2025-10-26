<div class="widget-body">
    <section>
        <div class="row">
            <div class="col col-xs-12 col-sm-6 col-md-4">
                @include('project_report.chart.partials.loading_spinner')
                @include('project_report.chart.partials.chart')
            </div>
            <div class="col col-xs-12 col-sm-6 col-md-8">
                <div class="row">
                    <div class="col col-xs-12">
                        <div style="padding: 10px 0;">
                            <div class="pull-right">
                                @include('project_report.chart.partials.filters.subsidiaries_btn')
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    @if (! empty($record->filters['options']['year']) || (! empty($record->filters['options']['grouping']) && count($record->filters['options']['grouping']) > 1))
                        <div class="col col-xs-12 col-sm-5 col-md-5 col-lg-5" style="margin-bottom:8px;">
                            @include('project_report.chart.partials.filters.grouping')
                        </div>
                        <div class="col col-xs-12 col-sm-7 col-md-7 col-lg-7" style="margin-bottom:8px;">
                            @include('project_report.chart.partials.filters.subsidiaries')
                        </div>
                    @else
                        <div class="col col-xs-12">
                            @include('project_report.chart.partials.filters.subsidiaries')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>