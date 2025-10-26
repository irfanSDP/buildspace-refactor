<?php use PCK\Dashboard\DashboardGroup; ?>
<?php $selectedCountryId = ($selectedCountry) ? $selectedCountry->id : -1?>
@extends('layout.main', array('hide_ribbon'=>true))

@section('css')
<style>
    .select2-container {
        min-width: 60px!important;
    }
</style>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-chart-line"></i>
            {{{ $user->dashboardGroup()->getName() }}}
        </h1>
    </div>
    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
        <button id="dashboard_filter-btn" class="btn btn-info btn-md pull-right header-btn"><i class="fa fa-filter"></i> {{ trans('general.filter') }}</button>
    </div>
</div>

{{ Form::open(array('id'=>'dashboard_country-form', 'class'=>'smart-form', 'route' => $formRoute, 'method' => 'POST')) }}
<div class="row" style="margin:0;margin-top:8px;">
    <article class="col col-xs-4 col-sm-4 col-md-4 col-lg-4" style="margin-bottom:8px;">
        <div class="well">
            <h5>From Awarded Date</h5>
            <div class="row">
                <section class="col col-6">
                    <label class="label">Month:</label>
                    <label class="fill-horizontal">
                        <select class="select2 fill-horizontal" name="fm" id="dashboard_from_month-select">
                            @foreach($months as $k => $month)
                            <option value="{{$k}}" @if($k == $selectedFromMonth) selected @endif>{{{$month}}}</option>
                            @endforeach
                        </select>
                    </label>
                </section>

                <section class="col col-6">
                    <label class="label">Year:</label>
                    <label class="fill-horizontal">
                        <select class="select2 fill-horizontal" name="fy" id="dashboard_from_year-select">
                            @foreach($years as $k => $year)
                            <option value="{{$k}}" @if($k == $selectedFromYear) selected @endif>{{{$year}}}</option>
                            @endforeach
                        </select>
                    </label>
                </section>
            </div>
        </div>
    </article>
    
    <article class="col col-xs-4 col-sm-4 col-md-4 col-lg-4" style="margin-bottom:8px;">
        <div class="well">
            <h5>To Awarded Date</h5>
            <div class="row">
                <section class="col col-6">
                    <label class="label">Month:</label>
                    <label class="fill-horizontal">
                        <select class="input-sm select2 fill-horizontal" name="tm" id="dashboard_to_month-select">
                            @foreach($months as $k => $month)
                            <option value="{{$k}}" @if($k == $selectedToMonth) selected @endif>{{{$month}}}</option>
                            @endforeach
                        </select>
                    </label>
                </section>

                <section class="col col-6">
                    <label class="label">Year:</label>
                    <label class="fill-horizontal">
                        <select class="input-sm select2 fill-horizontal" name="ty" id="dashboard_to_year-select">
                            @foreach($years as $k => $year)
                            <option value="{{$k}}" @if($k == $selectedToYear) selected @endif>{{{$year}}}</option>
                            @endforeach
                        </select>
                    </label>
                </section>
            </div>
        </div>
    </article>

    <article class="col col-xs-4 col-sm-4 col-md-4 col-lg-4" style="margin-bottom:8px;">
        <div class="well">
            <h5>&nbsp;</h5>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{trans('companies.country')}}}:</label>
                    <label class="fill-horizontal">
                        <select class="input-sm select2 fill-horizontal" name="cid" id="dashboard_country-select">
                            @foreach($countries as $country)
                            <option value="{{$country->id}}" @if($country->id == $selectedCountryId) selected @endif>{{{$country->country}}}</option>
                            @endforeach
                        </select>
                    </label>
                </section>
            </div>
        </div>
    </article>
</div>
{{ Form::close() }}

@if($selectedCountry)

    @if($user->dashboardGroup()->type==DashboardGroup::TYPE_DEVELOPER)
        @if($dashboardType == 'subsidiaries')
            @include('dashboard.partials.developer.subsidiaries.view')
        @elseif($dashboardType == 'statusSummary')
            @include('dashboard.partials.developer.status_summary.view')
        @else
            @include('dashboard.partials.developer.overview.view')
        @endif
    @elseif($user->dashboardGroup()->type==DashboardGroup::TYPE_MAIN_CONTRACTOR)
        @if($dashboardType == 'overview')
            @include('dashboard.partials.main_contractor.view')
        @endif
    @endif

@else

<div class="row">
    <article class="col-sm-12">
        <div class="alert alert-warning text-center fade in">
            <i class="fa-fw fa fa-exclamation-triangle"></i>
            There is <strong>no data</strong> to be displayed.
        </div>
    </article>
</div>

@endif

@endsection

@section('js')

@if($selectedCountry)
<script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>

<!-- new flot lib -->
<!-- <script src="{{ asset('js/plugin/flot-new/jquery.flot.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.colorhelper.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.canvas.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.categories.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.crosshair.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.errorbars.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.fillbetween.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.image.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.navigate.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.pie.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.selection.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.symbol.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.threshold.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.time.js') }}"></script> -->


<script src="{{ asset('js/plugin/flot/jquery.flot.cust.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.resize.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.fillbetween.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.orderBar.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.pie.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.stack.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.time.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.barnumbers.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.categories.min.js') }}"></script>

@if($user->dashboardGroup()->type==DashboardGroup::TYPE_DEVELOPER)
    @if($dashboardType == 'subsidiaries')
        @include('dashboard.partials.developer.subsidiaries.javascript')
    @elseif($dashboardType == 'statusSummary')
        @include('dashboard.partials.developer.status_summary.javascript')
    @else
        @include('dashboard.partials.developer.overview.javascript')
    @endif
@elseif($user->dashboardGroup()->type==DashboardGroup::TYPE_MAIN_CONTRACTOR)
    @include('dashboard.partials.main_contractor.javascript')
@endif

<script type="text/javascript">
$(document).ready(function() {
    $('#dashboard_filter-btn').on('click', function (e) {
        $('#dashboard_country-form').submit();
    });
});
</script>
@endif

@endsection