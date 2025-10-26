@if (! empty($record->filters['options']['year']) || (! empty($record->filters['options']['grouping']) && count($record->filters['options']['grouping']) > 1))
	<div class="well">
		<div class="row">
			@if (! empty($record->filters['options']['grouping']) && count($record->filters['options']['grouping']) > 1)
				<div class="col col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<label class="label">{{ trans('projectReportChart.groupBy') }}</label>
					<label class="fill-horizontal">
						<select class="select2 fill-horizontal form-control options_grouping" name="options_grouping_{{ $record->id }}" id="options_grouping_{{ $record->id }}">
							@foreach ($record->filters['options']['grouping'] as $key => $grouping)
								<option value="{{ $key }}" @if($record->filters['grouping'] == $key) selected @endif>{{ $grouping }}</option>
							@endforeach
						</select>
					</label>
				</div>
			@endif
			@if (! empty($record->filters['options']['year']))
				<div class="col col-xs-12 col-sm-6 col-md-6 col-lg-6" id="options_year_container_{{ $record->id }}" style="{{ $record->filters['grouping'] === \PCK\ProjectReport\ProjectReportChartPlot::GRP_YEARLY ? 'display:none;' : '' }}">
					<label class="label">{{ trans('dates.year') }}</label>
					<label class="fill-horizontal">
						<select class="select2 fill-horizontal form-control options_year" name="options_year_{{ $record->id }}" id="options_year_{{ $record->id }}">
							@foreach ($record->filters['options']['year'] as $year)
								<option value="{{ $year }}" @if($record->filters['year'] == $year) selected @endif>{{ $year }}</option>
							@endforeach
						</select>
					</label>
				</div>
			@endif
		</div>
	</div>
@endif