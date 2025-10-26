<div class="row">
    <section class="col col-xs-11 col-md-5 col-lg-5">
        <label class="label">{{ trans('projectReportChart.valueColumn') }} <span class="required">*</span>:</label>
        <label class="fill-horizontal">
            <select class="{{ (! empty($selections['value_columns']))?'select2':'form-control' }} fill-horizontal" name="valueColumn" id="valueColumn" required>
                @if (empty($selections['value_columns']))<option value="">None</option>@endif
                @foreach ($selections['value_columns'] as $key => $valueColumn)
                    <option value="{{ $key }}" {{ $key == $record->value_column_id ? 'selected' : '' }}>{{ $valueColumn }}</option>
                @endforeach
            </select>
        </label>
        {{ $errors->first('valueColumn', '<em class="invalid">:message</em>') }}
    </section>
</div>