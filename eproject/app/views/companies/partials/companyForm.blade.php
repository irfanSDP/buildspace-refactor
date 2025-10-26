@if(isset($company))
<input type="hidden" id="initial_contract_group_category_id" name="initial_contract_group_category_id" value="{{ $company->contract_group_category_id }}">
@endif
    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('companies.name') }}}<span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                    {{ Form::text('name', Input::old('name'), array('required')) }}
                </label>
                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('companies.address') }}}<span class="required">*</span>:</label>
                <label class="textarea {{{ $errors->has('address') ? 'state-error' : null }}}">
                    {{ Form::textarea('address', Input::old('address'), array('required', 'rows' => 3, 'class' => 'rounded')) }}
                </label>
                {{ $errors->first('address', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                <label class="label">{{{ trans('companies.country') }}} <span class="required">*</span>:</label>
                <label class="fill-horizontal">
                    <select class="form-control select2" name="country_id" id="country" data-action="filter" data-select-width="180px"></select>
                </label>
                {{ $errors->first('country_id', '<em class="invalid">:message</em>') }}
            </section>
            <section class="form-group col col-xs-12 col-md-12 col-lg-6">
                <label class="label">{{{ trans('companies.state') }}} <span class="required">*</span>:</label>
                <label class="fill-horizontal">
                    <select class="form-control select2" name="state_id" id="state" data-action="filter" data-select-width="180px">
                        <option value="" disabled>{{{ trans('companies.selectState') }}}</option>   
                    </select>
                </label>
                {{ $errors->first('state_id', '<em class="invalid">:message</em>') }}
            </section>
        </div>
    </fieldset>
    
    <hr class="simple"/>

    <fieldset>
        @if(isset($vendorManagementModuleEnabled) && $vendorManagementModuleEnabled)
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
                    <select name="business_entity_type_id" class="fill select2" style="width:100%;">
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
            <section class="col col-4">
                <label class="label">{{{ trans('companies.mainContact') }}}<span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('main_contact') ? 'state-error' : null }}}">
                    {{ Form::text('main_contact', Input::old('main_contact'), array('required')) }}
                </label>
                {{ $errors->first('main_contact', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-4">
                <label class="label">{{{ trans('companies.referenceNumber') }}}<span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('reference_no') ? 'state-error' : null }}}">
                    {{ Form::text('reference_no', Input::old('reference_no'), array('required')) }}
                </label>
                {{ $errors->first('reference_no', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-4">
                <label class="label">{{{ trans('companies.taxRegistrationNumber') }}}:</label>
                <label class="input {{{ ($errors->has('tax_registration_no') || $errors->has('tax_registration_id')) ? 'state-error' : null }}}">
                    {{ Form::text('tax_registration_no', Input::old('tax_registration_no')) }}
                </label>
                {{ $errors->first('tax_registration_id', '<em class="invalid">:message</em>') }}
            </section>
        </div>

        <div class="row">
            <section class="col col-4">
                <label class="label" for="email">{{{ trans('companies.email') }}} <span class="required">*</span></label>
                <label class="input {{{ $errors->has('email') ? 'state-error' : null }}}">
                    {{ Form::email('email', Input::old('email'), array('required')) }}
                </label>
                {{ $errors->first('email', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-4">
                <label class="label">{{{ trans('companies.telephone') }}}<span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('telephone_number') ? 'state-error' : null }}}">
                    {{ Form::text('telephone_number', Input::old('telephone_number'), array('required')) }}
                </label>
                {{ $errors->first('telephone_number', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-4">
                <label class="label">{{{ trans('companies.fax') }}}:</label>
                <label class="input {{{ $errors->has('fax_number') ? 'state-error' : null }}}">
                    {{ Form::text('fax_number', Input::old('fax_number')) }}
                </label>
                {{ $errors->first('fax_number', '<em class="invalid">:message</em>') }}
            </section>
        </div>
    </fieldset>

    <hr class="simple"/>

    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('companies.attachments') }}}:</label>

                @include('file_uploads.partials.upload_file_modal')
            </section>
        </div>
    </fieldset>