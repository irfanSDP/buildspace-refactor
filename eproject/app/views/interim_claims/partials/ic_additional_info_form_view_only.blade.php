<div class="widget-body no-padding">
	<div class="smart-form">
		<fieldset>
			<section>
				<strong>Date Submitted:</strong><br>
				<strong class="dateSubmitted"><i>{{{ $ic->project->getProjectTimeZoneTime($object->created_at) }}}</i></strong> by {{{ $object->createdBy->present()->byWhoAndRole($ic->project, $ic->created_at) }}}
			</section>

			<section>
				<strong>Reference:</strong><br>
				{{{ $object->reference }}}
			</section>

			<section>
				<strong>Date</strong><br>
				{{{ $ic->project->getProjectTimeZoneTime($object->date) }}}
			</section>

			<section>
				<strong>To:</strong><br>
				@if ( $ic->project->subsidiary->name )
					<address>
						<strong>{{{ $ic->project->subsidiary->name }}}</strong><br>
					</address>
				@else
					-
				@endif
			</section>

			<section>
				<strong>Works:</strong><br>
				{{{ $ic->project->title }}}
			</section>

			<section>
				<strong>Contract Sum ({{{ $ic->project->modified_currency_code }}}):</strong><br>
				{{{ number_format($ic->project->pam2006Detail->contract_sum, 2) }}}
			</section>

			<section>
				<strong>Nett Addition/Omission ({{{ $ic->project->modified_currency_code }}}):</strong><br>
				{{{ number_format($object->nett_addition_omission, 2) }}}
			</section>

			@if ( ! $object->nettAdditionOmissionAttachments->isEmpty() )
				<section>
					<strong>Nett Addition/Omission Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $object->nettAdditionOmissionAttachments, 'projectId' => $ic->project_id])
				</section>
			@endif

			<section>
				<strong>Adjusted Contract Sum ({{{ $ic->project->modified_currency_code }}}):</strong><br>
				{{{ number_format($ic->project->pam2006Detail->contract_sum + $object->nett_addition_omission, 2) }}}
			</section>

			<section>
				<strong>Date of Certificate:</strong><br>
				{{{ $ic->project->getProjectTimeZoneTime($object->date_of_certificate) ?? '-' }}}
			</section>

			<section>
				<strong>Gross Values of Works ({{{ $ic->project->modified_currency_code }}}):</strong><br>
				{{{ number_format($object->gross_values_of_works, 2) }}}
			</section>

			@if ( ! $object->grossValuesAttachments->isEmpty() )
				<section>
					<strong>Gross Values of Works Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $object->grossValuesAttachments, 'projectId' => $ic->project_id])
				</section>
			@endif

			<section>
				<strong>
					Less Retention Fund ({{{ $ic->project->modified_currency_code }}}):<br>
					({{{ $ic->project->pam2006Detail->percentage_of_certified_value_retained }}}% of the certified value retained or a maximum retention of {{{ $ic->project->modified_currency_code }}} {{{ number_format($ic->max_retention_fund, 2) }}}
				</strong><br>
				@if ( $ic->getCertifiedRetentionFund($object->gross_values_of_works) > $ic->max_retention_fund )
					{{{ number_format($ic->max_retention_fund, 2) }}}
				@else
					{{{ number_format($ic->getCertifiedRetentionFund($object->gross_values_of_works), 2) }}}
				@endif
			</section>

			@if ( $ic->claim_counter > 1 )
				<section>
					<strong>
						Less amounts previously certified (Certificate No.1{{ $ic->claim_counter > 2 ? " to No.".($ic->claim_counter - 1) : null }}) ({{{ $ic->project->modified_currency_code }}})
					</strong><br>
					{{{ number_format($previousGrantedAmount, 2) }}}
				</section>
			@endif

			<section>
				<strong>
					Nett Amount of Payment Certified ({{{ $ic->project->modified_currency_code }}}):
				</strong><br>
				{{{ number_format($object->net_amount_of_payment_certified, 2) }}}
			</section>

			<section>
				<strong>Amount in Word ({{{ $ic->project->modified_currency_code }}}):</strong><br>
				{{{ $object->amount_in_word }}}
			</section>

			<section>
				<strong>
					Contractor's Company Name:
				</strong><br>
				@foreach ( $project->selectedCompanies as $company )
					@if ( $company->hasProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) )
						{{{ $company->name }}}
					@endif
				@endforeach
			</section>

			<section>
				<strong>
					Period of Honouring Certificates ({{{ $ic->project->pam2006Detail->period_of_honouring_certificate }}} days from submitted date):
				</strong><br>
				@if ( $object->type == PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER )
					{{{ $object->honouring_certificate_period }}}
				@else
					{{{ $ic->project->pam2006Detail->period_of_honouring_certificate }}} days
				@endif
			</section>

			<section>
				<strong>
					Architect's Company Name:
				</strong><br>
				@foreach ( $project->selectedCompanies as $company )
					@if ( $company->hasProjectRole($project, PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
						{{{ $company->name }}}
					@endif
				@endforeach
			</section>
		</fieldset>

		<footer>
			{{ link_to_route('ic.additional_info_print', 'Print', array($ic->project_id, $object->id), array('class' => 'btn btn-primary', 'target' => '_blank')) }}
		</footer>
	</div>
</div>