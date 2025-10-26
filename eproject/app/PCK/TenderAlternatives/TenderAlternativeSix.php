<?php namespace PCK\TenderAlternatives;

class TenderAlternativeSix extends Calculation {

    protected $formula = '( A + B ) + F';

    protected $adjustmentTotalFormula = '( ( A + B ) * ( E / 100 ) )';

    protected $adjustmentPercentageFormula = '( F / ( A + B ) ) * 100';

    protected $viewName = 'tender_alternatives/six';

}