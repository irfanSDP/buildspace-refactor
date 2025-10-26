<?php namespace PCK\TenderAlternatives;

class TenderAlternativeSeven extends Calculation {

    protected $formula = 'A + F + ( D / 100 ) * ( C + F )';

    protected $adjustmentTotalFormula = '( A * ( E / 100 ) )';

    protected $adjustmentPercentageFormula = '( F / A ) * 100';

    protected $viewName = 'tender_alternatives/seven';

}