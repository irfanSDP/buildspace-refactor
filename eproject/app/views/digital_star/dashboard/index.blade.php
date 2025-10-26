@extends('layout.main', array('hide_ribbon'=>true))

@section('css')
    <style>
        .tabulator .tabulator-header .tabulator-col .tabulator-col-content .tabulator-col-title {
            white-space: normal;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
        <h1 class="page-title txt-color-blueDark">
            <i class="fas fa-chart-line"></i>
            {{ trans('digitalStar/digitalStar.digitalStarDashboard') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('digitalStar/digitalStar.vendorStatistics') }}</h2>
            </header>

            @include('digital_star.dashboard.partials.view.vendors_by_ds_rating_chart')
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('digitalStar/digitalStar.digitalStarStatistics') }}</h2>
            </header>

            @include('digital_star.dashboard.partials.view.statistics')
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>
    <style>
        .chart_tooltip {
            position: relative;
            background: #555;
            border: 2px solid #000000;
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>
    <script>
        function roundToDecimal(num, places = 2) {
            return +(Math.round(num + ("e+" + places))  + ("e-" + places));
        }
    </script>
    @include('digital_star.dashboard.partials.javascript.vendors_by_ds_rating_chart_script');
    @include('digital_star.dashboard.partials.javascript.statistics_script');
@endsection