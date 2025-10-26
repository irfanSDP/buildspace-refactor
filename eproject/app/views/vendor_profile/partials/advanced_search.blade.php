<?php use PCK\Companies\Company; ?>
<div class="well">
    <div class="row">
        <section class="col col-xs-9 col-md-9 col-lg-9">
            <h5><i class="fa fa-search"></i> {{ trans('general.advancedSearch') }} <button id="global_search-toggle-btn" class="btn btn-xs btn-info"><i class="far fa-eye"></i> {{ trans('general.show') }}</button></h5>
        </section>
    </div>

    <fieldset id="global_search-content" style="display:none;">
        {{ Form::open(['route' => ['vendorProfile.ajax.list'], 'id'=>'advanced_search-form', 'class' => 'smart-form', 'method' => 'post']) }}
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-md header-btn">
                        <i class="fa fa-search"></i> {{{ trans('general.search') }}}
                    </button>
                    <button type="button" class="btn btn-default btn-md header-btn" id="advanced_search-reset-btn"><i class="fas fa-undo"></i> {{{ trans('forms.reset') }}}</button>
                </div>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <div class="card border">
                    <div class="card-header">
                        <strong>Search Criteria</strong>
                    </div>
                    <div class="card-body">

                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'company_name', 1, ['id'=>'criteria_company_name', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_company_name">{{{ trans('companies.companyName') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'reference_no', 0, ['id'=>'criteria_reference_no', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_reference_no">{{{ trans('companies.referenceNumber') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'vendor_category', 0, ['id'=>'criteria_vendor_category', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_vendor_category">{{{ trans('vendorManagement.vendorCategory') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'vendor_work_category', 0, ['id'=>'criteria_vendor_work_category', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_vendor_work_category">{{{ trans('vendorManagement.vendorWorkCategory') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'state', 0, ['id'=>'criteria_state', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_state">{{{ trans('projects.state') }}}</label>
                        </div>
                        
                        <hr class="simple"/>

                        <div class="well">
                            <label class="input">
                                {{ Form::text('criteria_search_str', '', ['id'=>'criteria_search_str-input']) }}
                            </label>
                        </div>

                    </div>
                </div>

            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-4 col-lg-4">
                <div class="card border">
                    <div class="card-header">
                        <strong>Vendor Status</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="vendor_status" id="vendor_status" style="width:100%;">
                                <option value="">{{{ trans('forms.none') }}}</option>
                                <option value="{{ Company::STATUS_ACTIVE }}">{{ trans('general.active') }}</option>
                                <option value="{{ Company::STATUS_EXPIRED }}">{{ trans('general.expired') }}</option>
                                <option value="{{ Company::STATUS_DEACTIVATED }}">{{ trans('general.deactivated') }}</option>
                            </select>
                        </label>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-4 col-lg-4">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{{ trans('contractGroupCategories.vendorGroup') }}}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select class="select2 fill-horizontal" name="contract_group_category_id" id="contract_group_category_id-select" style="width:100%;">
                                <option value="">{{{ trans('forms.none') }}}</option>
                                @foreach($contractGroups as $contractGroup)
                                <option value="{{$contractGroup->id}}">{{{$contractGroup->description}}}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>

            <section class="col col-xs-12 col-md-4 col-lg-4">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{ trans('vendorManagement.companyStatus') }}</strong>
                    </div>
                    <div class="card-body">
                        <label class="fill-horizontal">
                            <select name="company_status[]" class="select2" multiple>
                                @foreach($companyStatusDescriptions as $identifier => $description)
                                <option value="{{{ $identifier }}}">{{{ $description }}}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-md-4 col-lg-4">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{{ trans('vendorManagement.activationDate') }}}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.from') }}} :</label>
                                <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                    <input name="activation_date_from" id="activation_date_from-input" type="text" class="form-control datetimepicker" placeholder="{{{ trans('vendorManagement.activationDate') }}}">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('email.to') }}} :</label>
                                <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                    <input name="activation_date_to" id="activation_date_to-input" type="text" class="form-control datetimepicker" placeholder="{{{ trans('vendorManagement.activationDate') }}}">
                                </label>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

            <section class="col col-xs-12 col-md-4 col-lg-4">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{{ trans('vendorManagement.expiryDate') }}}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.from') }}} :</label>
                                <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                    <input name="expiry_date_from" id="expiry_date_from-input" type="text" class="form-control datetimepicker" placeholder="{{{ trans('vendorManagement.expiryDate') }}}">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('email.to') }}} :</label>
                                <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                    <input name="expiry_date_to" id="expiry_date_to-input" type="text" class="form-control datetimepicker" placeholder="{{{ trans('vendorManagement.expiryDate') }}}">
                                </label>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

            <section class="col col-xs-12 col-md-4 col-lg-4">
                <div class="card border">
                    <div class="card-header">
                        <strong>{{{ trans('vendorManagement.deactivationDate') }}}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.from') }}} :</label>
                                <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                    <input name="deactivation_date_from" id="deactivation_date_from-input" type="text" class="form-control datetimepicker" placeholder="{{{ trans('vendorManagement.deactivationDate') }}}">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('email.to') }}} :</label>
                                <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                    <input name="deactivation_date_to" id="deactivation_date_to-input" type="text" class="form-control datetimepicker" placeholder="{{{ trans('vendorManagement.deactivationDate') }}}">
                                </label>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        {{ Form::close() }}
    </fieldset>

</div>