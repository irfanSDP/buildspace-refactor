<!DOCTYPE html>
<html>
<head>
    @include('layout.main_partials.head')
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/your_style.css') }}">
    
    <script src="{{ asset('js/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/plugin/bootstrap-slider/bootstrap-slider.min.js') }}"></script>
    <script src="{{ asset('js/plugin/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
    <script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
    <script src="{{ asset('js/app/app.expandable.js') }}"></script>

    <script>
        window.webClaim = {
            urlCountry : '{{{ $urlCountry }}}',
            urlStates : '{{{ $urlStates }}}',
            stateId : '{{{ $stateId }}}',
            urlContractGroupCategories : '{{{ $urlContractGroupCategories }}}',
            urlVendorCategories : '{{{ $urlVendorCategories }}}',
            contractGroupCategoryId : '{{{ $contractGroupCategoryId }}}'
        };
    </script>
    <title>{{{ trans('auth.registration') }}}</title>
</head>
<body>

    <header id="header">
        <div id="logo-group">
            <a href="{{Config::get('app.url', getenv('APPLICATION_URL'))}}" id="logo" style="margin-top: 10px; margin-left: 12px;">
                @if(file_exists(public_path('img/company-logo.png')))
                    <img src="{{{ asset('img/company-logo.png') }}}" alt="{{{ \PCK\MyCompanyProfiles\MyCompanyProfile::all()->first()->name }}}">
                @else
                    <img src="{{{ asset('img/buildspace-login-logo.png') }}}" alt="BuildSpace eProject">
                @endif
            </a>
            <div class="ribbon-banner-small"></div>
        </div>
    </header>

    <div class="container bg-white padded-bottom">

        {{ Form::open(array('route' => 'register.store')) }}

            <!-- Register Company -->

            <div class="padded"></div>

            <div class="well">

                <h2 class="text-left color-bootstrap-success">
                    <i class="far fa-lg fa-id-card"></i> {{{ trans('vendorManagement.loginRequestForm') }}}
                </h2>

                <hr/>

                @if($settings && $settings->include_instructions)
                    <span>{{ nl2br($settings->instructions) }}</span>
                    <hr/>
                @endif

                @include('layout.partials.flash_message')

                <div class="form-group {{{ $errors->has('company') ? 'has-error' : null }}}">
                    <label for="name" class="label">{{{ trans('companies.name') }}}  <span class="required">*</span></label>
                    {{ Form::text('company', Input::old('company'), array('class'=>'form-control', 'placeholder' => trans('companies.name'), 'required', 'autofocus')) }}
                    {{ $errors->first('company', '<em class="invalid">:message</em>') }}
                </div>

                <div class="form-group {{{ $errors->has('address') ? 'has-error' : null }}}">
                    <label for="address" class="label">{{{ trans('companies.address') }}}  <span class="required">*</span></label>
                    {{ Form::textarea('address', Input::old('address'), array('class' => 'form-control', 'required', 'rows' => 4)) }}
                    {{ $errors->first('address', '<em class="invalid">:message</em>') }}
                </div>
                
                <div class="row">
                    <section class="form-group col col-xs-12 col-lg-6 col-md-6 col-sm-6">
                        <label class="label" for="country">{{{ trans('companies.country') }}}  <span class="required">*</span></label>
                        <select name="country_id" id="country" class="fill" style="width:100%;">
                            <option value="">{{{ trans('companies.selectCountry') }}}</option>
                        </select>
                        {{ $errors->first('country_id', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="form-group col col-xs-12 col-lg-6 col-md-6 col-sm-6">
                        <label class="label" for="state">{{{ trans('companies.state') }}}  <span class="required">*</span></label>
                        <select name="state_id" id="state" class="fill" style="width:100%;">
                            <option value="" disabled>{{{ trans('companies.selectState') }}}</option>
                        </select>
                        {{ $errors->first('state_id', '<em class="invalid">:message</em>') }}
                    </section>
                </div>

                <hr />
                @if($vendorManagementModuleEnabled)
                <div class="row">
                    <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                        <label class="label">{{{ trans('contractGroupCategories.vendorGroup') }}} <span class="required">*</span>:</label>
                        <label class="fill-horizontal {{{ $errors->has('contract_group_category_id') ? 'state-error' : null }}}">
                            <select class="fill-horizontal" name="contract_group_category_id" style="width:100%;"></select>
                        </label>
                        {{ $errors->first('contract_group_category_id', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                        <label class="label">{{{ trans('vendorManagement.vendorCategory') }}}:</label>
                        <label class="fill-horizontal {{{ $errors->has('vendor_category_id') ? 'state-error' : null }}}">
                            <select class="fill-horizontal" name="vendor_category_id[]" data-type="dependentSelection" data-dependent-id="second" @if($multipleVendorCategories) multiple @endif style="width:100%;"></select>
                        </label>
                        {{ $errors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                    </section>
                </div>

                <div class="row">
                    <section class="form-group col col-xs-12 col-lg-6 col-md-6 col-sm-12">
                        <label class="label" for="business_entity_type_id">{{{ trans('businessEntityTypes.businessEntityTypes') }}}  <span class="required">*</span></label>
                        <label class="select fill-horizontal">
                            <select name="business_entity_type_id" class="fill" style="width:100%;">
                                <option value="" data-disabled="true" {{{ Input::old('business_entity_type_id') ? '' : 'selected' }}}>{{{ trans('businessEntityTypes.selectEntityType') }}}</option>
                                @foreach($businessEntityTypes as $entityType)
                                    <option value="{{{ $entityType->id }}}" {{{ (Input::old('business_entity_type_id') == $entityType->id) ? 'selected' : '' }}}>{{{ $entityType->name }}}</option>
                                @endforeach
                                @if($allowOtherBusinessEntityTypes)
                                    <option value="other">{{ trans('forms.othersPleaseSpecify') }}</option>
                                @endif
                            </select>
                        </label>
                        {{ $errors->first('business_entity_type_id', '<em class="invalid">:message</em>') }}
                    </section>

                    <section class="form-group col col-xs-12 col-lg-6 col-md-6 col-sm-12 {{{ $errors->has('business_entity_type_other') ? 'has-error' : null }}}" style="display:none;" id="business_entity_type_other_section">
                        <label class="label">{{{ trans('forms.other') }}}  <span class="required">*</span></label>
                        {{ Form::text('business_entity_type_other', Input::old('business_entity_type_other'), array('class' => 'form-control', 'placeholder' => trans('forms.other'))) }}
                        {{ $errors->first('business_entity_type_other', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
                @else
                <div class="row">
                    <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                        <label class="label">{{{ trans('contractGroupCategories.userGroup') }}} <span class="required">*</span>:</label>
                        <label class="fill-horizontal {{{ $errors->has('contract_group_category_id') ? 'state-error' : null }}}">
                            <select class="fill-horizontal" name="contract_group_category_id" style="width:100%;"></select>
                        </label>
                        {{ $errors->first('contract_group_category_id', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
                @endif

                <div class="row">
                    <section class="form-group col col-xs-12 col-lg-4 col-md-4 col-sm-12 {{{ $errors->has('main_contact') ? 'has-error' : null }}}">
                        <label class="label" for="main_contact">{{{ trans('companies.mainContact') }}}  <span class="required">*</span></label>
                        {{ Form::text('main_contact', Input::old('main_contact'), array('class' => 'form-control', 'placeholder' => trans('companies.mainContact'), 'required')) }}
                        {{ $errors->first('main_contact', '<em class="invalid">:message</em>') }}
                    </section>

                    <section class="form-group col col-xs-12 col-lg-4 col-md-4 col-sm-12 {{{ $errors->has('reference_no') ? 'has-error' : null }}}">
                        <label class="label" for="reference_no">{{{ trans('companies.referenceNumber') }}} <span class="required">*</span></label>
                        <span style="font-family: monospace">
                            {{ Form::text('reference_no', Input::old('reference_no'), array('class' => 'form-control', 'maxlength' => 20, 'required', 'placeholder' => trans('companies.referenceNumber'))) }}
                        </span>
                        {{ $errors->first('reference_no', '<em class="invalid">:message</em>') }}
                    </section>

                    <section class="form-group col col-xs-12 col-lg-4 col-md-4 col-sm-12 {{{ ($errors->has('tax_registration_no') || $errors->has('tax_registration_id')) ? 'has-error' : null }}}">
                        <label class="label" for="reference_no">{{{ trans('companies.taxRegistrationNumber') }}} </label>
                        <span style="font-family: monospace">
                            {{ Form::text('tax_registration_no', Input::old('tax_registration_no'), array('class' => 'form-control', 'maxlength' => 20, 'placeholder' => trans('companies.taxRegistrationNumber'))) }}
                        </span>
                        <?php
                            $errorMessage = $errors->first('tax_registration_no');
                            if(empty($errorMessage)) $errorMessage = $errors->first('tax_registration_id');
                        ?>
                        <em class="invalid">{{{ $errorMessage }}}</em>
                    </section>
                </div>

                <div class="row">
                    <div class="form-group col col-xs-12 col-lg-4 col-md-4 col-sm-4 {{{ $errors->has('company_email') ? 'has-error' : null }}}">
                        <label class="label" for="email">{{{ trans('companies.email') }}} <span class="required">*</span></label>
                        {{ Form::email('company_email', Input::old('company_email'), array('class' => 'form-control', 'required', 'placeholder' => trans('companies.email'))) }}
                        {{ $errors->first('company_email', '<em class="invalid">:message</em>') }}
                    </div>

                    <div class="form-group col col-xs-12 col-lg-4 col-md-4 col-sm-4 {{{ $errors->has('telephone_number') ? 'has-error' : null }}}">
                        <label class="label" for="telephone_number">{{{ trans('companies.telephone') }}} <span class="required">*</span></label>
                        {{ Form::text('telephone_number', Input::old('telephone_number'), array('class' => 'form-control', 'required', 'placeholder' => trans('companies.telephone'))) }}
                        {{ $errors->first('telephone_number', '<em class="invalid">:message</em>') }}
                    </div>

                    <div class="form-group col col-xs-12 col-lg-4 col-md-4 col-sm-4 {{{ $errors->has('fax_number') ? 'has-error' : null }}}">
                        <label class="label" for="fax_number">{{{ trans('companies.fax') }}}</label>
                        {{ Form::text('fax_number', Input::old('fax_number'), array('class' => 'form-control', 'placeholder' => trans('companies.fax'))) }}
                        {{ $errors->first('fax_number', '<em class="invalid">:message</em>') }}
                    </div>
                </div>

            </div>

           <!-- Register Company End -->

            <!-- Register User -->

            <h2 class="text-center color-bootstrap-success"></h2>

            <div class="well">

                <h2 class="text-left color-bootstrap-success"><i class="fa fa-lg fa-user"></i> {{{ trans('users.userDetails') }}}</h2>

                <hr/>

                <div class="form-group {{{ $errors->has('user_name') ? 'has-error' : null }}}">
                    <label class="label" for="name">{{{ trans('users.name') }}}  <span class="required">*</span></label>
                    {{ Form::text('user_name', Input::old('user_name'), array('class'=>'form-control', 'placeholder' => trans('users.name'), 'required', 'autofocus')) }}
                    {{ $errors->first('user_name', '<em class="invalid">:message</em>') }}
                </div>

                <div class="form-group {{{ $errors->has('user_contact_number') ? 'has-error' : null }}}">
                    <label class="label" for="contact_number">{{{ trans('users.contactNumber') }}}  <span class="required">*</span></label>
                    {{ Form::text('user_contact_number', Input::old('user_contact_number'), array('class' => 'form-control', 'placeholder' => trans('users.contactNumber'), 'required')) }}
                    {{ $errors->first('user_contact_number', '<em class="invalid">:message</em>') }}
                </div>

                <div class="form-group {{{ $errors->has('user_email') ? 'has-error' : null }}}">
                    <label class="label" for="email">{{{ trans('users.email') }}}  <span class="required">*</span></label>
                    {{ Form::email('user_email', Input::old('user_email'), array('class' => 'form-control', 'placeholder' => trans('users.email'), 'required')) }}
                    {{ $errors->first('user_email', '<em class="invalid">:message</em>') }}
                </div>
            </div>

           <!-- Register User End -->

            <div data-id="terms-and-conditions" class="well {{{ $errors->has('agree-to-terms-and-conditions') ? 'border-crimson' : ''}}}">
                <div class="row">
                    <div class="col col-md-12">
                        @if(empty(getenv('PRIVACY_POLICY')) || empty(getenv('TERMS_OF_USE')))
                            <button type="button" class="btn btn-xs btn-info" data-action="expandToggle" data-target="terms-of-use"><i class="fa fa-file-signature"></i> {{ trans('forms.termsOfUse') }}</button>
                            <br/>
                            <br/>
                            <div class="well bg-color-white" data-type="expandable" data-id="terms-of-use" hidden>
                                @include('registration.termsOfUse')
                            </div>
                            <input type="checkbox" value="1" name="agree-to-terms-and-conditions"/>
                            <label>{{ trans('forms.checkAgreeUseTerms') }}</label>
                        @else
                            <input type="checkbox" value="1" name="agree-to-terms-and-conditions"/>
                            <label>{{ trans('forms.agreeToTermsofUse', array('termsOfUse' => getenv('TERMS_OF_USE'))) }}</label>
                            <br>
                            <input type="checkbox" value="2" name="agree-to_privacy-policy"/>
                            <label>{{ trans('forms.agreeToPrivacyPolicy', array('privacyPolicy' => getenv('PRIVACY_POLICY'))) }}</label>
                        @endif
                        @if($settings && $settings->include_disclaimer)
                            <br/>
                            <br/>
                            <button type="button" class="btn btn-xs btn-info" data-action="expandToggle" data-target="disclaimer"><i class="fa fa-file-signature"></i> {{ trans('vendorManagement.vendorDisclaimer') }}</button>
                            <br/>
                            <br/>
                            <div class="well bg-color-white" data-type="expandable" data-id="disclaimer" hidden>
                                {{ nl2br($settings->disclaimer) }}
                            </div>
                            <input type="checkbox" value="1" name="agree-to-disclaimer"/>
                            <label>{{ trans('vendorManagement.agreeToDisclaimer') }}</label>
                        @endif
                    </div>
                </div>
            </div>
            {{ $errors->first('agree-to-terms-and-privacy', '<em class="invalid">:message</em>') }}
            <div class="row">
                <div class="col col-md-12">
                    <button type="submit" class="btn btn-primary pull-right" disabled><i class="fa fa-save" aria-hidden="true"></i> {{{ trans('forms.submit') }}}</button>
                </div>
            </div>

        {{ Form::close() }}

    </div>
</body>
</html>

<script>
    $('select').select2({
        theme: 'bootstrap',
        width: 'resolve'
    });

    // Disable submitting on submit
    // to prevent the user from sending multiple requests at a time.
    $(document).on('submit', 'form', function(){
        $('button[type=submit]').prop('disabled', true);
    });

    $('option[data-disabled=true]').each(function()
    {
        $(this).prop('disabled', true);
    });

    $('input[type=checkbox][name=agree-to-terms-and-conditions],[name=agree-to_privacy-policy],[name=agree-to-disclaimer]').on('change', function(){

        var termsAndConditionsCheckBox = $('input[type=checkbox][name=agree-to-terms-and-conditions]');
        var termsAndConditionsPass = termsAndConditionsCheckBox.length ? termsAndConditionsCheckBox.prop('checked') : true;

        var privacyPolicyCheckBox = $('input[type=checkbox][name=agree-to_privacy-policy]');
        var privacyPolicyPass = privacyPolicyCheckBox.length ? privacyPolicyCheckBox.prop('checked') : true;

        var disclaimerCheckBox = $('input[type=checkbox][name=agree-to-disclaimer]');
        var disclaimerPass = disclaimerCheckBox.length ? disclaimerCheckBox.prop('checked') : true;

        if(termsAndConditionsPass && privacyPolicyPass && disclaimerPass){
            $('button[type=submit]').prop('disabled', false);
        }
        else {
            $('button[type=submit]').prop('disabled', true);
        }
    });

    @if($vendorManagementModuleEnabled)
        $('select[name="business_entity_type_id"]').on('change', function(e) {
            e.preventDefault();

            var selectedValue = $(this).val();

            if(selectedValue == 'other') {
                $('#business_entity_type_other_section').show();
            } else {
                $('#business_entity_type_other_section').hide();
            }
        });
    @endif

    var dependentSelection = $.extend({}, DependentSelection);
    dependentSelection.setUrls({first: webClaim.urlContractGroupCategories, second: webClaim.urlVendorCategories});
    dependentSelection.setForms({first: $('form [name=contract_group_category_id]'), second: $('form [name="vendor_category_id[]"]')});
    dependentSelection.setSelectedIds({first: webClaim.contractGroupCategoryId, second: {{json_encode($vendorCategoryId)}}});
    dependentSelection.setPreSelectOnLoad({first: true, second: false});
    dependentSelection.init();
</script>