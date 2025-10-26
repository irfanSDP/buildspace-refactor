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
            {{ trans('vendorManagement.vendorStatisticsDashboard') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('vendorManagement.vendorStatisticsDashboard') }}</h2>
            </header>
            <div class="widget-body">
                @include('vendor_management.dashboard.partials.view.advanced_filter')
            </div>

            <div class="smart-form" id="vendorsByCountrySection">
                <fieldset>
                    <div class="row">
                        <section class="col col-12">
                            <label class="label">{{ trans('vendorManagement.vendorsByCountry') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByCountryChart"></div>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>

            <div class="smart-form" id="vendorsByStateSection" hidden>
                <fieldset>
                    <div class="row">
                        <section class="col col-12">
                            <label class="label">{{ trans('vendorManagement.vendorsByState') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByStateChart"></div>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>

            <div class="smart-form">
                <fieldset>
                    <div class="row">
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByVendorGroup') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByVendorGroupChart"></div>
                            </div>
                        </section>
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByVendorCategory') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByVendorCategoryTable"></div>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>

            <div class="smart-form">
                <fieldset>
                    <div class="row">
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByVendorWorkCategory') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByVendorWorkCategoryTable"></div>
                            </div>
                        </section>
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByVendorSubWorkCategory') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByVendorWorkSubCategoryTable"></div>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>

            <div class="smart-form">
                <fieldset>
                    <div class="row">
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByRegistrationStatus') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByRegistrationStatusChart"></div>
                            </div>
                        </section>
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByCompanyStatus') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByCompanyStatusChart"></div>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="smart-form">
                <fieldset>
                    <div class="row">
                        @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByPreqRating') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByPreqRatingChart"></div>
                            </div>
                        </section>
                        @endif
                        @if($latestCompletedVpeCycle)
                        <section class="col col-6">
                            <label class="label">{{ trans('vendorManagement.vendorsByVpeRating') }}</label>
                            <div class="col col-xs-12 well">
                                <div id="vendorsByVpeRatingChart"></div>
                            </div>
                        </section>
                        @endif
                    </div>
                </fieldset>
            </div>

            <div class="smart-form">
                <fieldset>
                    <label class="label">{{ trans('vendorManagement.totalNumberOfNewlyRegisteredVendorsByDate') }}</label>
                    <div class="well">
                        <div class="row">
                            <section class="col col-xs-3">
                                <label class="label">{{ trans('general.from') }}</label>
                                <input id="date_from" name="date_from" type="text" class="form-control">
                            </section>
                            <section class="col col-xs-3">
                                <label class="label">{{ trans('general.to') }}</label>
                                <input id="date_to" name="date_to" type="text" class="form-control">
                            </section>
                            <section class="col col-xs-1">
                                <label class="label">&nbsp;</label>
                                <button id="btnFilterTotalNumberOfNewlyRegisteredVendorsByDate" class="btn btn-info"><i class="fas fa-filter"></i> {{ trans('general.filter') }}</button>
                            </section>
                            <section class="col col-xs-5">
                                <label class="label">&nbsp;</label>
                                <label class="label pull-right">{{ trans('vendorManagement.numberOfVendors') }} : <span data-component="newlyRegisteredVendorsCount">0</span></label>
                            </section>
                        </div>
                        <div class="row">
                            <div class="col col-xs-12">
                                <div id="registration-statistics-newly-registered-vendors-by-date"></div>
                            </div>
                        </div>
                    </div>
                </fieldset>
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
    @include('vendor_management.dashboard.partials.javascript.vendors_by_country_chart_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_state_chart_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_vendor_group_chart_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_vendor_category_table_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_vendor_work_category_table_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_vendor_work_subcategory_table_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_registration_status_chart_javascript');
    @include('vendor_management.dashboard.partials.javascript.vendors_by_company_status_chart_javascript');
    @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
        @include('vendor_management.dashboard.partials.javascript.vendors_by_preq_rating_chart_javascript');
    @endif
    @if($latestCompletedVpeCycle)
        @include('vendor_management.dashboard.partials.javascript.vendors_by_vpe_rating_chart_javascript');
    @endif
    @include('vendor_management.dashboard.partials.javascript.newly_registered_vendors_by_date_javascript');
    @include('templates.generic_table_modal', [
        'modalId'    => 'newlyRegisteredVendorListModal',
        'title'      => trans('vendorManagement.newlyRegisteredVendors'),
        'tableId'    => 'newlyRegisteredVendorListTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
    <script>
        $('#global_search-toggle-btn').on('click', function(e){
            if($('#global_search-content:visible').length){
                $(this).html('<i class="far fa-eye"></i> {{ trans("general.show") }}');
                $('#global_search-content').hide(200);
            }else{
                $(this).html('<i class="far fa-eye-slash"></i> {{ trans("general.hide") }}');
                $('#global_search-content').show(200);
            }
        });

        $('#country_select').on('change', function(e) {
            e.preventDefault();

            var countryId = $(this).val();

            if(countryId == '') {
                resetStateSelect(false);
                return false;
            }

            resetStateSelect();

            $.ajax({
                url: "{{ route('country.states.get') }}",
                method: 'GET',
                data: {
                    countryId: countryId,
                },
                success: function(states){
                    states.forEach(function(state, index) {
                        $('#state_select').append(`<option value="${state.id}">${state.name}</option>`)
                    });
                }
            });
        });

        function resetStateSelect(showFlag = true)
        {
            $('#state_select').empty().append('<option value="">{{ trans("general.all") }}</option>');

            if(showFlag) {
                $('#state_select_container').show(200);
            } else {
                $('#state_select_container').hide(200);
            }
        }

        $('#vendor_group_select').on('change', function(e) {
            e.preventDefault();

            resetVendorCategorySelect(false);
            resetVendorWorkCategorySelect(false);
            resetVendorWorkSubCategorySelect(false);

            vendorGroupId = $(this).val();

            if(vendorGroupId == '') return false;

            resetVendorCategorySelect();

            $.ajax({
                url: "{{ route('vendor.categories.get') }}",
                method: 'GET',
                data: {
                    vendorGroupId: vendorGroupId,
                },
                success: function(vendorCategories){
                    vendorCategories.forEach(function(vendorCategory, index) {
                        $('#vendor_category_select').append(`<option value="${vendorCategory.id}">${vendorCategory.name}</option>`)
                    });
                }
            });
        });

        function resetVendorCategorySelect(showFlag = true) {
            $('#vendor_category_select').empty().append('<option value="">{{ trans("general.all") }}</option>');

            if(showFlag) {
                $('#vendor_category_select_container').show(200);
            } else {
                $('#vendor_category_select_container').hide(200);
            }
        }

        $('#vendor_category_select').on('change', function(e) {
            e.preventDefault();

            resetVendorWorkCategorySelect(false);
            resetVendorWorkSubCategorySelect(false);

            vendorCategoryId = $(this).val();

            if(vendorCategoryId == '') return false;

            resetVendorWorkCategorySelect();

            $.ajax({
                url: "{{ route('vendor.work.categories.get') }}",
                method: 'GET',
                data: {
                    vendorCategoryId: vendorCategoryId,
                },
                success: function (response) {
                    response.data.forEach(function(item, index) {
                        $('#vendor_work_category_select').append(`<option value="${item.id}">${item.description}</option>`);
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        function resetVendorWorkCategorySelect(showFlag = true) {
            $('#vendor_work_category_select').empty().append('<option value="">{{ trans("general.all") }}</option>');

            if(showFlag) {
                $('#vendor_work_category_select_container').show(200);
            } else {
                $('#vendor_work_category_select_container').hide(200);
            }
        }

        $('#vendor_work_category_select').on('change', function(e) {
            e.preventDefault();

            resetVendorWorkSubCategorySelect(false);

            vendorWorkCategoryId = $(this).val();

            if(vendorWorkCategoryId == '') return false;

            resetVendorWorkSubCategorySelect();

            $.ajax({
                url: "{{ route('vendor.work.sub.categories.get') }}",
                method: 'GET',
                data: {
                    vendorWorkCategoryId: vendorWorkCategoryId,
                },
                success: function (response) {
                    response.data.forEach(function(item, index) {
                        $('#vendor_work_subcategory_select').append(`<option value="${item.id}">${item.description}</option>`);
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });

        function resetVendorWorkSubCategorySelect(showFlag = true) {
            $('#vendor_work_subcategory_select').empty().append('<option value="">{{ trans("general.all") }}</option>');

            if(showFlag) {
                $('#vendor_work_subcategory_select_container').show(200);
            } else {
                $('#vendor_work_subcategory_select_container').hide(200);
            }
        }

        $('#advanced_filter-reset-btn').on('click', function(e) {
            e.preventDefault();

            $('#country_select').val('').trigger('change');
            resetStateSelect(false);

            $('#vendor_group_select').val('').trigger('change');
            resetVendorCategorySelect(false);
            resetVendorWorkCategorySelect(false);
            resetVendorWorkSubCategorySelect(false);

            $('#registration_status_select').val('').trigger('change');
            $('#company_status_select').val('').trigger('change');
            $('#preq_grade_select').val('').trigger('change');
            $('#vpe_grade_select').val('').trigger('change');
        });

        $('#advanced_filter-form').on('submit', function(e) {
            e.preventDefault();
            var formInputsArray = {};

            $.each($(this).serializeArray(), function() {
                formInputsArray[this.name] = this.value;
            });

            if(formInputsArray['country'] == '') {
                $('#vendorsByCountrySection').show(200);
                renderVendorsByCountryChart(formInputsArray, 'vendorsByCountry');

                $('#vendorsByStateSection').hide(200);
            } else {
                $('#vendorsByCountrySection').hide(200);

                if(formInputsArray['state'] == '') {
                    $('#vendorsByStateSection').show(200);
                    renderVendorsByStateChart(formInputsArray, 'vendorsByState');
                } else {
                    $('#vendorsByStateSection').hide(200);
                }
            }

            renderVendorsByVendorGroupChart(formInputsArray, 'vendorsByVendorGroup');
            renderVendorsByVendorCategoryTable(formInputsArray, 'vendorsByVendorCategory');
            renderVendorsByVendorWorkCategoryTable(formInputsArray, 'vendorsByVendorWorkCategory');
            renderVendorWorkSubCategoryTable(formInputsArray, 'vendorsByVendorWorkSubCategory')
            renderVendorsByRegistrationStatusChart(formInputsArray, 'vendorsByRegistrationStatus');
            renderVendorsByCompanyStatusChart(formInputsArray, 'vendorsByCompanyStatus');
            renderVendorsByPreqRatingChart(formInputsArray, 'vendorsByPreqRating');
            renderVendorsByVpeRatingChart(formInputsArray, 'vendorsByVpeRating');

            $('#global_search-content').hide(200);

            return false;
        });
    </script>
@endsection