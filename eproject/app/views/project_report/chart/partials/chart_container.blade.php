<div class="row chart-container" id="chart_container_{{ $record->id }}" data-r="{{ $record->id }}" data-l="{{ route('projectReport.charts.data', array($record->id)) }}">
    <article class="col col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false" data-widget-fullscreen="false">
            <header>
                <span class="widget-icon"> <i class="{{ $record->chart_icon }}"></i> </span>
                <h2 class="hidden-mobile">{{ $record->title }}</h2>
            </header>
            @include('project_report.chart.partials.'.$record->chart_type_label.'.layout')
        </div>
    </article>
</div>