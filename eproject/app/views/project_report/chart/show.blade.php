@extends('layout.main')

@section('css')
	@include('project_report.chart.partials.style')
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projectReport.charts.showAll', trans('projectReportChart.projectReportChart')) }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-line-chart"></i> {{ trans('projectReportChart.projectReportChart') }}
			</h1>
		</div>
	</div>
	@include('project_report.chart.partials.chart_container')
@endsection

@section('js')
	<script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>
	@include('project_report.chart.partials.script')
@endsection