<div class="row">
    <div class="col col-xs-12">
        <div class="chart-container" id="chart_container_{{ $chartId }}" data-r="{{ $chartId }}">
            @if ($chartWidget)
                <article class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false" data-widget-fullscreen="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                        <h2 class="hidden-mobile">{{ $chartTitle }}</h2>
                    </header>
                    <div class="widget-body">
                        <div>
                            @include('dashboard.e_bidding.partials.loading_spinner', ['chartId' => $chartId])

                            @if($chartType === 'counter-chart')
                                @include('dashboard.e_bidding.partials.counter', ['chartId' => $chartId])
                            @else
                                @include('dashboard.e_bidding.partials.chart', ['chartId' => $chartId])
                            @endif
                        </div>
                    </div>
                </article>
            @else
                <div>
                    @include('dashboard.e_bidding.partials.loading_spinner', ['chartId' => $chartId])

                    @if($chartType === 'counter-chart')
                        @include('dashboard.e_bidding.partials.counter', ['chartId' => $chartId])
                    @else
                        @include('dashboard.e_bidding.partials.chart', ['chartId' => $chartId])
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>