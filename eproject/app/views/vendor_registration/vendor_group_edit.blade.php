@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.changeVendorGroup') }}}</li>
    </ol>

    <span class="ribbon-button-alignment pull-right">
        <span class="label label-success">{{{ $currentUser->company->vendorRegistration->status_text }}}</span>
        <span class="label label-info">{{{ $currentUser->company->vendorRegistration->submission_type_text }}}</span>
    </span>
@endsection
<?php
    $companyErrors = $errors->getBag('company');
    $vendorCategoryIds = Input::old('vendor_category_id', (isset($company)) ? $company->vendorCategories()->lists('vendor_category_id') : []);
?>
@section('content')
    <div class="jarviswidget" id="wid-id-8" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{{ trans('vendorManagement.changeVendorGroup') }}}</h2>				
        </header>
        <div>
            <div class="widget-body no-padding">
                <form action="{{ route('vendors.vendorRegistration.vendorGroup.update') }}" method="post" class="smart-form" id="change-vendor-group-form">
                    <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                    <fieldset>
                        <div class="row">
                            <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                                <label class="label">{{{ trans('contractGroupCategories.vendorGroup') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $companyErrors->has('contract_group_category_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal" name="contract_group_category_id" style="width:100%;"></select>
                                </label>
                                {{ $companyErrors->first('contract_group_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.vendorCategory') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $companyErrors->has('vendor_category_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal" name="vendor_category_id[]" data-type="dependentSelection" data-dependent-id="second" @if($multipleVendorCategories) multiple @endif style="width:100%;"></select>
                                </label>
                                {{ $companyErrors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        <button type="submit" class="btn btn-primary">{{ trans('forms.save') }}</button>
                    </footer>
                </form>						
            </div>
        </div>
    </div>

    @include('templates/yesNoModal', [
        'modalId' => 'changeVendorGroupYesNoModal',
        'title'   => trans('vendorProfile.changeVendorGroupTitle'),
        'message' => trans('vendorProfile.changeVendorGroupWarning'),
        'size'    => 'lg',
    ])
@endsection

@section('js')
    <script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
    <script>
        $(document).ready(function() {
            var dependentSelection = $.extend({}, DependentSelection);
            dependentSelection.setUrls({first: webClaim.urlContractGroupCategories, second: webClaim.urlVendorCategories});
            dependentSelection.setForms({first: $('form [name=contract_group_category_id]'), second: $('form [name="vendor_category_id[]"]')});
            dependentSelection.setSelectedIds({first: {{Input::old('contract_group_category_id', $user->company->contract_group_category_id)}}, second: {{json_encode($vendorCategoryIds)}}});
            dependentSelection.setPreSelectOnLoad({first: true, second: false});
            dependentSelection.init();

            $('#change-vendor-group-form').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serializeArray();
                var data = {};

                formData.forEach(function(item, index) {
                    data[item.name] = item.value.trim();
                });

                var contractGroupCategoryId = data['contract_group_category_id'].trim();

                if("{{ $company->contract_group_category_id }}".trim() !== contractGroupCategoryId) {
                    $('#changeVendorGroupYesNoModal').modal('show');

                    return false;
                }

                $(this)[0].submit();
            });

            $('#changeVendorGroupYesNoModal [data-action="actionYes"]').on('click', function(e) {
                $('#change-vendor-group-form')[0].submit();
            });
        });
    </script>
@endsection