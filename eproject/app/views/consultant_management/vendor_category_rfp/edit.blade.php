@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        <li>@if(!isset($vendorCategoryRfp)) {{{ trans('general.addConsultantRFP') }}} @else {{{ trans('general.editConsultantRFP') }}} @endif</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-user-tie"></i> @if(!isset($vendorCategoryRfp)) {{{ trans('general.addConsultantRFP') }}} @else {{{ trans('general.editConsultantRFP') }}} @endif
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.vendor.category.rfp.store', $consultantManagementContract->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-8 col-lg-8">
                            <label class="label">{{{ trans('vendorManagement.vendorCategory') }}} <span class="required">*</span>:</label>
                            <label class="fill-horizontal {{{ $errors->has('vendor_category_id') ? 'state-error' : null }}}">
                                @if(isset($vendorCategoryRfp))
                                {{ Form::hidden('vendor_category_id', $vendorCategoryRfp->vendor_category_id) }}
                                <div class="well">
                                {{{ $vendorCategoryRfp->vendorCategory->name }}}
                                </div>
                                @else
                                <select class="select2 fill-horizontal" name="vendor_category_id" id="vendor_category_id">
                                    <option value="-1">{{{ trans('forms.none') }}}</option>
                                    @foreach($vendorCategories as $vendorCategory)
                                    <option value="{{$vendorCategory->id}}" @if($vendorCategory->id == Input::old('vendor_category_id')) selected @endif>{{{ $vendorCategory->name }}}</option>
                                    @endforeach
                                </select>
                                @endif
                            </label>
                            {{ $errors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-4 col-lg-4">
                            <label class="label">{{{ trans('general.costType') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('cost_type') ? 'state-error' : null }}}">
                                <select class="select2 fill-horizontal" name="cost_type" id="cost_type">
                                    <option value="">{{{ trans('forms.none') }}}</option>
                                    @foreach($costTypes as $id => $name)
                                    <option value="{{$id}}" @if($id == Input::old('cost_type', isset($vendorCategoryRfp) ? $vendorCategoryRfp->cost_type : null)) selected @endif>{{{ $name }}}</option>
                                    @endforeach
                                </select>
                            </label>
                            {{ $errors->first('cost_type', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', (isset($vendorCategoryRfp)) ? $vendorCategoryRfp->id : -1) }}
                        {{ Form::hidden('consultant_management_contract_id', $consultantManagementContract->id) }}
                        {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection