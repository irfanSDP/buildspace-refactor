<table class="table">
    <thead style="display:table;width:100%;table-layout:fixed;">
        <tr>
            <th class="text-center text-middle min" rowspan="2" style="width:64px;">{{ trans('general.no') }}</th>
            <th class="text-middle" rowspan="2" style="min-width:380px;">{{ trans('vendorManagement.vendorWorkCategory') }}</th>
            <th class="text-center" colspan="2">{{ trans('vendorManagement.original') }}</th>
            <th class="text-center" colspan="2">{{ trans('vendorManagement.deliberated') }}</th>
        </tr>
        <tr>
            <th class="text-center" style="width:120px;">{{ trans('vendorManagement.score') }}</th>
            <th class="text-center min">{{ trans('vendorManagement.rating') }}</th>
            <th class="text-center" style="width:120px;">{{ trans('vendorManagement.score') }}</th>
            <th class="text-center min">{{ trans('vendorManagement.rating') }}</th>
        </tr>
    </thead>
    <tbody style="display:block;height:260px;overflow:auto;" id="vp-vendor_performance_evaluation-rows"></tbody>
</table>