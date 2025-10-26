<?php
$companyErrors = $errors->getBag('company');
?>
<?php use PCK\Companies\Company; ?>
<?php use PCK\BuildingInformationModelling\BuildingInformationModellingLevel; ?>
{{ Form::open(['route' => ['vendorProfile.company.details.store'], 'class' => 'smart-form', 'method' => 'post', 'id' => 'company-details-form']) }}
<div class="row">
    <section class="col col-xs-9 col-md-9 col-lg-9">
        <h5>{{ trans('companies.generalInformation') }}</h5>
    </section>
    <section class="col col-xs-3 col-md-3 col-lg-3">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-md header-btn">
                <i class="far fa-save"></i> {{{ trans('forms.save') }}}
            </button>
            @if(isset($company) && empty($company->deactivated_at))
            <button type="button" data-toggle="modal" data-target="#vendorDeactivateModal" class="btn btn-danger btn-md header-btn">
                <i class="far fa-times-circle"></i> {{{ trans('vendorManagement.deactivate') }}}
            </button>
            @endif
            @if(isset($company) && !empty($company->deactivated_at))
            <button type="button" data-toggle="modal" data-target="#vendorActivateModal" class="btn btn-success btn-md header-btn">
                <i class="far fa-check-circle"></i> {{{ trans('vendorManagement.activate') }}}
            </button>
            @endif
        </div>
    </section>
</div>

<hr class="simple"/>

<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('companies.companyName') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('name') ? 'state-error' : null }}}">
            {{ Form::text('name', Input::old('name', isset($company) ? $company->name : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('name', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('vendorManagement.vendorCode') }}} :</label>
        <p>{{isset($company) ? $company->getVendorCode() : null}}</p>
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('vendorManagement.activationDate') }}} :</label>
        <label class="input {{{ $companyErrors->has('activation_date') ? 'state-error' : null }}}">
            <?php
            $activationDate = Input::old('activation_date', (isset($company)) ? $company->activation_date : null);
            $activationDate = ($activationDate) ? date('Y-m-d',strtotime($activationDate)) : null;
            ?>
            <input min="2000-01-01" name="activation_date" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.activationDate') }}}" value="{{ $activationDate }}"/>
        </label>
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('vendorManagement.expiryDate') }}} :</label>
        <label class="input {{{ $companyErrors->has('expiry_date') ? 'state-error' : null }}}">
            <?php
            $expiryDate = Input::old('expiry_date', (isset($company)) ? $company->expiry_date : null);
            $expiryDate = ($expiryDate) ? date('Y-m-d',strtotime($expiryDate)) : null;
            ?>
            <input min="2000-01-01" name="expiry_date" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.expiryDate') }}}" value="{{ $expiryDate }}"/>
        </label>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('companies.address') }}} <span class="required">*</span>:</label>
        <label class="textarea {{{ $companyErrors->has('address') ? 'state-error' : null }}}">
            {{ Form::textarea('address', Input::old('address', isset($company) ? $company->address : null), ['required' => 'required', 'rows' => 3]) }}
        </label>
        {{ $companyErrors->first('address', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label">{{{ trans('projects.country') }}} <span class="required">*</span>:</label>
        <label class="fill-horizontal {{{ $companyErrors->has('country_id') ? 'state-error' : null }}}">
            <select class="select2 fill-horizontal" name="country_id" id="country"></select>
        </label>
        {{ $companyErrors->first('country_id', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label">{{{ trans('projects.state') }}} <span class="required">*</span>:</label>
        <label class="fill-horizontal {{{ $companyErrors->has('state_id') ? 'state-error' : null }}}">
            <select class="select2 fill-horizontal" name="state_id" id="state"></select>
        </label>
        {{ $companyErrors->first('state_id', '<em class="invalid">:message</em>') }}
    </section>
</div>

<hr class="simple"/>

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

@if($company->isContractor())
<hr class="simple"/>

<div class="row">
    <section class="col col-xs-4">
        <label class="label">{{ trans('companies.cidbGrade') }} <span class="required">*</span></label>
        <select name="cidb_grade" class="select2">
            <option value="">{{ trans('general.selectAnOption') }}</option>
            @foreach($cidb_grades as $cidb_grade)
                @if($company->cidb_grade == $cidb_grade->id)
                    <option value="{{{$cidb_grade->id}}}" selected >{{{ $cidb_grade->grade }}}</option>
                @else
                    <option value="{{{$cidb_grade->id}}}">{{{ $cidb_grade->grade }}}</option>
                @endif
            @endforeach
        </select>
        {{ $companyErrors->first('cidb_grade', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-5">
        <label class="label">{{ trans('companies.cidbCode') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a class="btn btn-link btn-sm" data-toggle="modal" data-target="#viewCidbCodesModal">
            <i class="fa fa-eye"></i>
            {{{ trans('companies.viewCidbCode') }}}
            </a>
        </label>
        <select name="cidb_code_id[]" id="cidb_code_id" class="form-control select2" multiple>
            @foreach($cidbCodes as $cidbCode)
            <?php $selected = in_array($cidbCode->id, $selectedCidbCodeIds); ?>
                @if($cidbCode->parent && !$cidbCode->child)
                    <option @if($selected) selected @endif disabled value="{{{ $cidbCode->id }}}">
                        {{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                    </option>
                @elseif($cidbCode->parent && $cidbCode->child)
                    <option @if($selected) selected @endif disabled value="{{{ $cidbCode->id }}}">
                        &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                    </option>
                @elseif($cidbCode->child)
                    <option @if($selected) selected @endif value="{{{ $cidbCode->id }}}">
                        &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                    </option>
                @else($cidbCode->subChild)
                    <option @if($selected) selected @endif value="{{{ $cidbCode->id }}}">
                        &nbsp;&nbsp;&nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                    </option>
                @endif
            @endforeach
    </select> 
    {{ $companyErrors->first('cidb_code_id', '<em class="invalid">:message</em>') }}
    </section>
</div>


<hr class="simple"/>
@endif
@if($company->isConsultant())
<hr class="simple"/>

<div class="row">
    <section class="col col-xs-4">
        <label class="label">{{ trans('companies.bimLevel') }} <span class="required">*</span></label>
        <select name="bim_level_id" class="select2">
            <option value="">{{ trans('general.selectAnOption') }}</option>
            @foreach(BuildingInformationModellingLevel::getBIMLevelSelections() as $id => $name)
            <?php $selected = ($company->bim_level_id == $id) ? 'selected' : null; ?>
            <option value="{{{ $id }}}" {{{ $selected }}}>{{{ $name }}}</option>
            @endforeach
        </select>
        {{ $companyErrors->first('bim_level_id', '<em class="invalid">:message</em>') }}
    </section>
</div>

<hr class="simple"/>
@endif

<div class="row">
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('companies.mainContact') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('main_contact') ? 'state-error' : null }}}">
            {{ Form::text('main_contact', Input::old('main_contact', isset($company) ? $company->main_contact : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('main_contact', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('companies.referenceNumber') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('reference_no') ? 'state-error' : null }}}">
            {{ Form::text('reference_no', Input::old('reference_no', isset($company) ? $company->reference_no : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('reference_no', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('companies.taxRegistrationNumber') }}} :</label>
        <label class="input {{{ $companyErrors->has('tax_registration_no') ? 'state-error' : null }}}">
            {{ Form::text('tax_registration_no', Input::old('tax_registration_no', isset($company) ? $company->tax_registration_no : null), ['autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('tax_registration_no', '<em class="invalid">:message</em>') }}
    </section>
</div>

<div class="row">
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('companies.email') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('email') ? 'state-error' : null }}}">
            {{ Form::email('email', Input::old('email', isset($company) ? $company->email : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('email', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('companies.telephone') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('telephone_number') ? 'state-error' : null }}}">
            {{ Form::text('telephone_number', Input::old('telephone_number', isset($company) ? $company->telephone_number : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('telephone_number', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('companies.fax') }}} :</label>
        <label class="input {{{ $companyErrors->has('fax_number') ? 'state-error' : null }}}">
            {{ Form::text('fax_number', Input::old('fax_number', isset($company) ? $company->fax_number : null), ['autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('fax_number', '<em class="invalid">:message</em>') }}
    </section>
</div>

<hr class="simple"/>

<div class="row">
    <section class="col col-xs-4">
        <label class="label">{{ trans('vendorManagement.companyStatus') }} <span class="required">*</span></label>
        <select name="company_status" class="select2">
            <option value="">{{ trans('general.selectAnOption') }}</option>
            @foreach($companyStatusDescriptions as $identifier => $description)
            <?php $selected = ($company->company_status == $identifier) ? 'selected' : null; ?>
            <option value="{{{ $identifier }}}" {{{ $selected }}}>{{{ $description }}}</option>
            @endforeach
        </select>
        {{ $errors->first('company_status', '<em class="invalid">:message</em>') }}
    </section>
</div>

<div class="row">
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('vendorManagement.bumiputeraEquity') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('bumiputera_equity') ? 'state-error' : null }}}">
            {{ Form::text('bumiputera_equity', Input::old('bumiputera_equity', isset($company) ? $company->bumiputera_equity : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('bumiputera_equity', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('vendorManagement.nonBumiputeraEquity') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $companyErrors->has('non_bumiputera_equity') ? 'state-error' : null }}}">
            {{ Form::text('non_bumiputera_equity', Input::old('non_bumiputera_equity', isset($company) ? $company->non_bumiputera_equity : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('non_bumiputera_equity', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">{{{ trans('vendorManagement.foreignerEquity') }}} :</label>
        <label class="input {{{ $companyErrors->has('foreigner_equity') ? 'state-error' : null }}}">
            {{ Form::text('foreigner_equity', Input::old('foreigner_equity', isset($company) ? $company->foreigner_equity : null), ['autofocus' => 'autofocus']) }}
        </label>
        {{ $companyErrors->first('foreigner_equity', '<em class="invalid">:message</em>') }}
    </section>
</div>
{{ Form::hidden('id', (isset($company)) ? $company->id : -1) }}
{{ Form::close() }}

@if(isset($company) && empty($company->deactivated_at))
<div class="modal fade" id="vendorDeactivateModal" tabindex="-1" aria-labelledby="vendorDeactivateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header alert-danger">
                <h4 class="modal-title">{{ trans('vendorManagement.deactivate') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            {{ Form::open(['route' => ['vendorProfile.deactivate', $company->id], 'class' => 'smart-form', 'method' => 'post']) }}
            <div class="modal-body">
                <fieldset>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.companyName') }}:</dt>
                                <dd>{{{ $company->name }}}</dd>
                                <dt>{{ trans('companies.referenceNumber') }}:</dt>
                                <dd>{{{ $company->reference_no }}}</dd>
                            </dl>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div class="well">
                                <ol style="padding-left:8px;">
                                    <li><strong>{{{trans('vendorManagement.expiryDate')}}} :</strong> The date that this vendor will be expired.</li>
                                    <li><strong>{{{trans('vendorManagement.deactivationDate')}}} :</strong> The date that this vendor will get deactivated after a grace period starting from the Expiry Date.</li>
                                </ol>
                            </div>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label">{{{ trans('vendorManagement.expiryDate') }}} :</label>
                            <label class="input">
                                <?php
                                $expiryDate = ($company->expiry_date) ? date('Y-m-d',strtotime($company->expiry_date)) : date('Y-m-d');
                                ?>
                                <input min="2000-01-01" name="expiry_date" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.expiryDate') }}}" value="{{ $expiryDate }}" required/>
                            </label>
                        </section>
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label">{{{ trans('vendorManagement.deactivationDate') }}} :</label>
                            <label class="input">
                                <?php
                                $deactivationDate = ($company->deactivation_date) ? date('Y-m-d',strtotime($company->deactivation_date)) : date('Y-m-d', strtotime($company->calculateDeactivationDate()));
                                ?>
                                <input min="2000-01-01" name="deactivation_date" type="date" class="form-control" placeholder="{{{ trans('vendorManagement.deactivationDate') }}}" value="{{ $deactivationDate }}" required/>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <fieldset>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div class="pull-right">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('forms.cancel')}}</button>
                                <button type="submit" class="btn btn-danger"><i class="far fa-times-circle"></i> {{trans('vendorManagement.deactivate')}}</button>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>
            {{ Form::hidden('id', $company->id) }}
            {{ Form::close() }}
        </div>
    </div>
</div>
@endif

@if(isset($company) && !empty($company->deactivated_at))
<div class="modal fade" id="vendorActivateModal" tabindex="-1" aria-labelledby="vendorActivateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header alert-success">
                <h4 class="modal-title">{{ trans('vendorManagement.activate') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            {{ Form::open(['route' => ['vendorProfile.activate', $company->id], 'class' => 'smart-form', 'method' => 'post']) }}
            <div class="modal-body">
                <fieldset>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.companyName') }}:</dt>
                                <dd>{{{ $company->name }}}</dd>
                                <dt>{{ trans('companies.referenceNumber') }}:</dt>
                                <dd>{{{ $company->reference_no }}}</dd>
                                <dt>{{ trans('vendorManagement.expiryDate') }}:</dt>
                                <dd>{{ date('d/m/Y',strtotime($company->expiry_date)) }}</dd>
                                <dt>{{ trans('vendorManagement.deactivationDate') }}:</dt>
                                <dd>{{ date('d/m/Y',strtotime($company->deactivation_date)) }}</dd>
                            </dl>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <fieldset>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div class="pull-right">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('forms.cancel')}}</button>
                                <button type="submit" class="btn btn-success"><i class="far fa-check-circle"></i> {{trans('vendorManagement.activate')}}</button>
                            </div>
                        </section>
                    </div>
                </fieldset>
            </div>
            {{ Form::hidden('id', $company->id) }}
            {{ Form::close() }}
        </div>
    </div>
</div>
@endif
@include('vendor_registration.vendor_details.partials.view_cidb_codes_modal')
