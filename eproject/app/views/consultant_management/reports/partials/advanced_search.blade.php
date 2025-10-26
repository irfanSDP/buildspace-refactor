<?php use PCK\Companies\Company; ?>
<div class="well mb-4">
    <div class="row">
        <section class="col col-xs-9 col-md-9 col-lg-9">
            <h5><i class="fa fa-search"></i> {{ trans('general.advancedSearch') }} <button id="global_search-toggle-btn" class="btn btn-xs btn-info"><i class="far fa-eye"></i> {{ trans('general.show') }}</button></h5>
        </section>
    </div>
    <fieldset id="global_search-content" style="display:none;">
        {{ Form::open(['route' => ['consultant.management.reports.ajax.list'], 'id'=>'advanced_search-form', 'class' => 'smart-form', 'method' => 'post']) }}
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
        <div class="row mt-4">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <div class="card border">
                    <div class="card-header">
                        <strong>Search Criteria</strong>
                    </div>
                    <div class="card-body">
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'reference_no', 1, ['id'=>'criteria_reference_no', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_reference_no">{{{ trans('companies.referenceNo') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'consultant_name', 0, ['id'=>'criteria_consultant_name', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_consultant_name">{{{ trans('vendorManagement.consultant') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'vendor_category', 0, ['id'=>'criteria_vendor_category', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_vendor_category">{{{ trans('general.consultantCategories') }}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            {{ Form::radio('search_criteria', 'subsidiary_name', 0, ['id'=>'criteria_subsidiary_name', 'class'=>'custom-control-input']) }}
                            <label class="custom-control-label" for="criteria_subsidiary_name">{{{ trans('general.subsidiaryTownship') }}}</label>
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
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <div class="card border">
                    <div class="card-header">
                        <strong>Rec of Consultant Approved Date</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.from') }}} :</label>
                                <label class="input">
                                    <i class="icon-append fa fa-calendar"></i>
                                    <input class="datetimepicker" name="roc_approved_date_from" type="text" id="roc_approved_date_from-input" class="form-control" placeholder="Rec of Consultant Approved Date" value="">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('email.to') }}} :</label>
                                <label class="input">
                                    <i class="icon-append fa fa-calendar"></i>
                                    <input class="datetimepicker" name="roc_approved_date_to" type="text" id="roc_approved_date_to-input" class="form-control" placeholder="Rec of Consultant Approved Date" value="">
                                </label>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <div class="card border">
                    <div class="card-header">
                        <strong>Letter of Award Date</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.from') }}} :</label>
                                <label class="input">
                                    <i class="icon-append fa fa-calendar"></i>
                                    <input class="datetimepicker" name="loa_date_from" type="text" id="loa_date_from-input" class="form-control" placeholder="Letter of Award Date" value="">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('email.to') }}} :</label>
                                <label class="input">
                                    <i class="icon-append fa fa-calendar"></i>
                                    <input class="datetimepicker" name="loa_date_to" type="text" id="loa_date_to-input" class="form-control" placeholder="Letter of Award Date" value="">
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