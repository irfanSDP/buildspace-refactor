@extends('layout.main')

<?php
use Carbon\Carbon;
$currencyCode = empty($contract->modified_currency_code) ? $contract->country->currency_code : $contract->modified_currency_code;
?>
@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $contract->short_title, [$contract->id]) }}</li>
        <li>@if(isset($phase)) {{{ trans('forms.edit') }}} @else {{{ trans('forms.add') }}} @endif {{{ trans('general.phase') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-contract"></i> {{{ trans('general.phase') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($phase)) {{{ trans('forms.edit') }}} {{{ $phase->short_title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.phase') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.contracts.phase.store'], 'class' => 'smart-form']) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('general.subsidiaryTownship') }}}/{{{trans('general.phase')}}} <span class="required">*</span>:</label>
                                <label class="input fill-horizontal {{{ $errors->has('subsidiary_id') ? 'state-error' : null }}}">
                                    <select name="subsidiary_id" id="subsidiary_id" class ="fill-horizontal" required>
                                        <?php
                                        $selectedSubsidiaryId = Input::old('subsidiary_id', isset($phase) ? $phase->subsidiary_id : null);
                                        ?>
                                        @foreach($subsidiaries as $subsidiary)
                                        <option value="{{$subsidiary->id}}" data-parent="{{$subsidiary->parent_id}}" @if($subsidiary->id == $selectedSubsidiaryId) selected @endif>{{{$subsidiary->name}}}</option>
                                        @endforeach
                                    </select>
                                </label>
                                {{ $errors->first('subsidiary_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('general.developmentType') }}} <span class="required">*</span>:</label>
                                <label class="input fill-horizontal {{{ $errors->has('development_type_id') ? 'state-error' : null }}}">
                                    {{ Form::select('development_type_id', $developmentTypes, Input::old('development_type_id', isset($phase) ? $phase->development_type_id : null), ['id'=>'development_type_id', 'class' => 'select2 fill-horizontal', 'required'=>'required']) }}
                                </label>
                                {{ $errors->first('development_type_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('general.grossAcreage') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('gross_acreage') ? 'state-error' : null }}}">
                                    {{ Form::number('gross_acreage', Input::old('gross_acreage', (isset($phase)) ? number_format($phase->gross_acreage, 2, '.', '') : "0.00"), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $errors->first('gross_acreage', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">Project Brief <span class="required">*</span>:</label>
                                <label class="textarea {{{ $errors->has('business_case') ? 'state-error' : null }}}">
                                    {{ Form::textarea('business_case', Input::old('business_case', (isset($phase)) ? $phase->business_case : ""), ['required' => 'required', 'autofocus' => 'autofocus', 'rows' => 4]) }}
                                </label>
                                {{ $errors->first('business_case', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <hr class="simple">
                        <div class="row">
                            <section class="col col-xs-6 col-md-6 col-lg-3">
                                <label class="label">{{{ trans('general.projectBudget') }}} ({{{$currencyCode}}})<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('project_budget') ? 'state-error' : null }}}">
                                    {{ Form::number('project_budget', Input::old('project_budget', (isset($phase)) ? number_format($phase->project_budget, 2, '.', '') : "0.00"), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $errors->first('project_budget', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-6 col-md-6 col-lg-3">
                                <label class="label">{{{ trans('general.totalConstructionCost') }}} ({{{$currencyCode}}})<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('total_construction_cost') ? 'state-error' : null }}}">
                                    {{ Form::number('total_construction_cost', Input::old('total_construction_cost', (isset($phase)) ? number_format($phase->total_construction_cost, 2, '.', '') : "0.00"), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $errors->first('total_construction_cost', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-6 col-md-6 col-lg-3">
                                <label class="label">{{{ trans('general.totalLandscapeCost') }}} ({{{$currencyCode}}})<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('total_landscape_cost') ? 'state-error' : null }}}">
                                    {{ Form::number('total_landscape_cost', Input::old('total_landscape_cost', (isset($phase)) ? number_format($phase->total_landscape_cost, 2, '.', '') : "0.00"), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $errors->first('total_landscape_cost', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-6 col-md-6 col-lg-3">
                                <label class="label">{{{ trans('general.costPerSquareFeet') }}} ({{{$currencyCode}}})<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('cost_per_square_feet') ? 'state-error' : null }}}">
                                    {{ Form::number('cost_per_square_feet', Input::old('cost_per_square_feet', (isset($phase)) ? number_format($phase->cost_per_square_feet, 2, '.', '') : "0.00"), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $errors->first('cost_per_square_feet', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <hr class="simple">
                        <div class="row">
                            <section class="col col-xs-4 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('general.targetPlanningPermission') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('planning_permission_date') ? 'state-error' : null }}}">
                                    <?php
                                        $planningPermissionDate = Input::old('planning_permission_date', (isset($phase)) ? Carbon::parse($phase->planning_permission_date)->format('Y-m-d') : date('Y-m-d'));
                                    ?>
                                    <input type="date" name="planning_permission_date" value="{{ $planningPermissionDate }}">
                                </label>
                                {{ $errors->first('planning_permission_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-4 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('general.targetBuildingPlan') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('building_plan_date') ? 'state-error' : null }}}">
                                    <?php
                                        $buildingPlanDate = Input::old('building_plan_date', (isset($phase)) ? Carbon::parse($phase->building_plan_date)->format('Y-m-d') : date('Y-m-d'));
                                    ?>
                                    <input type="date" name="building_plan_date" value="{{ $buildingPlanDate }}">
                                </label>
                                {{ $errors->first('building_plan_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-4 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('general.targetLaunch') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('launch_date') ? 'state-error' : null }}}">
                                    <?php
                                        $launchDate = Input::old('launch_date', (isset($phase)) ? Carbon::parse($phase->launch_date)->format('Y-m-d') : date('Y-m-d'));
                                    ?>
                                    <input type="date" name="launch_date" value="{{ $launchDate }}">
                                </label>
                                {{ $errors->first('launch_date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <hr class="simple">
                        <div class="row">
                            <section class="col col-xs-6 col-sm-6 col-md-8 col-lg-8">
                                <h5>{{{trans('general.productTypes')}}}</h5>
                            </section>
                            <section class="col col-xs-6 col-sm-6 col-md-4 col-lg-4">
                                <div class="pull-right">
                                {{ Form::button('<i class="fa fa-plus"></i> '.trans('forms.add')." ".trans('general.productType'), ['id'=>'addPhaseBtn', 'class' => 'btn btn-info'] )  }}
                                </div>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <table class="table table-bordered table-condensed table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width:auto;">{{{trans('general.productTypes')}}}</th>
                                            <th style="width:82px;text-align:center;">{{{trans('forms.delete')}}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="phaseRow" class="phaseRow">
                                    <?php
                                    $consultantManagementProductTypes = Input::old('product_type', (isset($phase)) ? $phase->productTypes->toArray() : [['product_type_id'=>-1, 'number_of_unit'=>0, 'lot_dimension_length'=>'0.00', 'lot_dimension_width'=>'0.00', 'proposed_built_up_area'=>'0.00', 'proposed_average_selling_price'=>'0.00']]);
                                    $developmentTypeId = Input::old('development_type_id', isset($phase) ? $phase->development_type_id : null);
                                    $productTypes = [];
                                    if(!empty($developmentTypeId))
                                    {
                                        $developmentType = PCK\ConsultantManagement\DevelopmentType::find($developmentTypeId);
                                        if($developmentType)
                                        {
                                            $productTypes = $developmentType->productTypes()->lists('title', 'id');
                                        }
                                    }
                                    ?>
                                    @foreach($consultantManagementProductTypes as $productTypeIdx => $consultantManagementProductType)
                                        <tr class="phaseRecordRow">
                                            <td>
                                                <div class="row">
                                                    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                        <label class="label">{{{ trans('general.productType') }}} <span class="required">*</span>:</label>
                                                        <label class="input fill-horizontal {{{ $errors->has('product_type.'.$productTypeIdx.'.product_type_id') ? 'state-error' : null }}}">
                                                            <select name="product_type[{{$productTypeIdx}}][product_type_id]" class="select2 fill-horizontal">
                                                                <option value="" >{{{trans('forms.select')}}}</option>
                                                                @foreach($productTypes as $productTypeId => $productTypeTitle)
                                                                <option value="{{$productTypeId}}" @if($productTypeId == Input::old('product_type.'.$productTypeIdx.'.product_type_id', (array_key_exists('product_type_id', $consultantManagementProductType)) ? $consultantManagementProductType['product_type_id'] : -1 )) selected @endif>{{{$productTypeTitle}}}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                        {{ $errors->first('product_type.'.$productTypeIdx.'.product_type_id', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                </div>
                                                <div class="row">
                                                    <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                        <label class="label">{{{ trans('general.noOfUnits') }}} <span class="required">*</span>:</label>
                                                        <label class="input {{{ $errors->has('product_type.'.$productTypeIdx.'.number_of_unit') ? 'state-error' : null }}}">
                                                            {{ Form::number('product_type['.$productTypeIdx.'][number_of_unit]', Input::old('product_type.'.$productTypeIdx.'.number_of_unit', $consultantManagementProductType['number_of_unit']), ['required' => 'required', 'autofocus' => 'autofocus', 'min' => '0']) }}
                                                        </label>
                                                        {{ $errors->first('product_type.'.$productTypeIdx.'.number_of_unit', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                    <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                        <label class="label">{{{ trans('general.lotSize') }}} <span class="required">*</span>:</label>
                                                        <label class="input {{{ ($errors->has('product_type.'.$productTypeIdx.'.lot_dimension_length') or $errors->has('product_type.'.$productTypeIdx.'.lot_dimension_width') ) ? 'state-error' : null }}}">
                                                            {{ Form::number('product_type['.$productTypeIdx.'][lot_dimension_length]', Input::old('product_type.'.$productTypeIdx.'.lot_dimension_length', number_format($consultantManagementProductType['lot_dimension_length'], 2, '.', '')), ['required' => 'required', 'step' => '0.01', 'style' => 'display:inline;width:120px']) }} <strong>X</strong> {{ Form::number('product_type['.$productTypeIdx.'][lot_dimension_width]', Input::old('product_type.'.$productTypeIdx.'.lot_dimension_width', number_format($consultantManagementProductType['lot_dimension_width'], 2, '.', '')), ['required' => 'required', 'step' => '0.01', 'style' => 'display:inline;width:120px']) }}
                                                        </label>
                                                        {{ $errors->first('product_type.'.$productTypeIdx.'.lot_dimension_length', '<em class="invalid">:message</em>') }}
                                                        {{ $errors->first('product_type.'.$productTypeIdx.'.lot_dimension_width', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                </div>
                                                <div class="row">
                                                    <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                        <label class="label">{{{ trans('general.proposedBuildUpArea') }}} <span class="required">*</span>:</label>
                                                        <label class="input {{{ $errors->has('product_type.'.$productTypeIdx.'.proposed_built_up_area') ? 'state-error' : null }}}">
                                                            {{ Form::number('product_type['.$productTypeIdx.'][proposed_built_up_area]', Input::old('product_type.'.$productTypeIdx.'.proposed_built_up_area', number_format($consultantManagementProductType['proposed_built_up_area'], 2, '.', '')), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                                        </label>
                                                        {{ $errors->first('product_type.'.$productTypeIdx.'.proposed_built_up_area', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                    <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                        <label class="label">{{{ trans('general.proposedAverageSellingPrice') }}} ({{{$currencyCode}}})<span class="required">*</span>:</label>
                                                        <label class="input {{{ $errors->has('product_type.'.$productTypeIdx.'.proposed_average_selling_price') ? 'state-error' : null }}}">
                                                            {{ Form::number('product_type['.$productTypeIdx.'][proposed_average_selling_price]', Input::old('product_type.'.$productTypeIdx.'.proposed_average_selling_price', number_format($consultantManagementProductType['proposed_average_selling_price'], 2, '.', '')), ['required' => 'required', 'step' => '0.01', 'autofocus' => 'autofocus']) }}
                                                        </label>
                                                        {{ $errors->first('product_type.'.$productTypeIdx.'.proposed_average_selling_price', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                </div>
                                            </td>
                                            <td class="text-middle text-center squeeze">
                                                @if($productTypeIdx > 0)
                                                {{ Form::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-md btn-danger deletePhaseBtn', 'title'=>trans('forms.delete')] ) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($phase)) ? $phase->id : -1) }}
                            {{ Form::hidden('cid',  $contract->id) }}
                            {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$contract->id], ['class' => 'btn btn-default']) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
<script src="{{ asset('js/select2tree.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.6/handlebars.min.js"></script>
<script id="document-template" type="text/x-handlebars-template">
    <tr class="phaseRecordRow">
        <td>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('general.productType') }}} <span class="required">*</span>:</label>
                    <label class="input fill-horizontal">
                        <select name="product_type[@{{inputIdx}}][product_type_id]" class="select2 fill-horizontal"></select>
                    </label>
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <label class="label">{{{ trans('general.noOfUnits') }}} <span class="required">*</span>:</label>
                    <label class="input">
                        <input required="required" autofocus="autofocus" name="product_type[@{{inputIdx}}][number_of_unit]" type="number" value="0">
                    </label>
                </section>
                <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <label class="label">{{{ trans('general.lotSize') }}} <span class="required">*</span>:</label>
                    <label class="input">
                        <input required="required" style="display:inline;width:120px" name="product_type[@{{inputIdx}}][lot_dimension_length]" type="number" value="0.00"> <strong>X</strong> <input required="required" style="display:inline;width:120px" name="product_type[@{{inputIdx}}][lot_dimension_width]" type="number" value="0.00">
                    </label>
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <label class="label">{{{ trans('general.proposedBuildUpArea') }}} <span class="required">*</span>:</label>
                    <label class="input">
                        <input required="required" autofocus="autofocus" name="product_type[@{{inputIdx}}][proposed_built_up_area]" type="number" value="0.00">
                    </label>
                </section>
                <section class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <label class="label">{{{ trans('general.proposedAverageSellingPrice') }}} ({{{$currencyCode}}})<span class="required">*</span>:</label>
                    <label class="input">
                        <input required="required" autofocus="autofocus" name="product_type[@{{inputIdx}}][proposed_average_selling_price]" type="number" value="0.00">
                    </label>
                </section>
            </div>
        </td>
        <td class="text-middle text-center squeeze">
            {{ Form::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-md btn-danger deletePhaseBtn', 'title'=>trans('forms.delete')] ) }}
        </td>
    </tr>
</script>
<script type="text/javascript">
$(document).ready(function () {
    $("#subsidiary_id").select2tree({
        expandChildren: true
    });
    
    $("#development_type_id").on("change.select2", function(e) {
        $("#phaseRow").find('.select2').val(null).trigger('change');
        updateProductTypeSelect2();
    });

    $('#addPhaseBtn').on('click',function(e){
        e.preventDefault();
        var source = $("#document-template").html();
        var template = Handlebars.compile(source);

        var inputIdx;
        $('.phaseRecordRow').each(function(i, fields){
            $('select,input', fields).each(function(){
                // Rename first array value from name to group index
                $(this).attr('name', $(this).attr('name').replace(/e\[[^\]]*\]/, 'e['+i+']')); 
            });
            i++;
            inputIdx = i;
        });

        var html = template({
            inputIdx:inputIdx
        });

        $("#phaseRow").append(html).append(function(){
            updateProductTypeSelect2();
        });
    });

    $(document).on('click','.deletePhaseBtn',function(event){
        $(this).closest('.phaseRecordRow').remove();
        $('.phaseRecordRow').each(function(i, fields){
            $('select,input', fields).each(function(){
                $(this).attr('name', $(this).attr('name').replace(/e\[[^\]]*\]/, 'e['+i+']')); 
            });
            i++;
        });
    });
});

function updateProductTypeSelect2(){
    var devTypeId = (parseInt($('#development_type_id').val())) ? parseInt($('#development_type_id').val()) : -1;
    var url = '{{ route("consultant.management.contracts.phase.product.type.list", ":id") }}';
        url = url.replace(':id', devTypeId);
    $("#phaseRow").find('.select2').select2({
        theme: "bootstrap",
        ajax: {
            type: "GET",
            url: url,
            dataType: 'json'
        }
    });
}
</script>
@endsection