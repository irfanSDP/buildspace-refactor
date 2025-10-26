<label class="label">{{ trans('eBiddingMode.bidMode') }}</label>
<label class="fill-horizontal">
    <select class="select2 fill-horizontal form-control options_filter" name="options_bid_mode" id="options_bid_mode">
        @foreach ($bidModeSelections as $value => $label)
            <option value="{{ $value }}" @if($value == $bidModeSelected) selected @endif>{{ $label }}</option>
        @endforeach
    </select>
</label>