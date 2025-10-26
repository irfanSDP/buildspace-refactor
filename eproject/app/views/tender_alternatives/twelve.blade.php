Based on the total works to be completed by tenderer with contractor's own purchase of material and with contractor's alternative design proposal adjustment <strong><span style="color: red;">{{{ $contractorIncentive }}}</span>%</strong> and to complete the works within the Main Contractor's work programme for the whole of the works, from the Date of Commencement or within such extended time as provided by the Conditions of Contract, and the total amount of Tender is the Firm Price Lump Sum of Ringgit:-
@include('tender_alternatives.partials.tender_alternatives_ending', array('currencyName' => $currencyName))
@if ($includeTax)
    @include('tender_alternatives.partials.tax', array('taxPercentage' => $taxPercentage, 'taxName' => $taxName))
@endif