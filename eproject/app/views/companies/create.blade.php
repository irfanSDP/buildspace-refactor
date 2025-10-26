@extends('layout.main')
<?php use PCK\BusinessEntityType\BusinessEntityType; ?>
@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('companies', 'Companies', array()) }}</li>
        <li>{{ trans('companies.addNewCompany') }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-edit"></i> {{ trans('companies.addNewCompany') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2>{{ trans('companies.addNewCompany') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'company-form')) }}
                            @include('companies.partials.companyForm')

                            <footer>
                                {{ link_to_route('companies', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.add'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
    <script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#company-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });

            var dependentSelection = $.extend({}, DependentSelection);
            dependentSelection.setUrls({first: webClaim.urlContractGroupCategories, second: webClaim.urlVendorCategories});
            dependentSelection.setForms({first: $('form [name=contract_group_category_id]'), second: $('form [name="vendor_category_id[]"]')});
            dependentSelection.setSelectedIds({first: webClaim.contractGroupCategoryId, second: webClaim.vendorCategoryId});
            dependentSelection.setPreSelectOnLoad({first: true, second: false});
            dependentSelection.init();

            $('select[name="business_entity_type_id"]').on('change', function(e) {
                e.preventDefault();

                var selectedValue = $(this).val();

                if(selectedValue == 'other') {
                    $('#business_entity_type_other_section').show();
                } else {
                    $('#business_entity_type_other_section').hide();
                }
            });

            if(webClaim.businessEntityTypeId != null) {
                $('select[name="business_entity_type_id"]').val(webClaim.businessEntityTypeId).trigger('change');
            }

            if(webClaim.businessEntityTypeId != null && webClaim.allowOtherBusinessEntityTypes && webClaim.businessEntityTypeId == "{{ BusinessEntityType::OTHER }}") {
                $('select[name="business_entity_type_id"]').val('other').trigger('change');
                $('[name="business_entity_type_other"]').val(webClaim.businessEntityTypeName);
            }
        });
    </script>
@endsection