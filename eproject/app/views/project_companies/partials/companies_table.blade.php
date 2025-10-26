<!-- This file does not seem to be used -->
<div class="table-responsive">
	<table class="table ">
		<thead>
			<tr>
				<th style="text-align: center; width: 15%;">Select Company</th>
				<th style="text-align: center;">Company Name</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($companies as $company)
				@if ($company->pam_2006_contract_group_id == $group->id)
					<tr>
						<td style="text-align: center;">{{ Form::radio($group->id, $company->id, in_array($company->id, $selectedCompanyIds) ? true : false) }}</td>
						<td style="text-align: center;">
							{{{ $company->name }}}
						</td>
					</tr>
				@endif
			@endforeach
		</tbody>
	</table>
</div>