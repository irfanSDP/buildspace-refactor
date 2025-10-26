<?php
    $widgets = [
        'procurement_method' => [
            'header_title' => 'Summary of Procurement Method For Projects',
            'chart_title'  => 'Procurement Methods'
        ],
        'project_status' => [
            'header_title' => 'Summary of Project Status',
            'chart_title'  => 'Project Status'
        ],
        'e_tender_waiver_status' => [
            'header_title' => 'Summary of E-Tender Waiver Status',
            'chart_title'  => 'E-Tender Waiver Status'
        ],
        'e_auction_waiver_status' => [
            'header_title' => 'Summary of E-Auction Waiver Status',
            'chart_title'  => 'E-Auction Waiver Status'
        ],
    ];
?>
@foreach($widgets as $id => $data)
<div id="status_summary_widget-{{$id}}" class="row">
    <article class="col-xs-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-list"></i> </span>
                <h2 class="hidden-mobile">{{{$data['header_title']}}}</h2>
            </header>

            <div>
                <div class="widget-body no-padding">
                    <div id="status_summary_widget-content-{{$id}}" class="row no-space">
                        <article class="col col-xs-12 col-sm-12 col-md-8 col-lg-8">
                            <div id="dashboard-{{$id}}-table" class="tabulator-no-border"></div>
                        </article>
                        <article class="col col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <div class="text-middle text-center">
                                <h5>{{{$data['chart_title']}}}</h5>
                                <div id="{{$id}}-pie-chart"></div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </article>
</div>
@endforeach