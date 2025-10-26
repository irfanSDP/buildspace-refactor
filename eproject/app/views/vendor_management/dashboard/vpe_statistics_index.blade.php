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
            {{ trans('vendorManagement.vpeStatisticsDashboard') }}
        </h1>
    </div>
</div>

<div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
    <header>
        <span class="widget-icon"> <i class="fas fa-chart-pie"></i> </span>
        <h2>{{ trans('vendorManagement.vendorPerformanceStatistics') }}</h2>
    </header>
    <div>
        <div class="widget-body">
            <ul id="dashboardNav" class="nav nav-tabs bordered">
                <li class="active">
                    <a href="#overall-vendor-performance-statistics" data-toggle="tab">{{ trans('vendorManagement.overallVendorPerformanceStatistics') }}</a>
                </li>
                <li>
                    <a href="#vendor-group-statistics" data-toggle="tab">{{ trans('vendorManagement.overallVendorPerformanceEvaluationResults') }}</a>
                </li>
            </ul>
            <div id="dashboardContent" class="tab-content padding-10">
                <div class="tab-pane fade in active" id="overall-vendor-performance-statistics">
                    <header>{{ trans('vendorManagement.totalNumberOfProjectsEvaluated') }}</header>
                    <div class="smart-form">
                        <fieldset>
                            <div class="row">
                                <section class="col col-4">
                                    <label class="label" id="totalNumberOfProjectsEvaluated"></label>
                                </section>
                            </div>
                        </fieldset>
                    </div>
                    <hr>
                    <div class="smart-form">
                        <fieldset>
                            <div class="row">
                                <section class="col col-10">
                                    <label class="label">{{ trans('vendorManagement.totalAppraisalsAssignedToEachSubsidiary') }}</label>
                                </section>
                                @if($latestEvaluationCycle)
                                <section class="col col-2">
                                    <a href="{{ route('vendorManagement.dashboard.overallVendorPerformanceStatistics.excel.export') }}" target="_blank" class="btn btn-success pull-right"><i class="far fa-file-excel fa-lg"></i>&nbsp;&nbsp;{{ trans('general.export') }}</a>
                                </section>
                                @endif
                            </div>
                            <div class="row">
                                <section class="col col-10">
                                    <label class="label">{{ trans('vendorManagement.vendorCategoryScores') }}</label>
                                </section>
                                <section class="col col-2">
                                    <a href="{{ route('vendorManagement.dashboard.export.vendorPerformanceEvaluation.vendorCategoryScores') }}" target="_blank" class="btn btn-success pull-right @if(\PCK\VendorPerformanceEvaluation\Cycle::hasOngoingCycle()) disabled @endif"><i class="far fa-file-excel fa-lg"></i>&nbsp;&nbsp;{{ trans('general.export') }}</a>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-10">
                                    <label class="label">{{ trans('vendorManagement.vendorWorkCategoryScores') }}</label>
                                </section>
                                <section class="col col-2">
                                    <a href="{{ route('vendorManagement.dashboard.export.vendorPerformanceEvaluation.vendorWorkCategoryScores') }}" target="_blank" class="btn btn-success pull-right @if(\PCK\VendorPerformanceEvaluation\Cycle::hasOngoingCycle()) disabled @endif"><i class="far fa-file-excel fa-lg"></i>&nbsp;&nbsp;{{ trans('general.export') }}</a>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-10">
                                    <label class="label">{{ trans('vendorManagement.evaluationForms') }}</label>
                                </section>
                                <section class="col col-2">
                                    <div v-show="showVendorPerformanceEvaluationFormsDownload" style="display:none;">
                                        <a href="{{ route('vendorManagement.dashboard.export.vendorPerformanceEvaluation.forms') }}" target="_blank" class="btn btn-success pull-right @if(\PCK\VendorPerformanceEvaluation\Cycle::hasOngoingCycle()) disabled @endif"><i class="far fa-file-excel fa-lg"></i>&nbsp;&nbsp;{{ trans('general.export') }}</a>
                                    </div>
                                    <div v-show="showVendorPerformanceEvaluationFormsProgress" style="display:none;">
                                        <button disabled class="btn btn-warning pull-right">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <div class="pull-right padding-5">
                                                @{{ progress }}%
                                            </div>
                                        </button>
                                    </div>
                                </section>
                            </div>
                        </fieldset>
                    </div>
                    <div class="smart-form">
                        <fieldset>
                            <div class="row">
                                <section class="col col-xs-12 well">
                                    <label class="label">&nbsp;</label>
                                    <div id="overallVendorPerformanceStatisticsTable"></div>
                                </section>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="tab-pane fade" id="vendor-group-statistics">
                    @include('vendor_management.dashboard.partials.view.evaluations_by_rating')
                    <hr>
                    @include('vendor_management.dashboard.partials.view.top_evaluation_scorers')
                    @include('vendor_management.dashboard.partials.view.total_vendors_evaluated')
                    <hr>
                    @include('vendor_management.dashboard.partials.view.average_scores')
                </div>
            </div>
        </div>
    </div> 
</div>
@endsection

@section('js')
    <script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    @include('vendor_management.dashboard.partials.javascript.overall_vendor_performance_statistics_table');
    @include('vendor_management.dashboard.partials.javascript.evaluations_by_rating');
    @include('vendor_management.dashboard.partials.javascript.top_evaluation_scorers');
    @include('vendor_management.dashboard.partials.javascript.total_vendors_evaluated');
    @include('vendor_management.dashboard.partials.javascript.average_scores');
    <script>
        $(document).ready(function() {
            $.get("{{ route('vendorManagement.dashboard.evaluatedProjectsTotal') }}", function(data){
                $('#totalNumberOfProjectsEvaluated').html(data.total);
            });

            var dashboardVue = new Vue({
                el: '#dashboardContent',
                data: {
                    progress: 0,
                    showVendorPerformanceEvaluationFormsDownload: false,
                    showVendorPerformanceEvaluationFormsProgress: false,
                    interval: null
                },
                methods: {
                    updateVendorPerformanceEvaluationFormProgress: function()
                    {
                        var self = this;

                        $.ajax({
                            url: "{{{ route('vendorManagement.dashboard.progress.vendorPerformanceEvaluation.forms') }}}",
                            method: 'GET',
                            success: function (data) {
                                clearInterval(self.interval);
                                if (data['exist']) {
                                    if (data['is_complete']) {
                                        self.showVendorPerformanceEvaluationFormsDownload = true;
                                        self.showVendorPerformanceEvaluationFormsProgress = false;
                                    }
                                    else{
                                        self.showVendorPerformanceEvaluationFormsDownload = false;
                                        self.showVendorPerformanceEvaluationFormsProgress = true;
                                        self.progress = data['completion_percentage'];
                                        self.interval = setInterval(()=>dashboardVue.updateVendorPerformanceEvaluationFormProgress(), 5000);
                                    }
                                } else {
                                    self.showVendorPerformanceEvaluationFormsDownload = true;
                                }

                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                            }
                        });
                    }
                }
            });

            dashboardVue.updateVendorPerformanceEvaluationFormProgress();
        });
    </script>
@endsection