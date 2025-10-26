<?php namespace PCK\TenderAlternatives;

class TenderAlternativeFive extends Calculation {

    protected $formula = '( A + F ) + ( ( A + F )  * ( G / 100 ) )';

    protected $adjustmentTotalFormula = '( A * ( E / 100 ) )';

    protected $adjustmentPercentageFormula = '( F / A ) * 100';

    protected $viewName = 'tender_alternatives/five';

}