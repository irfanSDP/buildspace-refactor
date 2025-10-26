<div class="row" style="{{ $display ? '':'display: none;' }}">
    <section class="col col-xs-11 col-md-5 col-lg-5">
        <label class="label">{{ trans('projectReportChart.dataGrouping') }} <span class="required">*</span>:</label>
        <label class="fill-horizontal">
            <select class="{{ (! empty($selections['data_grouping']))?'select2':'form-control' }} fill-horizontal" name="dataGrouping" id="dataGrouping" required>
                @if (empty($selections['data_grouping']))<option value="">None</option>@endif
                @foreach ($selections['data_grouping'] as $key => $dataGrouping)
                    <option value="{{ $key }}">{{ $dataGrouping }}</option>
                @endforeach
            </select>
        </label>
        {{ $errors->first('dataGrouping', '<em class="invalid">:message</em>') }}
    </section>
</div>