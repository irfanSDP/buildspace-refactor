Based on the total works to be completed by tenderer with contractor's own purchase of material with <strong>FIXED OVERALL COMPLETION PERIOD OF <span style="color: red;">{{{ $projectPeriodByDeveloper }}}</span> {{{ strtoupper($projectPeriodMetric) }}}</strong>
@include('tender_alternatives.partials.tender_alternatives_ending_2', array('currencyName' => $currencyName))
@if ($includeTax)
    @include('tender_alternatives.partials.tax', array('taxPercentage' => $taxPercentage, 'taxName' => $taxName))
@endif