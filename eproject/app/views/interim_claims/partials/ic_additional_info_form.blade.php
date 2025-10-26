<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">

<?php $grossValuesOfWorks = Input::old('gross_values_of_works', ( $previousInfo ) ? $previousInfo->gross_values_of_works : null); ?>

<div class="widget-body no-padding">
    {{ Form::open(array('route' => array('ic.additional_info_create', $ic->project->id, $ic->id), 'class' => 'smart-form', 'id' => 'icForm')) }}
    <fieldset>
        <section>
            <label class="label">Reference<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('reference') ? 'state-error' : null }}}">
                {{ Form::text('reference', Input::old('reference', ($previousInfo) ? $previousInfo->reference : $ic->claim_no), array('required' => 'required')) }}
            </label>
            {{ $errors->first('reference', '<em class="invalid">:message</em>') }}
        </section>

        <section>
            <label class="label">Date<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('date') ? 'state-error' : null }}}">
                {{ Form::text('date', Input::old('date', ($previousInfo) ? $ic->project->getProjectTimeZoneTime($previousInfo->date) : date(\Config::get('dates.submission_date_formatting'))), array('class' => 'finishdate')) }}
            </label>
            {{ $errors->first('date', '<em class="invalid">:message</em>') }}
        </section>

        <section>
            <label class="label">To:</label>
            @if ( $ic->project->subsidiary->name )
                <address>
                    <strong>{{{ $ic->project->subsidiary->name }}}</strong><br>
                </address>
            @else
                -
            @endif
        </section>

        <section>
            <label class="label">Works:</label>
            {{{ $ic->project->title }}}
        </section>

        <section>
            <label class="label">Contract Sum ({{{ $ic->project->modified_currency_code }}}):</label>
            <label class="input">
                {{{ number_format($ic->project->pam2006Detail->contract_sum, 2) }}}
                {{ Form::hidden('contract_sum', $ic->project->pam2006Detail->contract_sum, array('class' => 'interim_claim-contract_sum'))  }}
            </label>
        </section>

        <section>
            <label class="label">Nett Addition/Omission ({{{ $ic->project->modified_currency_code }}})<span
                        class="required">*</span>:</label>
            <label class="input {{{ $errors->has('nett_addition_omission') ? 'state-error' : null }}}">
                {{ Form::text('nett_addition_omission', Input::old('nett_addition_omission', ($previousInfo) ? $previousInfo->nett_addition_omission : null), array('required' => 'required', 'class' => 'interim_claim-nett_addition-omission')) }}
            </label>
            {{ $errors->first('nett_addition_omission', '<em class="invalid">:message</em>') }}
        </section>

        <section>
            <label class="label">Nett Addition/Omission Attachment(s):</label>

            <div id="nettAdditionOmissionFileUpload">
                <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                <div class="row fileupload-buttonbar" style="margin: 0;">
                    <div>
                        <!-- The fileinput-button span is used to style the file input field as button -->
							<span class="btn btn-sm btn-success fileinput-button">
								<i class="glyphicon glyphicon-plus"></i>
								<span>{{ trans('files.addFiles') }}</span>
								<input type="file" name="file" multiple>
							</span>
                        <button class="btn btn-sm btn-primary start">
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>{{ trans('files.startUpload') }}</span>
                        </button>
                        <button class="btn btn-sm btn-warning cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>{{ trans('files.cancelUpload') }}</span>
                        </button>

                        <!-- The global file processing state -->
                        <span class="fileupload-process"></span>
                    </div>
                </div>
                <!-- The global progress state -->
                <div class="fileupload-progress fade">
                    <!-- The global progress bar -->
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0"
                         aria-valuemax="100">
                        <div class="progress-bar progress-bar-success" style="width:0;"></div>
                    </div>
                    <!-- The extended global progress state -->
                    <div class="progress-extended">&nbsp;</div>
                </div>
                <!-- The table listing the files available for upload/download -->
                <table role="presentation" class="table  table-bordered table-hover" id="uploadFileTable">
                    <thead>
                    <tr>
                        <th style="width:18%;">{{ trans('documentManagementFolders.preview') }}</th>
                        <th style="width:40%;">{{ trans('documentManagementFolders.filename') }}</th>
                        <th style="width:14%;">{{ trans('documentManagementFolders.size') }}</th>
                        <th style="width:28%;">{{ trans('documentManagementFolders.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="files" style="font-size:11px!important;"></tbody>
                </table>
            </div>
        </section>

        <section>
            <label class="label">Adjusted Contract Sum ({{{ $ic->project->modified_currency_code }}}):</label>
            <label class="input">
					<span class="interim_claim-adjusted_contract_sum">
						{{{ number_format($ic->project->pam2006Detail->contract_sum + Input::old('nett_addition_omission', ($previousInfo) ? $previousInfo->nett_addition_omission : null), 2) }}}
					</span>

                {{ Form::hidden('adjusted_contract_sum', $ic->project->pam2006Detail->contract_sum + Input::old('nett_addition_omission', ($previousInfo) ? $previousInfo->nett_addition_omission : null), array('class' => 'interim_claim-adjusted_contract_sum_input')) }}
            </label>
        </section>

        @if ( $user->hasCompanyProjectRole($ic->project, array( PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER, PCK\ContractGroups\Types\Role::CLAIM_VERIFIER )) )
            <section>
                <label class="label">Date of Certificate<span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('date_of_certificate') ? 'state-error' : null }}}">
                    {{ Form::text('date_of_certificate', Input::old('date_of_certificate', ($previousInfo) ? $ic->project->getProjectTimeZoneTime($previousInfo->date_of_certificate) : null), array('class' => 'finishdate')) }}
                </label>
                {{ $errors->first('date_of_certificate', '<em class="invalid">:message</em>') }}
            </section>
        @endif

        <section>
            <label class="label">Gross Values of Works ({{{ $ic->project->modified_currency_code }}})<span
                        class="required">*</span>:</label>
            <label class="input {{{ $errors->has('gross_values_of_works') ? 'state-error' : null }}}">
                {{ Form::text('gross_values_of_works', $grossValuesOfWorks, array('required' => 'required', 'class' => 'interim_claim-gross_values_of_works', 'v-model' => 'gross_values_of_works', 'v-on' => 'keyup: grossValuesOfWorks_onChange')) }}
            </label>
            {{ $errors->first('gross_values_of_works', '<em class="invalid">:message</em>') }}
        </section>

        <section>
            <label class="label">Gross Values of Works Attachment(s):</label>

            <div id="grossValuesOfWorksFileUpload">
                <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                <div class="row fileupload-buttonbar" style="margin: 0;">
                    <div>
                        <!-- The fileinput-button span is used to style the file input field as button -->
							<span class="btn btn-sm btn-success fileinput-button">
								<i class="glyphicon glyphicon-plus"></i>
								<span>{{ trans('files.addFiles') }}</span>
								<input type="file" name="file" multiple>
							</span>
                        <button class="btn btn-sm btn-primary start">
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>{{ trans('files.startUpload') }}</span>
                        </button>
                        <button class="btn btn-sm btn-warning cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>{{ trans('files.cancelUpload') }}</span>
                        </button>

                        <!-- The global file processing state -->
                        <span class="fileupload-process"></span>
                    </div>
                </div>
                <!-- The global progress state -->
                <div class="fileupload-progress fade">
                    <!-- The global progress bar -->
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0"
                         aria-valuemax="100">
                        <div class="progress-bar progress-bar-success" style="width:0;"></div>
                    </div>
                    <!-- The extended global progress state -->
                    <div class="progress-extended">&nbsp;</div>
                </div>
                <!-- The table listing the files available for upload/download -->
                <table role="presentation" class="table  table-bordered table-hover" id="uploadFileTable">
                    <thead>
                    <tr>
                        <th style="width:18%;">{{ trans('documentManagementFolders.preview') }}</th>
                        <th style="width:40%;">{{ trans('documentManagementFolders.filename') }}</th>
                        <th style="width:14%;">{{ trans('documentManagementFolders.size') }}</th>
                        <th style="width:28%;">{{ trans('documentManagementFolders.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="files" style="font-size:11px!important;"></tbody>
                </table>
            </div>
        </section>

        <section>
            <label class="label">
                Less Retention Fund ({{{ $ic->project->modified_currency_code }}}):<br>
                ({{{ $ic->project->pam2006Detail->percentage_of_certified_value_retained }}}% of the certified value
                retained or a maximum retention
                of {{{ $ic->project->modified_currency_code }}} {{{ number_format($ic->max_retention_fund, 2) }}}
            </label>

            @if ( $ic->getCertifiedRetentionFund($grossValuesOfWorks) > $ic->max_retention_fund )
                <span class="interim_claim-less_retention_fund">{{{ number_format($ic->max_retention_fund, 2) }}}</span>
                {{ Form::hidden('retention_fund', $ic->max_retention_fund) }}
            @else
                <span class="interim_claim-less_retention_fund">{{{ number_format($ic->getCertifiedRetentionFund($grossValuesOfWorks), 2) }}}</span>
                {{ Form::hidden('retention_fund', $ic->getCertifiedRetentionFund($grossValuesOfWorks)) }}
            @endif

            {{ Form::hidden('percentage_certified_value', $ic->project->pam2006Detail->percentage_of_certified_value_retained, array('class' => 'interim_claim-percentage_certified_value')) }}
            {{ Form::hidden('max_retention_fund', $ic->max_retention_fund, array('class' => 'interim_claim-max_retention_fund')) }}
        </section>

        @if ( $ic->claim_counter > 1 )
            <section>
                <label class="label">
                    Less amounts previously certified (Certificate
                    No.1{{{ $ic->claim_counter > 2 ? " to No.".($ic->claim_counter - 1) : null }}})
                    ({{{ $ic->project->modified_currency_code }}})
                </label>
                <label class="input">
                    {{{ number_format($previousGrantedAmount, 2) }}}

                    {{ Form::hidden('previousGrantedAmount', $previousGrantedAmount, array('class' => 'interim_claim-previous_granted_amount')) }}
                </label>
            </section>
        @endif

        <section>
            <label class="label">
                Nett Amount of Payment Certified ({{{ $ic->project->modified_currency_code }}}):
            </label>
            <label class="input">
					<span class="interim_claim-amount_certified">
						{{{ number_format( ($previousInfo) ? $previousInfo->net_amount_of_payment_certified : 0, 2) }}}
					</span>
            </label>
        </section>

        <section>
            <label class="label">Amount in Word ({{{ $ic->project->modified_currency_code }}})<span
                        class="required">*</span>:</label>
            <label class="input {{{ $errors->has('amount_in_word') ? 'state-error' : null }}}">
                {{ Form::text('amount_in_word', Input::old('amount_in_word', ($previousInfo) ? $previousInfo->amount_in_word : null), array('required' => 'required', 'v-model' => 'amount_in_word')) }}
            </label>
            {{ $errors->first('amount_in_word', '<em class="invalid">:message</em>') }}
        </section>

        <section>
            <label class="label">
                Contractor's Company Name:
            </label>
            <label class="input">
                @foreach ( $project->selectedCompanies as $company )
                    @if ( $company->hasProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) )
                        {{{ $company->name }}}
                    @endif
                @endforeach
            </label>
        </section>

        <section>
            <label class="label">
                Period of Honouring Certificates:
            </label>
            {{{ $ic->project->pam2006Detail->period_of_honouring_certificate }}} days
        </section>

        <section>
            <label class="label">
                Architect's Company Name:
            </label>
            <label class="input">
                @foreach ( $project->selectedCompanies as $company )
                    @if ( $company->hasProjectRole($project, PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
                        {{{ $company->name }}}
                    @endif
                @endforeach
            </label>
        </section>
    </fieldset>

    {{ Form::hidden('previous_claim_amount', $previousGrantedAmount) }}

    <footer>
        @if ( $isEditor )
            {{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'issue_ic_additional_info')) }}
        @endif
    </footer>
    {{ Form::close() }}
</div>