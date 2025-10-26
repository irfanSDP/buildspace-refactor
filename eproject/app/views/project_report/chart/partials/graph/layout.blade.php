<div class="widget-body">
    <section>
        <div class="row">
            <div class="col col-xs-12">
                <div style="padding: 10px 0;">
                    <div class="pull-right">
                        @include('project_report.chart.partials.filters.subsidiaries_btn')
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col col-xs-12 col-sm-4 col-md-4 col-lg-4" style="margin-bottom:8px;">
                @include('project_report.chart.partials.filters.grouping')
            </div>
            <div class="col col-xs-12 col-sm-8 col-md-8 col-lg-8" style="margin-bottom:8px;">
                @include('project_report.chart.partials.filters.subsidiaries')
            </div>
        </div>
    </section>
    <section>
        @include('project_report.chart.partials.loading_spinner')
        @include('project_report.chart.partials.chart')
    </section>
</div>