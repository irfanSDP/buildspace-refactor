<table class="table table-bordered table-condensed table-striped table-hover smallFont" style="width:50%;">
    <thead>
        <tr>
            <th class="text-center">{{ trans('requestForVariation.categoryOfRfv') }}</th>
            <th class="text-center">{{ trans('general.max') }} (%)</th>
            <th class="text-center">{{ trans('general.current') }} (%)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{{ $requestForVariation->requestForVariationCategory->name }}}</td>
            <td class="text-center" style="width:20%;">{{{ $maxKpiLimit}}}</td>
            <?php $class = ($currentKpiLimit > $maxKpiLimit) ? 'text-danger' : null; ?>
            <td class="text-center {{{ $class }}}" style="width:20%;">{{{ $currentKpiLimit }}}</td>
        </tr>
    </tbody>
</table>
