<?php
$vendorErrors = $errors->getBag('vendor');
?>
<fieldset>
    {{ Form::open(['route'=>['vendorProfile.vendor.store'],'id'=>'vendor_work_categories-form', 'class' => 'smart-form', 'method' => 'post']) }}
    <div class="row">
        <section class="col col-xs-9 col-md-9 col-lg-9">
            <h5 id="vendor_work_categories-header">{{ trans('forms.add') }} {{ trans('vendorManagement.vendorWorkCategory') }}</h5>
        </section>
        <section class="col col-xs-3 col-md-3 col-lg-3">
            <div class="pull-right">
                <button type="submit" class="btn btn-primary btn-md header-btn">
                    <i class="far fa-save"></i> {{{ trans('forms.save') }}}
                </button>
                <button type="button" class="btn btn-default btn-md header-btn" id="vendor_edit-cancel-btn" style="display:none;">{{{ trans('forms.cancel') }}}</button>
            </div>
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-6">
            <label class="label">{{{ trans('companies.companyName') }}} :</label>
            <p>{{ isset($company) ? $company->name : null}}</p>
        </section>
        <section class="col col-xs-6 col-md-4 col-lg-3">
            <label class="label">{{{ trans('contractGroupCategories.vendorGroup') }}} :</label>
            <p>{{(isset($company) && $company->contractGroupCategory) ? $company->contractGroupCategory->name : null}}</p>
        </section>
        <section class="form-group col col-xs-6 col-md-4 col-lg-2">
            <div class="custom-control custom-checkbox">
                {{ Form::checkbox('vendor[is_qualified]', 1, Input::old('vendor.is_qualified', true), ['id'=>'is_qualified-input', 'class'=>'custom-control-input']) }}
                <label class="custom-control-label" for="is_qualified-input">{{ trans('vendorManagement.qualified') }}</label>
            </div>
        </section>
    </div>
    <div class="row">
        <section class="form-group col col-xs-12 col-md-12 col-lg-6">
            <label class="label">{{{ trans('vendorManagement.vendorCategory') }}} <span class="required">*</span>:</label>
            <label id="input_label-vendor-vendor_category_id" class="fill-horizontal {{{ $vendorErrors->has('vendor.vendor_category_id') ? 'state-error' : null }}}">
                <select id="vendor_work_category-vendor_category_id-select" class="select2 fill-horizontal" name="vendor[vendor_category_id]" style="width:100%;">
                    <option value=""></option>
                    @if(isset($company))
                    @foreach($company->vendorCategories as $vendorCategoryObj)
                        <option value="{{$vendorCategoryObj->id}}" @if(Input::old("vendor.vendor_category_id") == $vendorCategoryObj->id) selected @endif>{{{$vendorCategoryObj->name}}}</option>
                    @endforeach
                    @endif
                </select>
            </label>
            {{ $vendorErrors->first('vendor.vendor_category_id', '<em class="invalid">:message</em>') }}
        </section>
        <section class="form-group col col-xs-12 col-md-12 col-lg-6">
            <label class="label">{{{ trans('vendorManagement.vendorWorkCategory') }}}:</label>
            <label id="input_label-vendor-vendor_work_category_id" class="fill-horizontal {{{ $vendorErrors->has('vendor.vendor_work_category_id') ? 'state-error' : null }}}">
                <select id="vendor_work_category-vendor_work_category_id-select" class="select2 fill-horizontal" name="vendor[vendor_work_category_id]"></select>
            </label>
            {{ $vendorErrors->first('vendor.vendor_work_category_id', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="form-group col col-xs-4 col-md-6 col-lg-8">
            <label class="label">{{{ trans('general.type') }}} <span class="required">*</span>:</label>
            <label id="input_label-vendor-type" class="fill-horizontal {{{ $vendorErrors->has('vendor.type') ? 'state-error' : null }}}">
                <select id="vendor_type-select" class="select2 fill-horizontal" name="vendor[type]" style="width:100%;">
                    <option value=""></option>
                    <option value="{{{ \PCK\Vendor\Vendor::TYPE_ACTIVE }}}" @if(Input::old("vendor.type", (isset($vendor)) ? $vendor->type : null) == \PCK\Vendor\Vendor::TYPE_ACTIVE) selected @endif>{{{trans('vendorManagement.activeVendorList')}}}</option>
                    <option value="{{{ \PCK\Vendor\Vendor::TYPE_WATCH_LIST }}}" @if(Input::old("vendor.type", (isset($vendor)) ? $vendor->type : null) == \PCK\Vendor\Vendor::TYPE_WATCH_LIST) selected @endif>{{{trans('vendorManagement.watchList')}}}</option>
                </select>
            </label>
            {{ $vendorErrors->first('vendor.type', '<em class="invalid">:message</em>') }}
        </section>
        <div id="watchlist-date" @if(Input::old("vendor.type", (isset($vendor)) ? $vendor->type : null) != \PCK\Vendor\Vendor::TYPE_WATCH_LIST) style="display:none;" @endif>
            <section class="form-group col col-xs-4 col-md-3 col-lg-2">
                <label class="label">{{{ trans('vendorManagement.entryDate') }}} <span class="required">*</span>:</label>
                <label id="input_label-vendor-watch_list_entry_date" class="input {{{ $vendorErrors->has('vendor.watch_list_entry_date') ? 'state-error' : null }}}">
                    <?php
                    $entryDate = Input::old('vendor.watch_list_entry_date', (isset($vendor)) ? $vendor->watch_list_entry_date : null);
                    $entryDate = ($entryDate) ? date('Y-m-d',strtotime($entryDate)) : null;
                    ?>
                    <input min="2000-01-01" name="vendor[watch_list_entry_date]" id="watch_list_entry_date-input" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.entryDate') }}}" value="{{ $entryDate }}"/>
                </label>
                {{ $vendorErrors->first('vendor.watch_list_entry_date', '<em class="invalid">:message</em>') }}
            </section>
            <section class="form-group col col-xs-4 col-md-3 col-lg-2">
                <label class="label">{{{ trans('vendorManagement.releaseDate') }}} <span class="required">*</span>:</label>
                <label id="input_label-vendor-watch_list_release_date" class="input {{{ $vendorErrors->has('vendor.watch_list_release_date') ? 'state-error' : null }}}">
                    <?php
                    $releaseDate = Input::old('vendor.watch_list_release_date', (isset($vendor)) ? $vendor->watch_list_release_date : null);
                    $releaseDate = ($releaseDate) ? date('Y-m-d',strtotime($releaseDate)) : null;
                    ?>
                    <input min="2000-01-01" name="vendor[watch_list_release_date]" id="watch_list_release_date-input" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.releaseDate') }}}" value="{{ $releaseDate }}"/>
                </label>
                {{ $vendorErrors->first('vendor.watch_list_release_date', '<em class="invalid">:message</em>') }}
                </section>
        </div>
    </div>
    {{ Form::hidden('cid', (isset($company)) ? $company->id : -1, ['id'=>'company_id-hidden']) }}
    {{ Form::hidden('id', (isset($vendor)) ? $vendor->id : -1, ['id'=>'vendor_id-hidden']) }}
    {{ Form::close() }}
</fieldset>

<hr class="simple"/>

<div class="row">
    <div class="col col-lg-12">
        <div id="vendor_work_categories-table"></div>
    </div>
</div>

<div class="modal fade" id="vendorDeleteModal" tabindex="-1" aria-labelledby="vendorDeleteModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header alert-danger">
                <h4 class="modal-title">{{trans('forms.delete')}} {{ trans('vendorManagement.vendorWorkCategory') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <p>{{trans('requestForVariation.areYouSureToDelete')}}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('forms.cancel')}}</button>
                <button type="button" class="btn btn-danger btn-ok" data-record-id="">{{trans('forms.delete')}}</button>
            </div>
        </div>
    </div>
</div>