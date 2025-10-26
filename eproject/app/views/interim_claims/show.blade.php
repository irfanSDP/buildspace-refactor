@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ic->project->title, 50), array($ic->project->id)) }}</li>
		<li>{{ link_to_route('ic', trans('navigation/projectnav.interimClaim') . ' (IC)', array($ic->project->id)) }}</li>
		<li>View Current IC ({{{ $ic->claim_no }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $ic->project))
@endsection

@section('content')
	<h1>View Current IC ({{{ $ic->claim_no }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">Notice to Claim</a>
							</li>

							@if ( $ic->contractorClaimInformation )
								<li>
									<a href="#s2" data-toggle="tab">Contractor Form</a>
								</li>

								@if ( ($ic->qsConsultantClaimInformation) or (! $ic->architectClaimInformation and $isEditor and $user->hasCompanyProjectRole($ic->project, \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER)) )
									<li>
										<?php $qsFormShow = true; ?>

										<a href="#s3" data-toggle="tab">QS Consultant Form</a>
									</li>
								@endif

								@if ( $ic->architectClaimInformation or ($isEditor and $user->hasCompanyProjectRole($ic->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER)) )
									<li>
										<?php $architectFormShow = true; ?>

										<a href="#s4" data-toggle="tab">Architect Form</a>
									</li>
								@endif
							@elseif ( $isEditor and $user->hasCompanyProjectRole($ic->project, \PCK\ContractGroups\Types\Role::CONTRACTOR) )
								<li>
									<a href="#s2" data-toggle="tab">Contractor Form</a>
								</li>
							@endif
						</ul>

						<div class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@include('interim_claims.partials.ic_view_only')
								</div>
							</div>

							@if ( $ic->contractorClaimInformation )
								<!-- Contractor Interim Claim Additional Information Tab -->
								<div class="tab-pane" id="s2">
									@include('interim_claims.partials.ic_additional_info_form_view_only', array('object' => $ic->contractorClaimInformation))
								</div>

								<!-- QS Interim Claim Additional Information Tab -->
								<div class="tab-pane" id="s3">
									@if ( $ic->qsConsultantClaimInformation )
										@include('interim_claims.partials.ic_additional_info_form_view_only', array('object' => $ic->qsConsultantClaimInformation))
									@elseif ( isset($qsFormShow) )
										@include('interim_claims.partials.ic_additional_info_form', array('previousInfo' => $ic->contractorClaimInformation))
									@endif
								</div>

								<!-- Architect Interim Claim Additional Information Tab -->
								<div class="tab-pane" id="s4">
									@if ( $ic->architectClaimInformation )
										@include('interim_claims.partials.ic_additional_info_form_view_only', array('object' => $ic->architectClaimInformation))
									@elseif ( isset($architectFormShow) )
										@include('interim_claims.partials.ic_additional_info_form', array('previousInfo' => $ic->qsConsultantClaimInformation ?: $ic->contractorClaimInformation))
									@endif
								</div>
							@elseif ( $isEditor and $user->hasCompanyProjectRole($ic->project, \PCK\ContractGroups\Types\Role::CONTRACTOR) )
								<!-- Contractor Interim Claim Additional Information Tab -->
								<div class="tab-pane" id="s2">
									@include('interim_claims.partials.ic_additional_info_form', array('previousInfo' => ($previousIc) ? $previousIc->architectClaimInformation : null))
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</article>
	</div>

	<!-- The template to display files available for upload -->
	<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
			<td>
				<span class="preview"></span>
			</td>
			<td>
				<p class="name">{%=file.name%}</p>
				<strong class="error text-danger"></strong>
			</td>
			<td>
				<p class="size">{{ trans('files.processing') }}...</p>
				<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
			</td>
			<td>
				{% if (!i && !o.options.autoUpload) { %}
					<button class="btn btn-xs btn-primary start" disabled>
						<i class="glyphicon glyphicon-upload"></i>
						<span>{{ trans('files.start') }}</span>
					</button>
				{% } %}
				{% if (!i) { %}
					<button class="btn btn-xs btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>{{ trans('files.cancel') }}</span>
					</button>
				{% } %}
			</td>
		</tr>
	{% } %}
	</script>

	<!-- The template to display files available for download -->
	<script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-download fade">
			<td>
				<span class="preview">
					{% if (file.thumbnailUrl) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
					{% } %}
				</span>
			</td>
			<td>
				<p class="name">
					{% if (file.url) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
					{% } else { %}
						<span>{%=file.name%}</span>
					{% } %}
					{% if (file.fileID) { %}
						<input class="upload-field-ids" type="hidden" name="nett_addition_omission_uploaded_files[]" value="{%=file.fileID%}">
					{% } %}
				</p>
				{% if (file.error) { %}
					<div><span class="label label-danger">{{ trans('files.error') }}</span> {%=file.error%}</div>
				{% } %}
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<td>
				{% if (file.deleteUrl) { %}
					<button class="btn btn-xs btn-danger delete" onclick="deleteUpload('{%=file.deleteUrl%}')">
						<i class="glyphicon glyphicon-trash"></i>
						<span>{{ trans('files.delete') }}</span>
					</button>
				{% } else { %}
					<button class="btn btn-xs btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>{{ trans('files.cancel') }}</span>
					</button>
				{% } %}
			</td>
		</tr>
	{% } %}
	</script>

	<script id="template-download-2" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-download fade">
			<td>
				<span class="preview">
					{% if (file.thumbnailUrl) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
					{% } %}
				</span>
			</td>
			<td>
				<p class="name">
					{% if (file.url) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
					{% } else { %}
						<span>{%=file.name%}</span>
					{% } %}
					{% if (file.fileID) { %}
						<input class="upload-field-ids" type="hidden" name="gross_values_uploaded_files[]" value="{%=file.fileID%}">
					{% } %}
				</p>
				{% if (file.error) { %}
					<div><span class="label label-danger">{{ trans('files.error') }}</span> {%=file.error%}</div>
				{% } %}
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<td>
				{% if (file.deleteUrl) { %}
					<button class="btn btn-xs btn-danger delete" onclick="deleteUpload('{%=file.deleteUrl%}')">
						<i class="glyphicon glyphicon-trash"></i>
						<span>{{ trans('files.delete') }}</span>
					</button>
				{% } else { %}
					<button class="btn btn-xs btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>{{ trans('files.cancel') }}</span>
					</button>
				{% } %}
			</td>
		</tr>
	{% } %}
    </script>

@endsection

@section('js')
	<script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/tmpl.min.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/load-image.all.min.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/canvas-to-blob.min.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.iframe-transport.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-process.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-image.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-audio.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-video.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-validate.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-ui.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        function ReplaceNumberWithCommas(yourNumber) {
            //Separates the components of the number
            var n= yourNumber.toString().split(".");

            //Comma-fies the first part
            n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            //Combines the two sections
            return n.join(".");
        }

        $(window).keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        $( "#icForm" ).validate({
            errorClass: 'required',
            rules: {
                reference: {
                    required: true
                },
                date: {
                    required: true
                },
                nett_addition_omission: {
                    required: true
                },
                amount_in_word: {
                    required: true
                },
                gross_values_of_works: {
                    required: true,
                    max: parseFloat($('.interim_claim-adjusted_contract_sum_input').val())
                }
            }
        });

        // for calculating Adjusted Contract Sum
        $(document).on('keyup', '.interim_claim-nett_addition-omission', function() {
            var $this = $(this);
            var form = $this.closest('form');

            var contractSum = form.find('.interim_claim-contract_sum').val();
            var adjustedContractSum = $this.val();

            var sumTotal = (parseFloat(contractSum) + parseFloat(adjustedContractSum)).toFixed(2);

            form.find('.interim_claim-adjusted_contract_sum').html( ReplaceNumberWithCommas(sumTotal) );
            form.find('.interim_claim-adjusted_contract_sum_input').val( sumTotal );

            var settings = $('#icForm').validate().settings;

            // remove the old rule of validation
            delete settings.rules.gross_values_of_works;

            // update the form's validation parameter for gross_values_of_works
            settings.rules.gross_values_of_works = {
                required: true,
                max: parseFloat(sumTotal)
            }
        });

        function deleteUpload(url){
            $.post(url, {
                '_token': $('meta[name=_token]').attr("content")
            })
                    .done(function(data) {
                        // code for result
                    });
        }

        $(document).ready(function() {
            var token = $('meta[name=_token]').attr("content");

            // Initialize the jQuery File Upload widget:
            $('#nettAdditionOmissionFileUpload').fileupload({
                url: '{{ route("moduleUploads.upload", array($project->id)) }}',
                formData: {_token :token},
                maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                // Enable image resizing, except for Android and Opera,
                // which actually support image resizing, but fail to
                // send Blob objects via XHR requests:
                disableImageResize: /Android(?!.*Chrome)|Opera/
                        .test(window.navigator.userAgent)
            });

            $('#grossValuesOfWorksFileUpload').fileupload({
                downloadTemplateId: 'template-download-2',
                url: '{{ route("moduleUploads.upload", array($project->id)) }}',
                formData: {_token :token},
                maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                // Enable image resizing, except for Android and Opera,
                // which actually support image resizing, but fail to
                // send Blob objects via XHR requests:
                disableImageResize: /Android(?!.*Chrome)|Opera/
                        .test(window.navigator.userAgent)
            });
        });

        var icFormVue = new Vue({
            el: '#icForm',
            data: {},
            methods: {
                grossValuesOfWorks_onChange: function()
                {
                    var form = $('#icForm');

                    var grossValuesOfWorks = this.gross_values_of_works;
                    var maxRetentionFund = form.find('.interim_claim-max_retention_fund').val();
                    var percentageCertifiedValue = form.find('.interim_claim-percentage_certified_value').val();
                    var previousGrantedAmount = form.find('.interim_claim-previous_granted_amount').val();

                    // calculate percentage certified value
                    percentageCertifiedValue = parseFloat(percentageCertifiedValue / 100);

                    var lessRetentionFund = (parseFloat(grossValuesOfWorks) * parseFloat(percentageCertifiedValue)).toFixed(2);

                    if ( parseFloat(lessRetentionFund) > parseFloat(maxRetentionFund) ) {
                        lessRetentionFund = parseFloat(maxRetentionFund).toFixed(2);
                    }

                    form.find('.interim_claim-less_retention_fund').html( ReplaceNumberWithCommas(lessRetentionFund) );

                    var sumTotal = (parseFloat(grossValuesOfWorks) - parseFloat(lessRetentionFund));

                    if ( previousGrantedAmount ) {
                        sumTotal = sumTotal - parseFloat(previousGrantedAmount);
                    }

                    form.find('.interim_claim-amount_certified').html( ReplaceNumberWithCommas(sumTotal.toFixed(2)) );

                    this.updateNettPaymentCertifiedAmount(sumTotal);
                },
                updateNettPaymentCertifiedAmount: function(amount)
                {
                    var self = this;
                    $.ajax({
                        url: '{{{ route('convert.spellCurrencyAmount') }}}',
                        method: 'POST',
                        data: {
                            _token: '{{{ csrf_token() }}}',
                            amount: amount,
                            currency_name: '{{{ $ic->project->modified_currency_name }}}'
                        },
                        success: function (data) {
                            self.amount_in_word = data;

                            // Quick fix, not sure why updating self.amount_in_word does not update the input value.
                            $('input[name=amount_in_word]' ).val(data);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                }
            }
        });
    </script>
@endsection