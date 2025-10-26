
<div id="developer_dashboard_widget-subsidiaries" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" id="developer_dashboard-subsidiaries-list" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-list"></i> </span>
                <h2 class="hidden-mobile">List of Subsidiaries</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="row" style="padding:6px;">
                        <article class="col col-xs-12">
                            <button id="subsidiaries-dashboard-generate" class="btn btn-sm btn-primary pull-right"><i class="fa fa-pencil-ruler"></i> {{trans('projects.generate')}}</button>
                        </article>
                    </div>
                    <div class="row no-space">
                        <div id="subsidairies-list-table" class="tabulator-no-border"></div>
                    </div>
                </div>
            </div>
        </div>
    </article>
</div>

@if($subsidiaryProjectCount)
<div id="developer_dashboard_widget-B" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" id="developer_dashboard-B-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-chart-bar"></i> </span>
                <h2 class="hidden-mobile">Total Budget vs Awarded Contract Sum & Variation Orders By Subsidiaries</h2>
            </header>

            <div>
                <div class="widget-body no-padding">
                    <div class="row no-space">
                        <div id="dashboard-B-table" class="tabulator-no-border"></div>
                    </div>

                    <div id="bar-chart-B-legend"></div>    
                    <div id="bar-chart-B" class="chart"></div>
                </div>
            </div>
        </div>
    </article>
</div>

@if($workCategories)
<div id="developer_dashboard_widget-C" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" id="developer_dashboard-C-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-chart-bar"></i> </span>
                <h2 class="hidden-mobile">Total Saving/Overrun According To Work Categories By Subsidiaries</h2>
            </header>

            <div>
                <div class="widget-body no-padding">
                    <div class="row no-space">
                        <div id="work_categories-info-table" class="tabulator-no-border"></div>
                    </div>
                    <div id ="developer_dashboard_widget_content-C" style="padding-top:6px;"></div>
                </div>
            </div>
        </div>
    </article>
</div>

<div id="developer_dashboard_widget-D" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" id="developer_dashboard-D-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                <h2 class="hidden-mobile">Total Budget vs Awarded Contract Sum & Variation Orders According to Work Categories by Subsidiaries</h2>
            </header>

            <div>
                <div class="widget-body no-padding">
                    <div id ="developer_dashboard_widget_content-D" style="padding-top:6px;"></div>
                </div>
            </div>
        </div>
    </article>
</div>
@endif

<div id="developer_dashboard_widget-E" class="row">
    <article class="col col-xs-12">
        <div class="jarviswidget" id="developer_dashboard-E-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-file-invoice-dollar"></i> </span>
                <h2 class="hidden-mobile">Total Certified Payment by Subsidiaries</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="chart-E-toggle" class="col col-xs-12" style="margin-top:8px;"></div>
                    <div id="developer_dashboard_widget_content-E"></div>
                </div>
            </div>
        </div>
    </article>
</div>

@endif