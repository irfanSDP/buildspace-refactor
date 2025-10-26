@extends('layout.main')

@section('css')
	@include('dashboard.e_bidding.style')
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('dashboard.ebidding', trans('eBiddingDashboard.title')) }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-chart-line"></i> {{ trans('eBiddingDashboard.title') }}
			</h1>
		</div>
	</div>

    <div class="well mb-5">
        <div class="row">
            <div class="col col-xs-12 col-md-4 col-lg-3">
                @include('dashboard.e_bidding.partials.filters.bid_mode')
            </div>
        </div>
        <div class="row mt-5">
            <div class="col col-xs-12 col-md-6 col-lg-6">
                <div class="mb-1">
                    @include('dashboard.e_bidding.partials.filters.subsidiaries')
                </div>
                @include('dashboard.e_bidding.partials.filters.subsidiaries_btn')
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-xs-12 col-sm-6 col-md-6 col-lg-6">
            @include('dashboard.e_bidding.partials.chart_container', [
                'chartId' => 'totals_bar',
                'chartType' => 'graph-chart',
                'chartWidget' => false,
                'chartTitle' => trans('eBiddingStats.eBiddingTotals')
            ])
        </div>
        <div class="col col-xs-12 col-sm-6 col-md-6 col-lg-6">
            @include('dashboard.e_bidding.partials.chart_container', [
                'chartId' => 'totals_tender_amount',
                'chartType' => 'counter-chart',
                'chartWidget' => false,
                'chartTitle' => trans('eBiddingStats.tenderAmount'),
                'chartColour' => 'blue'
            ])
            @include('dashboard.e_bidding.partials.chart_container', [
                'chartId' => 'leading_bid_amount',
                'chartType' => 'counter-chart',
                'chartWidget' => false,
                'chartTitle' => trans('eBiddingStats.leadingBidAmount'),
                'chartColour' => 'orange'
            ])
            @include('dashboard.e_bidding.partials.chart_container', [
                'chartId' => 'tender_amount_diff',
                'chartType' => 'counter-chart',
                'chartWidget' => false,
                'chartTitle' => trans('eBiddingStats.tenderDiff'),
                'chartColour' => 'red'
            ])
            @include('dashboard.e_bidding.partials.chart_container', [
                'chartId' => 'budget_amount_diff',
                'chartType' => 'counter-chart',
                'chartWidget' => false,
                'chartTitle' => trans('eBiddingStats.tenderDiff'),
                'chartColour' => 'green'
            ])
        </div>
    </div>
    @include('dashboard.e_bidding.partials.chart_container', [
        'chartId' => 'projects',
        'chartType' => 'table-chart',
        'chartWidget' => true,
        'chartTitle' => trans('eBiddingStats.eBiddingProjects')
    ])
@endsection

@section('js')
	<script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>
	@include('dashboard.e_bidding.script')
@endsection