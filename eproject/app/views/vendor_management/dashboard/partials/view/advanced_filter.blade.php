<?php use PCK\Companies\Company; ?>
<div class="well">
    <div class="row">
        <section class="col col-xs-9 col-md-9 col-lg-9">
            <h5><i class="fa fa-search"></i> {{ trans('vendorManagement.advancedFilters') }} <button id="global_search-toggle-btn" class="btn btn-xs btn-info"><i class="far fa-eye"></i> {{ trans('general.show') }}</button></h5>
        </section>
    </div>

    <fieldset id="global_search-content" style="display:none;">
        {{ Form::open(['id'=>'advanced_filter-form', 'class' => 'smart-form', 'method' => 'GET']) }}
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <div class="pull-right">
                    <button type="submit" class="btn btn-info btn-md header-btn">
                    <i class="fa fa-filter"></i> {{{ trans('general.filter') }}}
                    </button>
                    <button type="button" class="btn btn-default btn-md header-btn" id="advanced_filter-reset-btn"><i class="fas fa-undo"></i> {{{ trans('forms.reset') }}}</button>
                </div>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('countries.countries') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="country" id="country_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->country }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3" id="state_select_container" hidden>
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('countries.states') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="state" id="state_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                            </select>
                        </label>
                    </div>
                </div>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.vendorGroup') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="vendor_group" id="vendor_group_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                                @foreach($externalVendors as $vendorGroup)
                                    <option value="{{ $vendorGroup->id }}">{{ $vendorGroup->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3" id="vendor_category_select_container" hidden>
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.vendorCategory') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="vendor_category" id="vendor_category_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3" id="vendor_work_category_select_container" hidden>
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.vendorWorkCategory') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="vendor_work_category" id="vendor_work_category_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3" id="vendor_work_subcategory_select_container" hidden>
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.vendorSubWorkCategory') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="vendor_work_subcategory" id="vendor_work_subcategory_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                            </select>
                        </label>
                    </div>
                </div>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.registrationStatus') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="registration_status" id="registration_status_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                                @foreach($registrationStatuses as $key => $status)
                                    <option value="{{ $key }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3" id="state_select_container">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.companyStatus') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="company_status" id="company_status_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                                @foreach($companyStatuses as $key => $status)
                                    <option value="{{ $key }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.preQualification') . ' ' . trans('vendorManagement.grade') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="preq_grade" id="preq_grade_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                                @foreach($preqGradeLevels as $key => $description)
                                    <option value="{{ $key }}">{{ $description }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3" id="state_select_container">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.vendorPerformanceEvaluation') . ' ' . trans('vendorManagement.grade') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="vpe_grade" id="vpe_grade_select" style="width:100%;">
                                <option value="">{{{ trans('general.all') }}}</option>
                                @foreach($vpeGradeLevels as $key => $description)
                                    <option value="{{ $key }}">{{ $description }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
        </div>
        {{ Form::close() }}
    </fieldset>
</div>