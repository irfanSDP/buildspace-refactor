@if($historicalCycleIds && (count($historicalCycleIds) > 0))
    @foreach($externalVendors as $vendor)
        @if(!$vendor->vendorCategories->isEmpty())
            <header>{{ $vendor->name }}</header>
            <div class="smart-form">
                <fieldset>
                    <div class="row">
                        <section class="col col-xs-12 well">
                            <div style="width: 500px;">
                                <label class="label">{{ trans('vendorManagement.byVendorCategory') }}</label>
                                <select name="interested" class="select2" id="top-evaluation-score-select-{{ $vendor->id }}">
                                    @foreach($vendor->vendorCategories()->orderBy('name', 'asc')->get() as $key => $vendorCategory)
                                        <option value="{{ $vendorCategory->id }}" @if($key == 0) selected @endif>{{ $vendorCategory->name }}</option>
                                    @endforeach
                                </select> <i></i> </label>
                            </div>
                            <br>
                            <br>
                            <div id="top-evaluation-score-table-{{ $vendor->id }}"></div>
                        </section>
                    </div>
                </fieldset>
            </div>
            <hr>
        @endif
    @endforeach
@endif